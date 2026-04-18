<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class NotificationAiController extends Controller
{
    private string $apiUrl;
    private string $apiKey;
    private string $model;

    public function __construct()
    {
        $this->apiKey = config('services.groq.api_key', '');
        $this->model  = config('services.groq.model', 'llama-3.3-70b-versatile');
        $this->apiUrl = config('services.groq.api_url', 'https://api.groq.com/openai/v1/chat/completions');
    }

    // POST /admin/ai/notification-copy
    // Generates push notification text for a given user segment + context
    public function generateCopy(Request $request): JsonResponse
    {
        $request->validate([
            'segment' => 'required|string|max:200',
            'context' => 'required|string|max:500',
        ]);

        $segment = $request->input('segment');
        $context = $request->input('context');

        $system = 'You are a CRM copywriter for KayXchange, a Nigerian crypto exchange. '
            . 'Write 3 push notification variants for the given user segment and context. '
            . 'Each notification must be under 100 characters, action-oriented, and feel personal. '
            . 'Return ONLY a JSON array of 3 strings, no extra text.';

        $prompt = "User segment: {$segment}\nContext/goal: {$context}\n\nProvide 3 push notification variants.";

        $result = $this->callGroq($system, $prompt, 300);
        if (isset($result['error'])) {
            return response()->json(['error' => $result['error']], 500);
        }

        // Try to parse as JSON array, otherwise return raw
        $text = $result['text'];
        $decoded = json_decode($text, true);
        $variants = is_array($decoded) ? $decoded : [$text];

        return response()->json(['variants' => $variants]);
    }

    // POST /admin/ai/email-subject
    // Generates A/B email subject lines for a given template context
    public function optimizeSubject(Request $request): JsonResponse
    {
        $request->validate([
            'template_context' => 'required|string|max:600',
        ]);

        $context = $request->input('template_context');

        $system = 'You are an email marketing specialist for KayXchange, a Nigerian crypto exchange. '
            . 'Generate 5 A/B test email subject line variants for the given email context. '
            . 'Mix styles: curiosity, urgency, benefit-driven, personalisation, and question. '
            . 'Each subject must be under 60 characters. '
            . 'Return ONLY a JSON array of 5 strings, no extra commentary.';

        $prompt = "Email context: {$context}\n\nProvide 5 subject line variants for A/B testing.";

        $result = $this->callGroq($system, $prompt, 400);
        if (isset($result['error'])) {
            return response()->json(['error' => $result['error']], 500);
        }

        $text = $result['text'];
        $decoded = json_decode($text, true);
        $subjects = is_array($decoded) ? $decoded : [$text];

        return response()->json(['subjects' => $subjects]);
    }

    private function callGroq(string $system, string $prompt, int $maxTokens = 512): array
    {
        if (empty($this->apiKey)) {
            return ['error' => 'GROQ_API_KEY is not configured.'];
        }
        try {
            $response = Http::timeout(30)
                ->withToken($this->apiKey)
                ->post($this->apiUrl, [
                    'model'       => $this->model,
                    'temperature' => 0.8,
                    'max_tokens'  => $maxTokens,
                    'messages'    => [
                        ['role' => 'system', 'content' => $system],
                        ['role' => 'user',   'content' => $prompt],
                    ],
                ]);
            if ($response->failed()) {
                $msg = $response->json('error.message') ?? 'AI error (HTTP ' . $response->status() . ')';
                return ['error' => $msg];
            }
            return ['text' => trim($response->json('choices.0.message.content', ''))];
        } catch (\Throwable $e) {
            Log::error('NotificationAiController Groq exception', ['msg' => $e->getMessage()]);
            return ['error' => 'AI request failed: ' . $e->getMessage()];
        }
    }
}
