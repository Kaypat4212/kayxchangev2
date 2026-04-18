<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\SiteContent;
use App\Services\TelegramAiBotService;

class AdminAiBotController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'admin']);
    }

    /**
     * Show the AI Bot configuration page.
     */
    public function index()
    {
        $settings = [
            'ai_bot_enabled'          => SiteContent::where('key', 'ai_bot_enabled')->value('value') ?? '1',
            'ai_bot_system_prompt'    => SiteContent::where('key', 'ai_bot_system_prompt')->value('value') ?? '',
            'ai_bot_model'            => SiteContent::where('key', 'ai_bot_model')->value('value') ?? 'llama-3.3-70b-versatile',
            'ai_bot_max_tokens'       => SiteContent::where('key', 'ai_bot_max_tokens')->value('value') ?? '600',
            'ai_bot_temperature'      => SiteContent::where('key', 'ai_bot_temperature')->value('value') ?? '0.6',
            'ai_bot_trade_suggestions'=> SiteContent::where('key', 'ai_bot_trade_suggestions')->value('value') ?? '1',
            'ai_bot_welcome_message'  => SiteContent::where('key', 'ai_bot_welcome_message')->value('value') ?? '',
        ];

        return view('admin.ai-bot-config', compact('settings'));
    }

    /**
     * Save AI Bot configuration.
     */
    public function update(Request $request)
    {
        $request->validate([
            'ai_bot_enabled'           => 'nullable|boolean',
            'ai_bot_system_prompt'     => 'nullable|string|max:3000',
            'ai_bot_model'             => 'required|string|max:100',
            'ai_bot_max_tokens'        => 'required|integer|min:100|max:4096',
            'ai_bot_temperature'       => 'required|numeric|min:0|max:2',
            'ai_bot_trade_suggestions' => 'nullable|boolean',
            'ai_bot_welcome_message'   => 'nullable|string|max:1000',
        ]);

        $fields = [
            'ai_bot_enabled'           => $request->boolean('ai_bot_enabled') ? '1' : '0',
            'ai_bot_system_prompt'     => $request->input('ai_bot_system_prompt', ''),
            'ai_bot_model'             => $request->input('ai_bot_model'),
            'ai_bot_max_tokens'        => (string) $request->integer('ai_bot_max_tokens'),
            'ai_bot_temperature'       => (string) number_format((float) $request->input('ai_bot_temperature'), 2),
            'ai_bot_trade_suggestions' => $request->boolean('ai_bot_trade_suggestions') ? '1' : '0',
            'ai_bot_welcome_message'   => $request->input('ai_bot_welcome_message', ''),
        ];

        $labels = [
            'ai_bot_enabled'           => 'AI Bot Enabled',
            'ai_bot_system_prompt'     => 'AI Bot System Prompt',
            'ai_bot_model'             => 'AI Bot Model',
            'ai_bot_max_tokens'        => 'AI Bot Max Tokens',
            'ai_bot_temperature'       => 'AI Bot Temperature',
            'ai_bot_trade_suggestions' => 'AI Trade Suggestions',
            'ai_bot_welcome_message'   => 'AI Bot Welcome Message',
        ];

        foreach ($fields as $key => $value) {
            SiteContent::updateOrCreate(
                ['key' => $key],
                ['value' => $value, 'group' => 'ai_bot', 'label' => $labels[$key]]
            );
        }

        Log::info('Admin updated AI Bot config', ['admin' => auth()->id()]);

        return redirect()->back()->with('success', 'AI Bot configuration saved successfully.');
    }

    /**
     * Test the AI Bot with a sample message (AJAX).
     */
    public function testChat(Request $request)
    {
        $request->validate(['message' => 'required|string|max:500']);

        $aiService = app(TelegramAiBotService::class);

        if (empty(config('services.groq.api_key', env('GROQ_API_KEY', '')))) {
            return response()->json(['error' => 'GROQ_API_KEY is not configured.'], 422);
        }

        // Use a dummy admin user object for the test
        $adminUser = auth()->user();

        try {
            // Use a temp chat ID for testing so it doesn't pollute real user histories
            $testChatId = -99999;
            $aiService->clearHistory($testChatId);
            $reply = $aiService->chat($testChatId, $adminUser, $request->input('message'));
            $aiService->clearHistory($testChatId);

            return response()->json(['reply' => $reply]);
        } catch (\Exception $e) {
            Log::error('AdminAiBotController testChat error', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Test failed: ' . $e->getMessage()], 500);
        }
    }
}
