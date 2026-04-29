<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminSetting;
use App\Models\AiChatMessage;
use App\Services\CloudflareService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;

class AdminSettingsController extends Controller
{
    private const GROUPS = ['cloudflare', 'ai', 'payment', 'telegram', 'general'];

    // ── Token limits & pricing per model (as of 2025) ──────────────────────────
    public const MODEL_INFO = [
        'gpt-4o' => [
            'name'         => 'GPT-4o',
            'context'      => 128000,
            'max_output'   => 16384,
            'input_cost'   => 2.50,   // USD per 1M tokens
            'output_cost'  => 10.00,
            'tier'         => 'Flagship',
            'color'        => '#a78bfa',
            'desc'         => 'Most capable multimodal model. Best quality.',
        ],
        'gpt-4o-mini' => [
            'name'         => 'GPT-4o mini',
            'context'      => 128000,
            'max_output'   => 16384,
            'input_cost'   => 0.15,
            'output_cost'  => 0.60,
            'tier'         => 'Affordable',
            'color'        => '#4ade80',
            'desc'         => 'Best price/performance. Recommended for chatbots.',
        ],
        'gpt-4-turbo' => [
            'name'         => 'GPT-4 Turbo',
            'context'      => 128000,
            'max_output'   => 4096,
            'input_cost'   => 10.00,
            'output_cost'  => 30.00,
            'tier'         => 'Premium',
            'color'        => '#f59e0b',
            'desc'         => 'High-quality with vision support.',
        ],
        'gpt-3.5-turbo' => [
            'name'         => 'GPT-3.5 Turbo',
            'context'      => 16385,
            'max_output'   => 4096,
            'input_cost'   => 0.50,
            'output_cost'  => 1.50,
            'tier'         => 'Legacy',
            'color'        => '#94a3b8',
            'desc'         => 'Fast and cheap. Good for simple tasks.',
        ],
        'o1-mini' => [
            'name'         => 'o1-mini',
            'context'      => 128000,
            'max_output'   => 65536,
            'input_cost'   => 1.10,
            'output_cost'  => 4.40,
            'tier'         => 'Reasoning',
            'color'        => '#38bdf8',
            'desc'         => 'Fast reasoning model. Great for math/logic.',
        ],
    ];

    // Default rate limits by OpenAI tier (RPM/TPM). Actual limits vary by account.
    public const TIER_LIMITS = [
        'Free'   => ['rpm' => 3,     'rpd' => 200,    'tpm' => 40000],
        'Tier 1' => ['rpm' => 500,   'rpd' => 10000,  'tpm' => 200000],
        'Tier 2' => ['rpm' => 5000,  'rpd' => null,   'tpm' => 2000000],
        'Tier 3' => ['rpm' => 5000,  'rpd' => null,   'tpm' => 4000000],
        'Tier 4' => ['rpm' => 10000, 'rpd' => null,   'tpm' => 10000000],
        'Tier 5' => ['rpm' => 30000, 'rpd' => null,   'tpm' => 150000000],
    ];

    // ── Groq model info ────────────────────────────────────────────────────────
    public const GROQ_MODEL_INFO = [
        'llama-3.3-70b-versatile' => [
            'name'        => 'Llama 3.3 70B',
            'context'     => 128000,
            'max_output'  => 32768,
            'input_cost'  => 0.59,    // USD per 1M tokens
            'output_cost' => 0.79,
            'tier'        => 'Flagship',
            'color'       => '#f97316',
            'desc'        => 'Best Groq model. High quality, very fast.',
            'speed'       => '~275 tokens/s',
        ],
        'llama-3.1-8b-instant' => [
            'name'        => 'Llama 3.1 8B',
            'context'     => 131072,
            'max_output'  => 8192,
            'input_cost'  => 0.05,
            'output_cost' => 0.08,
            'tier'        => 'Fastest',
            'color'       => '#4ade80',
            'desc'        => 'Extremely fast & cheap. Best for simple queries.',
            'speed'       => '~750 tokens/s',
        ],
        'mixtral-8x7b-32768' => [
            'name'        => 'Mixtral 8x7B',
            'context'     => 32768,
            'max_output'  => 32768,
            'input_cost'  => 0.24,
            'output_cost' => 0.24,
            'tier'        => 'Balanced',
            'color'       => '#a78bfa',
            'desc'        => 'Mixture-of-experts. Good balance of speed and quality.',
            'speed'       => '~480 tokens/s',
        ],
        'llama-3.3-70b-specdec' => [
            'name'        => 'Llama 3.3 70B (SpecDec)',
            'context'     => 8192,
            'max_output'  => 8192,
            'input_cost'  => 0.59,
            'output_cost' => 0.99,
            'tier'        => 'Speed+',
            'color'       => '#38bdf8',
            'desc'        => 'Speculative decoding — fastest 70B variant.',
            'speed'       => '~1600 tokens/s',
        ],
        'gemma2-9b-it' => [
            'name'        => 'Gemma 2 9B',
            'context'     => 8192,
            'max_output'  => 8192,
            'input_cost'  => 0.20,
            'output_cost' => 0.20,
            'tier'        => 'Compact',
            'color'       => '#fb923c',
            'desc'        => 'Google Gemma 2 — efficient and capable.',
            'speed'       => '~520 tokens/s',
        ],
    ];

