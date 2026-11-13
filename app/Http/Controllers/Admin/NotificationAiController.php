<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminSetting;
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
        $this->apiKey = AdminSetting::getSetting('groq_api_key', '') ?: config('services.groq.api_key', '');
        $this->model  = AdminSetting::getSetting('groq_model', '') ?: config('services.groq.model', 'openai/gpt-oss-120b');
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

        $decoded = $this->decodeJson($result['text']);
        $subjects = is_array($decoded) ? $decoded : [];
        $subjects = array_values(array_filter(array_map(static fn ($subject) => trim(strip_tags((string)$subject)), $subjects)));
        if (!$subjects) return response()->json(['error' => 'The AI returned an empty subject list. Please try again.'], 502);

        return response()->json(['subjects' => $subjects]);
    }

    /** Create a reviewable campaign draft for the existing opted-in email audience. */
    public function generateEmailCampaign(Request $request): JsonResponse
    {
        $data = $request->validate([
            'goal' => 'required|string|max:500',
            'offer' => 'nullable|string|max:300',
            'call_to_action' => 'nullable|string|max:120',
            'tone' => 'required|in:professional,friendly,celebratory,urgent,educational',
        ]);

        $system = 'You are KayXchange’s careful email marketing writer for a Nigerian crypto and fintech platform. '
            . 'Write concise, clear, respectful promotional emails in plain text. Never invent discounts, rates, fees, deadlines, '
            . 'product features, regulatory status, or eligibility. Never promise profit, guaranteed returns, or risk-free trading. '
            . 'Use only the facts supplied by the admin. If no offer is specified, promote the stated goal without fabricating an offer. '
            . 'Make the message suitable for active newsletter subscribers who opted in. Return only valid JSON with keys: '
            . 'subject (string, max 100 chars), body (string, 80-180 words, include a clear CTA), subject_options (array of 3 short strings), '
            . 'campaign_ideas (array of 3 short angles). No markdown fences.';
        $prompt = "Campaign goal: {$data['goal']}\n"
            . 'Specified offer/details: ' . (($data['offer'] ?? '') !== '' ? $data['offer'] : 'No specific offer was supplied; do not invent one.') . "\n"
            . 'Call to action: ' . (($data['call_to_action'] ?? '') !== '' ? $data['call_to_action'] : 'Visit KayXchange') . "\n"
            . "Tone: {$data['tone']}\n\nWrite one polished campaign draft and three alternate subject lines and campaign angles.";

        $result = $this->callGroq($system, $prompt, 1100);
        if (isset($result['error'])) return response()->json(['error' => $result['error']], 502);

        $draft = $this->decodeJson($result['text']);
        if (!is_array($draft) || empty($draft['subject']) || empty($draft['body'])) {
            Log::warning('Email campaign AI response was not valid structured output.');
            return response()->json(['error' => 'The AI could not format a complete email draft. Please try again.'], 502);
        }

        return response()->json([
            'subject' => mb_substr(trim(strip_tags((string)$draft['subject'])), 0, 100),
            'body' => trim(strip_tags((string)$draft['body'])),
            'subject_options' => array_slice(array_values(array_filter(array_map(static fn ($value) => mb_substr(trim(strip_tags((string)$value)), 0, 100), (array)($draft['subject_options'] ?? [])))), 0, 3),
            'campaign_ideas' => array_slice(array_values(array_filter(array_map(static fn ($value) => mb_substr(trim(strip_tags((string)$value)), 0, 180), (array)($draft['campaign_ideas'] ?? [])))), 0, 3),
        ]);
    }

    private function decodeJson(string $text): ?array
    {
        $clean = trim(preg_replace('/^```(?:json)?\s*|\s*```$/i', '', $text) ?? $text);
        $decoded = json_decode($clean, true);
        if (is_array($decoded)) return $decoded;
        $start = strpos($clean, '{');
        $end = strrpos($clean, '}');
        if ($start !== false && $end !== false && $end > $start) {
            $decoded = json_decode(substr($clean, $start, $end - $start + 1), true);
            if (is_array($decoded)) return $decoded;
        }
        $start = strpos($clean, '[');
        $end = strrpos($clean, ']');
        if ($start !== false && $end !== false && $end > $start) {
            $decoded = json_decode(substr($clean, $start, $end - $start + 1), true);
            if (is_array($decoded)) return $decoded;
        }
        return null;
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
