<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class BlogAiController extends Controller
{
    private string $apiUrl  = 'https://api.groq.com/openai/v1/chat/completions';
    private string $apiKey;
    private string $model;

    public function __construct()
    {
        $this->apiKey = AdminSetting::getSetting('groq_api_key', '') ?: config('services.groq.api_key', '');
        $this->model  = AdminSetting::getSetting('groq_model', '') ?: config('services.groq.model', 'llama-3.3-70b-versatile');
    }

    // ── POST /admin/blog/ai/generate ─────────────────────────────────────────
    // Generate a full blog article from a topic prompt
    public function generate(Request $request): JsonResponse
    {
        $request->validate(['topic' => 'required|string|max:500']);

        $topic   = $request->input('topic');
        $tone    = $request->input('tone', 'informative');
        $context = 'KayXchange is a Nigerian crypto/gift-card exchange platform.';

        $system = "You are a professional content writer for a crypto exchange blog. "
            . "Write well-structured, engaging, SEO-friendly blog posts in HTML format. "
            . "Use semantic HTML tags: <h2>, <h3>, <p>, <ul>, <li>, <strong>, <blockquote>. "
            . "Context: {$context}";

        $prompt = "Write a {$tone} blog post about: \"{$topic}\".\n\n"
            . "Requirements:\n"
            . "- Compelling introduction paragraph\n"
            . "- 3-5 well-developed sections with <h2> headings\n"
            . "- Practical tips or takeaways\n"
            . "- Strong conclusion\n"
            . "- Approximately 600-900 words\n"
            . "Return ONLY the HTML body content (no <html>/<body> tags).";

        $result = $this->callGroq($system, $prompt, 2048);
        if (isset($result['error'])) {
            return response()->json(['error' => $result['error']], 500);
        }

        return response()->json(['content' => $result['text']]);
    }

    // ── POST /admin/blog/ai/improve ──────────────────────────────────────────
    // Rewrite / improve a piece of selected text
    public function improve(Request $request): JsonResponse
    {
        $request->validate([
            'text'   => 'required|string|max:10000',
            'action' => 'required|in:improve,simplify,expand,formal,casual,proofread',
        ]);

        $text   = $request->input('text');
        $action = $request->input('action');

        $instructions = [
            'improve'   => 'Rewrite this text to be clearer, more engaging, and professional.',
            'simplify'  => 'Simplify this text so it is easy to understand for non-technical readers.',
            'expand'    => 'Expand this text with more detail, examples, and context. Keep HTML structure.',
            'formal'    => 'Rewrite this text in a formal, professional tone.',
            'casual'    => 'Rewrite this text in a friendly, conversational tone.',
            'proofread' => 'Fix all grammar, spelling, and punctuation errors. Preserve HTML tags exactly.',
        ];

        $system = 'You are a professional editor. Return ONLY the rewritten HTML content. '
            . 'Preserve all existing HTML tags. Do not add explanations or commentary.';

        $prompt = $instructions[$action] . "\n\nText to process:\n\n" . $text;

        $result = $this->callGroq($system, $prompt, 2048);
        if (isset($result['error'])) {
            return response()->json(['error' => $result['error']], 500);
        }

        return response()->json(['content' => $result['text']]);
    }

    // ── POST /admin/blog/ai/excerpt ──────────────────────────────────────────
    // Auto-generate excerpt from article content
    public function excerpt(Request $request): JsonResponse
    {
        $request->validate(['content' => 'required|string|max:20000']);

        $content = strip_tags($request->input('content'));
        $content = mb_substr($content, 0, 3000); // cap input to keep tokens low

        $system = 'You are a copywriter. Write concise blog excerpts. '
            . 'Return only the excerpt text — no quotes, no labels.';

        $prompt = "Write a compelling 1-2 sentence excerpt (max 160 characters) for this blog post:\n\n" . $content;

        $result = $this->callGroq($system, $prompt, 200);
        if (isset($result['error'])) {
            return response()->json(['error' => $result['error']], 500);
        }

        $excerpt = mb_substr(trim($result['text']), 0, 500);
        return response()->json(['excerpt' => $excerpt]);
    }

    // ── POST /admin/blog/ai/titles ───────────────────────────────────────────
    // Suggest 5 title ideas from a topic or existing content
    public function titles(Request $request): JsonResponse
    {
        $request->validate(['topic' => 'required|string|max:1000']);

        $topic  = strip_tags($request->input('topic'));
        $topic  = mb_substr($topic, 0, 1000);

        $system = 'You are a content strategist. Generate catchy, SEO-friendly blog titles. '
            . 'Return exactly 5 titles, one per line, numbered 1-5. No extra text.';

        $prompt = "Suggest 5 compelling blog post titles for a crypto/fintech blog about:\n\n\"{$topic}\"";

        $result = $this->callGroq($system, $prompt, 300);
        if (isset($result['error'])) {
            return response()->json(['error' => $result['error']], 500);
        }

        // Parse the numbered list into an array
        $lines  = preg_split('/\r?\n/', trim($result['text']));
        $titles = [];
        foreach ($lines as $line) {
            $clean = trim(preg_replace('/^\d+[\.\)]\s*/', '', $line));
            if ($clean) {
                $titles[] = $clean;
            }
        }

        return response()->json(['titles' => $titles]);
    }

    // ── POST /admin/blog/ai/outline ──────────────────────────────────────────
    // Generate a structured outline for a topic
    public function outline(Request $request): JsonResponse
    {
        $request->validate(['topic' => 'required|string|max:500']);

        $topic  = $request->input('topic');
        $system = 'You are a blog strategist. Return the outline as HTML using <h2> for main sections '
            . 'and <ul><li> for sub-points. No introductory text — output HTML only.';

        $prompt = "Create a detailed blog post outline for: \"{$topic}\"\n\n"
            . "Include:\n"
            . "- 4-6 main sections (H2)\n"
            . "- 2-3 sub-points per section\n"
            . "- A brief note on what each section should cover";

        $result = $this->callGroq($system, $prompt, 800);
        if (isset($result['error'])) {
            return response()->json(['error' => $result['error']], 500);
        }

        return response()->json(['content' => $result['text']]);
    }

    // ── Internal: call Groq API ───────────────────────────────────────────────
    private function callGroq(string $system, string $prompt, int $maxTokens = 1024): array
    {
        if (empty($this->apiKey)) {
            return ['error' => 'GROQ_API_KEY is not configured. Please set it in Settings → Environment.'];
        }

        try {
            $response = Http::timeout(30)
                ->withToken($this->apiKey)
                ->post($this->apiUrl, [
                    'model'       => $this->model,
                    'temperature' => 0.7,
                    'max_tokens'  => $maxTokens,
                    'messages'    => [
                        ['role' => 'system', 'content' => $system],
                        ['role' => 'user',   'content' => $prompt],
                    ],
                ]);

            if ($response->failed()) {
                $body = $response->json();
                $msg  = $body['error']['message'] ?? 'Groq API request failed (HTTP ' . $response->status() . ')';
                Log::error('Groq API error', ['status' => $response->status(), 'body' => $body]);
                return ['error' => $msg];
            }

            $text = $response->json('choices.0.message.content', '');
            return ['text' => trim($text)];

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error('Groq connection error', ['message' => $e->getMessage()]);
            return ['error' => 'Could not reach Groq API. Check your internet connection.'];
        } catch (\Throwable $e) {
            Log::error('Groq unexpected error', ['message' => $e->getMessage()]);
            return ['error' => 'An unexpected error occurred while calling the AI.'];
        }
    }

    // ── POST /admin/blog/ai/seo-tags ─────────────────────────────────────────
    // Generate SEO meta keywords + description from post content
    public function seoTags(Request $request): JsonResponse
    {
        $request->validate(['content' => 'required|string|max:10000']);

        $content = mb_substr(strip_tags($request->input('content')), 0, 3000);

        $system = 'You are an SEO specialist. From blog content, generate SEO metadata. '
            . 'Return a JSON object with two keys: '
            . '"keywords" (array of 8-12 relevant keyword phrases) and '
            . '"meta_description" (string, max 155 characters, compelling, no HTML). '
            . 'Return ONLY valid JSON, no extra text.';

        $prompt = "Generate SEO metadata for this blog content:\n\n{$content}";

        $result = $this->callGroq($system, $prompt, 400);
        if (isset($result['error'])) {
            return response()->json(['error' => $result['error']], 500);
        }

        $decoded = json_decode($result['text'], true);
        if (!$decoded) {
            // Fallback: return raw text
            return response()->json(['raw' => $result['text']]);
        }

        return response()->json($decoded);
    }

    // ── POST /admin/blog/ai/social-caption ───────────────────────────────────
    // Generate platform-specific social media captions
    public function socialCaption(Request $request): JsonResponse
    {
        $request->validate([
            'title'    => 'required|string|max:300',
            'excerpt'  => 'nullable|string|max:500',
            'platform' => 'required|in:twitter,instagram,facebook,linkedin',
        ]);

        $title    = $request->input('title');
        $excerpt  = $request->input('excerpt', '');
        $platform = $request->input('platform');

        $limits = [
            'twitter'   => '280 characters max, use 2-3 hashtags',
            'instagram' => '150 characters caption + 10 relevant hashtags on new line',
            'facebook'  => '2-3 engaging sentences, no hashtag limit, conversational tone',
            'linkedin'  => '2-3 professional sentences, 3-5 industry hashtags, call to action',
        ];

        $system = 'You are a social media manager for KayXchange, a Nigerian crypto exchange. '
            . 'Write an engaging social media post caption for the given platform rules. '
            . 'Do NOT use markdown or HTML. Plain text only. Include relevant emojis.';

        $prompt = "Platform: {$platform} ({$limits[$platform]})\n"
            . "Blog title: {$title}\n"
            . ($excerpt ? "Excerpt: {$excerpt}\n" : '')
            . "\nWrite the caption:";

        $result = $this->callGroq($system, $prompt, 350);
        if (isset($result['error'])) {
            return response()->json(['error' => $result['error']], 500);
        }

        return response()->json(['caption' => $result['text']]);
    }

    // ── POST /admin/blog/ai/content-planner ──────────────────────────────────
    // Suggest 10 blog topic ideas with category + angle
    public function contentPlanner(Request $request): JsonResponse
    {
        $request->validate(['context' => 'nullable|string|max:500']);

        $context = $request->input('context', 'crypto exchange, Nigeria, fintech trends, Bitcoin, USDT');

        $system = 'You are a content strategist for a Nigerian crypto exchange blog. '
            . 'Suggest 10 blog topic ideas with a category label and unique angle. '
            . 'Return a JSON array of 10 objects, each with: '
            . '"title" (string), "category" (string e.g. Tutorial/News/Opinion), "angle" (1 sentence). '
            . 'Return ONLY valid JSON array, no extra text.';

        $prompt = "Suggest 10 blog post ideas for a crypto exchange blog. Context: {$context}";

        $result = $this->callGroq($system, $prompt, 800);
        if (isset($result['error'])) {
            return response()->json(['error' => $result['error']], 500);
        }

        $decoded = json_decode($result['text'], true);
        $topics  = is_array($decoded) ? $decoded : [];

        return response()->json(['topics' => $topics, 'raw' => $decoded ? null : $result['text']]);
    }
}