    // Groq free-tier rate limits (per model, per minute)
    public const GROQ_RATE_LIMITS = [
        'llama-3.3-70b-versatile'   => ['rpm' => 30,  'rpd' => 1000, 'tpm' => 131072,  'tpd' => 131072],
        'llama-3.1-8b-instant'      => ['rpm' => 30,  'rpd' => 14400,'tpm' => 131072,  'tpd' => 500000],
        'mixtral-8x7b-32768'        => ['rpm' => 30,  'rpd' => 14400,'tpm' => 5000,    'tpd' => 500000],
        'llama-3.3-70b-specdec'     => ['rpm' => 30,  'rpd' => 1000, 'tpm' => 131072,  'tpd' => 131072],
        'gemma2-9b-it'              => ['rpm' => 30,  'rpd' => 14400,'tpm' => 15000,   'tpd' => 500000],
    ];

    // ── Index ──────────────────────────────────────────────────────────────────

    public function index()
    {
        AdminSetting::seedDefaults();

        $grouped = [];
        foreach (self::GROUPS as $g) {
            /** @var \Illuminate\Database\Eloquent\Builder $q */
            $q = AdminSetting::where('group', $g)->orderBy('id');
            $grouped[$g] = $q->get()->map(function ($row) {
                    // Return masked display value for encrypted fields
                    $display = '';
                    if ($row->value && $row->is_encrypted) {
                        try {
                            $plain = Crypt::decryptString($row->value);
                            $display = $plain ? str_repeat('•', min(8, strlen($plain) - 4)) . substr($plain, -4) : '';
                        } catch (\Exception $e) {
                            $display = '(encrypted)';
                        }
                    } elseif ($row->value) {
                        $display = $row->value;
                    }
                    $row->display_value = $display;
                    return $row;
                });
        }

        $cfStatus = null;
        if (AdminSetting::getSetting('cf_enabled') == '1') {
            try {
                $cfStatus = app(CloudflareService::class)->getZoneDetails();
            } catch (\Exception $e) {
                $cfStatus = ['error' => $e->getMessage()];
            }
        }

        return view('admin.settings.index', compact('grouped', 'cfStatus'));
    }

    public function update(Request $request)
    {
        $data = $request->except(['_token', '_method', 'action']);

        foreach ($data as $key => $value) {
            $row = AdminSetting::where('key', $key)->first();
            if (! $row) continue;

            // Skip blank submissions for encrypted fields (don't overwrite existing secret)
            if ($row->is_encrypted && ($value === null || $value === '')) continue;

            if ($row->is_encrypted && $value !== null && $value !== '') {
                $row->value = Crypt::encryptString((string) $value);
            } else {
                $row->value = $value;
            }
            $row->save();
        }

        return back()->with('success', 'Settings saved successfully.');
    }

    public function cloudflareAction(Request $request)
    {
        $action = $request->input('action');
        $cf = app(CloudflareService::class);

        $result = match ($action) {
            'purge_all'  => $cf->purgeAllCache(),
            'purge_url'  => $cf->purgeUrls(array_filter(explode("\n", $request->input('urls', '')))),
            'dev_mode_on'  => $cf->setDevelopmentMode(true),
            'dev_mode_off' => $cf->setDevelopmentMode(false),
            default => ['success' => false, 'message' => 'Unknown action'],
        };

        if ($request->expectsJson()) {
            return response()->json($result);
        }

        return back()->with(
            $result['success'] ? 'success' : 'error',
            $result['message'] ?? ($result['success'] ? 'Done.' : 'Failed.')
        );
    }

    // ── AI Usage & Limits Dashboard ────────────────────────────────────────────

