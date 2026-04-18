<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use App\Models\BugReport;
use App\Models\FeatureRequest;

class AdminTerminalController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'admin']);
    }

    // ─────────────────────────── PIN management ───────────────────────────────

    /**
     * Check whether the current session has a valid terminal PIN unlock.
     */
    private function isPinUnlocked(): bool
    {
        return session('terminal_pin_unlocked_at') &&
               now()->diffInMinutes(session('terminal_pin_unlocked_at')) < 30;
    }

    /**
     * Verify the submitted PIN against the one stored in .env / config.
     */
    private function checkPin(string $pin): bool
    {
        $stored = env('ADMIN_TERMINAL_PIN', '');
        if (empty($stored)) {
            return false; // PIN not configured — disallow all access
        }
        // Support both plain text and bcrypt-hashed PINs
        if (str_starts_with($stored, '$2y$') || str_starts_with($stored, '$2b$')) {
            return Hash::check($pin, $stored);
        }
        return hash_equals($stored, $pin);
    }

    // ─────────────────────────── Main page ────────────────────────────────────

    public function index()
    {
        $unlocked = $this->isPinUnlocked();
        return view('admin.terminal', compact('unlocked'));
    }

    // ─────────────────────────── PIN unlock ───────────────────────────────────

    public function unlock(Request $request)
    {
        $request->validate(['pin' => 'required|string|min:4|max:32']);

        // Rate-limit: 5 attempts per 10 minutes per admin
        $cacheKey = 'terminal_pin_attempts_' . auth()->id();
        $attempts = Cache::get($cacheKey, 0);

        if ($attempts >= 5) {
            return back()->with('pin_error', '⛔ Too many incorrect attempts. Please try again in 10 minutes.');
        }

        if (!$this->checkPin($request->pin)) {
            Cache::put($cacheKey, $attempts + 1, now()->addMinutes(10));
            Log::warning('Terminal PIN failed', [
                'admin_id' => auth()->id(),
                'ip'       => $request->ip(),
                'attempts' => $attempts + 1,
            ]);
            $remaining = max(0, 4 - $attempts);
            return back()
                ->with('pin_error', '❌ Incorrect PIN.')
                ->with('pin_attempts_left', $remaining);
        }

        // Correct — unlock
        Cache::forget($cacheKey);
        session(['terminal_pin_unlocked_at' => now()]);

        Log::info('Terminal PIN unlocked', [
            'admin_id' => auth()->id(),
            'ip'       => $request->ip(),
        ]);

        return redirect()->route('admin.terminal')->with('success', '✅ Terminal unlocked.');
    }

    public function lock(Request $request)
    {
        session()->forget('terminal_pin_unlocked_at');
        return redirect()->route('admin.terminal')->with('success', '🔒 Terminal locked.');
    }

    // ─────────────────────────── Artisan runner ───────────────────────────────

    /**
     * Allowed artisan commands (allowlist for security).
     * Only these commands may be executed.
     */
    private const ALLOWED_ARTISAN = [
        'migrate',
        'migrate:status',
        'migrate:rollback',
        'db:seed',
        'cache:clear',
        'config:clear',
        'config:cache',
        'route:clear',
        'route:cache',
        'view:clear',
        'view:cache',
        'queue:restart',
        'storage:link',
        'optimize',
        'optimize:clear',
        'schedule:run',
        'about',
        'inspire',
        'telescope:clear',
        'telescope:prune',
        'down',
        'up',
        'telegram:setup',
    ];

    public function runArtisan(Request $request)
    {
        if (!$this->isPinUnlocked()) {
            return response()->json(['output' => '⛔ Terminal locked. Enter PIN first.'], 403);
        }

        $request->validate(['command' => 'required|string|max:500']);

        $input   = trim($request->command);
        // Strip leading "php artisan " or "artisan " if user typed it
        $input   = preg_replace('/^(php\s+)?artisan\s+/', '', $input);
        $parts   = explode(' ', $input, 2);
        $command = $parts[0];
        $args    = $parts[1] ?? '';

        // Allowlist check
        if (!in_array($command, self::ALLOWED_ARTISAN, true)) {
            Log::warning('Terminal: blocked artisan command', [
                'admin_id' => auth()->id(),
                'command'  => $input,
            ]);
            return response()->json([
                'output' => "⛔ Command [{$command}] is not in the allowed list.\n\nAllowed: " . implode(', ', self::ALLOWED_ARTISAN),
            ], 403);
        }

        Log::info('Terminal: running artisan command', [
            'admin_id' => auth()->id(),
            'command'  => $input,
        ]);

        try {
            $exitCode = Artisan::call($command . ($args ? ' ' . $args : ''), [], new \Symfony\Component\Console\Output\BufferedOutput());
            $output   = Artisan::output();

            return response()->json([
                'output'    => $output ?: "✅ Done. Exit code: {$exitCode}",
                'exit_code' => $exitCode,
            ]);
        } catch (\Exception $e) {
            Log::error('Terminal: artisan command exception', [
                'command' => $input,
                'error'   => $e->getMessage(),
            ]);
            return response()->json([
                'output' => "❌ Error: " . $e->getMessage(),
            ], 500);
        }
    }

    // ─────────────────────────── Admin: Bug Reports ───────────────────────────

    public function bugReports(Request $request)
    {
        $query = BugReport::with('user')->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('severity')) {
            $query->where('severity', $request->severity);
        }
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($uq) use ($search) {
                        $uq->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        $reports = $query->paginate(20)->appends($request->query());
        $stats  = [
            'open'          => BugReport::where('status', 'open')->count(),
            'investigating' => BugReport::where('status', 'investigating')->count(),
            'resolved'      => BugReport::where('status', 'resolved')->count(),
            'closed'        => BugReport::where('status', 'closed')->count(),
        ];

        return view('admin.bug-reports', compact('reports', 'stats'));
    }

    public function updateBugReport(Request $request, BugReport $bugReport)
    {
        $request->validate([
            'status'      => 'required|in:open,investigating,resolved,closed',
            'admin_notes' => 'nullable|string|max:2000',
        ]);

        $bugReport->update([
            'status'      => $request->status,
            'admin_notes' => $request->admin_notes,
            'resolved_at' => in_array($request->status, ['resolved', 'closed']) ? now() : null,
        ]);

        return response()->json(['success' => true, 'status' => $bugReport->status]);
    }

    // ─────────────────────────── Admin: Feature Requests ─────────────────────

    public function featureRequests(Request $request)
    {
        $query = FeatureRequest::with('user')->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($uq) use ($search) {
                        $uq->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        $requests = $query->paginate(20)->appends($request->query());
        $stats   = [
            'pending'   => FeatureRequest::where('status', 'pending')->count(),
            'in_review' => FeatureRequest::where('status', 'in_review')->count(),
            'planned'   => FeatureRequest::where('status', 'planned')->count(),
            'completed' => FeatureRequest::where('status', 'completed')->count(),
            'rejected'  => FeatureRequest::where('status', 'rejected')->count(),
        ];

        return view('admin.feature-requests', compact('requests', 'stats'));
    }

    public function updateFeatureRequest(Request $request, FeatureRequest $featureRequest)
    {
        $request->validate([
            'status'      => 'required|in:pending,in_review,planned,completed,rejected',
            'admin_notes' => 'nullable|string|max:2000',
        ]);

        $featureRequest->update([
            'status'      => $request->status,
            'admin_notes' => $request->admin_notes,
        ]);

        return response()->json(['success' => true, 'status' => $featureRequest->status]);
    }
}