    public function aiUsage()
    {
        $apiKey       = AdminSetting::getSetting('openai_api_key');
        $currentModel = AdminSetting::getSetting('openai_model', 'gpt-4o-mini');

        // Local chat usage stats from our DB
        $localStats = [
            'today'       => AiChatMessage::where('role', 'user')->whereDate('created_at', today())->count(),
            'this_week'   => AiChatMessage::where('role', 'user')->whereBetween('created_at', [now()->startOfWeek(), now()])->count(),
            'this_month'  => AiChatMessage::where('role', 'user')->whereMonth('created_at', now()->month)->count(),
            'total'       => AiChatMessage::where('role', 'user')->count(),
            'sessions_today' => AiChatMessage::where('role', 'user')->whereDate('created_at', today())->distinct('session_id')->count('session_id'),
            'avg_per_session' => 0,
            'top_hours'   => AiChatMessage::where('role', 'user')
                ->whereDate('created_at', today())
                ->selectRaw('HOUR(created_at) as hr, COUNT(*) as cnt')
                ->groupBy('hr')
                ->orderByDesc('cnt')
                ->limit(3)
                ->get(),
        ];

        if ($localStats['sessions_today'] > 0) {
            $localStats['avg_per_session'] = round($localStats['today'] / $localStats['sessions_today'], 1);
        }

        // Estimate cost: average message pair ≈ ~300 tokens in + ~250 tokens out
        $avgTokensIn  = 300;
        $avgTokensOut = 250;
        $modelInfo    = self::MODEL_INFO[$currentModel] ?? self::MODEL_INFO['gpt-4o-mini'];
        $costPerMsg   = (($avgTokensIn * $modelInfo['input_cost']) + ($avgTokensOut * $modelInfo['output_cost'])) / 1_000_000;

        $localStats['est_cost_today']  = round($costPerMsg * $localStats['today'], 4);
        $localStats['est_cost_month']  = round($costPerMsg * $localStats['this_month'], 4);

        // Try to fetch real rate limit headers from OpenAI
        $rateLimitHeaders = null;
        $connectionOk     = false;
        $orgInfo          = null;

        if ($apiKey) {
            try {
                $resp = Http::withHeaders([
                    'Authorization' => "Bearer {$apiKey}",
                ])->timeout(8)->get('https://api.openai.com/v1/models/' . $currentModel);

                $connectionOk = $resp->successful();

                // OpenAI sends rate limit info in response headers
                $rateLimitHeaders = [
                    'rpm_limit'      => $resp->header('x-ratelimit-limit-requests'),
                    'rpm_remaining'  => $resp->header('x-ratelimit-remaining-requests'),
                    'rpm_reset'      => $resp->header('x-ratelimit-reset-requests'),
                    'tpm_limit'      => $resp->header('x-ratelimit-limit-tokens'),
                    'tpm_remaining'  => $resp->header('x-ratelimit-remaining-tokens'),
                    'tpm_reset'      => $resp->header('x-ratelimit-reset-tokens'),
                    'org_id'         => $resp->header('openai-organization'),
                    'model_id'       => $resp->json('id'),
                    'owned_by'       => $resp->json('owned_by'),
                ];

            } catch (\Exception $e) {
                $rateLimitHeaders = ['error' => $e->getMessage()];
            }
        }

        // Groq live check
        $groqApiKey       = AdminSetting::getSetting('groq_api_key');
        $groqModel        = AdminSetting::getSetting('groq_model', 'llama-3.3-70b-versatile');
        $groqHeaders      = null;
        $groqConnectionOk = false;

        if ($groqApiKey) {
            try {
                $groqResp = Http::withHeaders(['Authorization' => "Bearer {$groqApiKey}"])
                    ->timeout(8)
                    ->get('https://api.groq.com/openai/v1/models/' . $groqModel);

                $groqConnectionOk = $groqResp->successful();
                $groqHeaders = [
                    'rpm_limit'      => $groqResp->header('x-ratelimit-limit-requests'),
                    'rpm_remaining'  => $groqResp->header('x-ratelimit-remaining-requests'),
                    'rpm_reset'      => $groqResp->header('x-ratelimit-reset-requests'),
                    'tpm_limit'      => $groqResp->header('x-ratelimit-limit-tokens'),
                    'tpm_remaining'  => $groqResp->header('x-ratelimit-remaining-tokens'),
                    'tpm_reset'      => $groqResp->header('x-ratelimit-reset-tokens'),
                    'rpd_limit'      => $groqResp->header('x-ratelimit-limit-requests-per-day'),
                    'rpd_remaining'  => $groqResp->header('x-ratelimit-remaining-requests-per-day'),
                    'model_id'       => $groqResp->json('id'),
                ];
            } catch (\Exception $e) {
                $groqHeaders = ['error' => $e->getMessage()];
            }
        }

        return view('admin.settings.ai-usage', [
            'modelInfo'        => self::MODEL_INFO,
            'tierLimits'       => self::TIER_LIMITS,
            'currentModel'     => $currentModel,
            'localStats'       => $localStats,
            'rateLimitHeaders' => $rateLimitHeaders,
            'connectionOk'     => $connectionOk,
            'apiKey'           => $apiKey,
            // Groq
            'groqModelInfo'    => self::GROQ_MODEL_INFO,
            'groqRateLimits'   => self::GROQ_RATE_LIMITS,
            'groqModel'        => $groqModel,
            'groqApiKey'       => $groqApiKey,
            'groqHeaders'      => $groqHeaders,
            'groqConnectionOk' => $groqConnectionOk,
            'activeProvider'   => AdminSetting::getSetting('ai_provider', 'openai'),
        ]);
    }

    /** AJAX: test OpenAI connection */
    public function aiTest()
    {
        $apiKey       = AdminSetting::getSetting('openai_api_key');
        $currentModel = AdminSetting::getSetting('openai_model', 'gpt-4o-mini');

        if (! $apiKey) {
            return response()->json(['ok' => false, 'error' => 'No OpenAI API key configured.']);
        }

        try {
            $resp = Http::withHeaders(['Authorization' => "Bearer {$apiKey}"])
                ->timeout(8)
                ->get('https://api.openai.com/v1/models/' . $currentModel);

            if (! $resp->successful()) {
                $err = $resp->json('error.message') ?? $resp->body();
                return response()->json(['ok' => false, 'error' => $err, 'status' => $resp->status()]);
            }

            return response()->json([
                'ok'            => true,
                'provider'      => 'openai',
                'model'         => $resp->json('id'),
                'owned_by'      => $resp->json('owned_by'),
                'rpm_limit'     => $resp->header('x-ratelimit-limit-requests'),
                'rpm_remaining' => $resp->header('x-ratelimit-remaining-requests'),
                'rpm_reset'     => $resp->header('x-ratelimit-reset-requests'),
                'tpm_limit'     => $resp->header('x-ratelimit-limit-tokens'),
                'tpm_remaining' => $resp->header('x-ratelimit-remaining-tokens'),
                'tpm_reset'     => $resp->header('x-ratelimit-reset-tokens'),
                'org_id'        => $resp->header('openai-organization'),
            ]);

        } catch (\Exception $e) {
            return response()->json(['ok' => false, 'error' => $e->getMessage()]);
        }
    }

    /** AJAX: test Groq connection */
    public function groqTest()
    {
        $apiKey    = AdminSetting::getSetting('groq_api_key');
        $model     = AdminSetting::getSetting('groq_model', 'llama-3.3-70b-versatile');

        if (! $apiKey) {
            return response()->json(['ok' => false, 'error' => 'No Groq API key configured.']);
        }

        try {
            $resp = Http::withHeaders(['Authorization' => "Bearer {$apiKey}"])
                ->timeout(8)
                ->get('https://api.groq.com/openai/v1/models/' . $model);

            if (! $resp->successful()) {
                $err = $resp->json('error.message') ?? $resp->body();
                return response()->json(['ok' => false, 'error' => $err, 'status' => $resp->status()]);
            }

            return response()->json([
                'ok'             => true,
                'provider'       => 'groq',
                'model'          => $resp->json('id'),
                'owned_by'       => $resp->json('owned_by'),
                'rpm_limit'      => $resp->header('x-ratelimit-limit-requests'),
                'rpm_remaining'  => $resp->header('x-ratelimit-remaining-requests'),
                'rpm_reset'      => $resp->header('x-ratelimit-reset-requests'),
                'tpm_limit'      => $resp->header('x-ratelimit-limit-tokens'),
                'tpm_remaining'  => $resp->header('x-ratelimit-remaining-tokens'),
                'tpm_reset'      => $resp->header('x-ratelimit-reset-tokens'),
                'rpd_limit'      => $resp->header('x-ratelimit-limit-requests-per-day'),
                'rpd_remaining'  => $resp->header('x-ratelimit-remaining-requests-per-day'),
            ]);

        } catch (\Exception $e) {
            return response()->json(['ok' => false, 'error' => $e->getMessage()]);
        }
    }

    // ── Fee Settings (deposit + withdrawal) ───────────────────────────────────
    public function saveFeeSettings(Request $request)
    {
        $request->validate([
            'key'   => ['required', 'string', 'in:deposit_fee_type,deposit_fee_value,withdrawal_fee_type,withdrawal_fee_value'],
            'value' => ['required', 'string'],
        ]);

        $key   = $request->input('key');
        $value = $request->input('value');

        // Extra validation per key type
        if (str_ends_with($key, '_type') && ! in_array($value, ['none', 'flat', 'percentage'])) {
            return response()->json(['success' => false, 'error' => 'Invalid fee type.'], 422);
        }
        if (str_ends_with($key, '_value') && (! is_numeric($value) || (float) $value < 0)) {
            return response()->json(['success' => false, 'error' => 'Fee value must be a non-negative number.'], 422);
        }

        AdminSetting::set($key, $value);

        return response()->json(['success' => true]);
    }
}
