<?php

namespace App\Http\Controllers;

use App\Models\CryptoDeposit;
use App\Models\SupportCase;
use App\Models\EmailSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;

class SupportCaseController extends Controller
{
    public function createDeposit(CryptoDeposit $deposit)
    {
        if ((int) $deposit->user_id !== (int) auth()->id()) abort(404);
        try {
            $deposit = app(\App\Services\CryptoWalletService::class)->refreshDepositStatus($deposit);
        } catch (\Throwable $exception) {
            Log::warning('Could not refresh crypto deposit before showing its support form.', [
                'deposit_id' => $deposit->id,
                'error' => $exception->getMessage(),
            ]);
        }
        return view('support.deposit-case', compact('deposit'));
    }

    public function index()
    {
        $cases = SupportCase::where('user_id', auth()->id())->latest()->paginate(15);
        return view('support.cases', compact('cases'));
    }

    public function show(SupportCase $supportCase)
    {
        abort_unless((int)$supportCase->user_id === (int)auth()->id(), 404);
        $supportCase->load(['deposit', 'messages']);
        return view('support.case-detail', compact('supportCase'));
    }

    public function reply(Request $request, SupportCase $supportCase)
    {
        abort_unless((int)$supportCase->user_id === (int)$request->user()->id, 404);
        $data = $request->validate(['message' => 'required|string|min:2|max:4000']);

        DB::transaction(function () use ($request, $supportCase, $data): void {
            $case = SupportCase::query()->lockForUpdate()->findOrFail($supportCase->id);
            $this->ensureLegacyConversation($case);
            $case->messages()->create([
                'sender_id' => $request->user()->id,
                'sender_role' => 'user',
                'message' => trim($data['message']),
            ]);
            if ($case->status === 'resolved') {
                $case->status = 'in_progress';
                $case->resolved_at = null;
                $case->save();
            }
        });

        foreach (\App\Models\User::where('is_admin', true)->get(['id']) as $admin) {
            \App\Models\Notification::create([
                'admin_id' => $admin->id,
                'type' => 'info',
                'title' => 'Customer replied to a support case',
                'message' => 'A new reply was added to case #' . $supportCase->id . '.',
                'data' => ['support_case_id' => $supportCase->id, 'url' => route('admin.support-cases.index')],
            ]);
        }

        return back()->with('success', 'Your reply was sent to the support team.');
    }

    public function resolve(SupportCase $supportCase)
    {
        abort_unless((int)$supportCase->user_id === (int)auth()->id(), 404);
        if ($supportCase->status !== 'resolved') {
            $supportCase->update(['status' => 'resolved', 'resolved_at' => now()]);
            foreach (\App\Models\User::where('is_admin', true)->get(['id']) as $admin) {
                \App\Models\Notification::create([
                    'admin_id' => $admin->id,
                    'type' => 'success',
                    'title' => 'Customer marked support case resolved',
                    'message' => 'The customer marked case #' . $supportCase->id . ' as resolved.',
                    'data' => ['support_case_id' => $supportCase->id, 'url' => route('admin.support-cases.index')],
                ]);
            }
        }
        return back()->with('success', 'Case marked as resolved. You can still reply to reopen it.');
    }

    public function store(Request $request, ?CryptoDeposit $deposit = null)
    {
        if ($deposit && (int) $deposit->user_id !== (int) auth()->id()) abort(404);
        $data = $request->validate([
            'subject' => 'required|string|max:160',
            'message' => 'required|string|min:10|max:4000',
            'category' => 'nullable|in:deposit,withdrawal,swap,bill,complaint,general',
        ]);
        if (SupportCase::where('user_id', auth()->id())->whereIn('status', ['open', 'in_progress'])->count() >= 5) {
            return back()->withErrors(['case' => 'You have reached the limit of five open support cases. Please wait for a response on an existing case.'])->withInput();
        }
        $case = SupportCase::create([
            'user_id' => auth()->id(), 'crypto_deposit_id' => $deposit?->id,
            'category' => $deposit ? 'deposit' : ($data['category'] ?? 'general'),
            'subject' => $data['subject'], 'message' => $data['message'],
        ]);
        $case->messages()->create([
            'sender_id' => $request->user()->id,
            'sender_role' => 'user',
            'message' => $data['message'],
        ]);
        $isComplaint = $case->category === 'complaint';
        foreach (\App\Models\User::where('is_admin', true)->get(['id']) as $admin) {
            \App\Models\Notification::create([
                'admin_id' => $admin->id, 'type' => $isComplaint ? 'warning' : 'info', 'title' => $isComplaint ? 'New customer complaint' : 'New customer support case',
                'message' => 'Case #' . $case->id . ': ' . $case->subject,
                'data' => ['support_case_id' => $case->id, 'category' => $case->category, 'url' => route('admin.support-cases.index')],
            ]);
        }
        \App\Models\Notification::createForUser(
            (int) $case->user_id,
            'success',
            $isComplaint ? 'Complaint received' : 'Support request received',
            ($isComplaint ? 'Your complaint' : 'Your support request') . ' #' . $case->id . ' has been sent to our support team. We will notify you when an admin replies.',
            ['support_case_id' => $case->id, 'category' => $case->category, 'url' => route('support.cases.show', $case)]
        );

        $this->notifySupportTelegram($case, $request->user()->name ?? 'Customer');
        $this->notifySupportEmail($case, $request->user()->name ?? 'Customer', $request->user()->email ?? '');

        return redirect()->route('support.cases.index')->with('success', ($isComplaint ? 'Complaint #' : 'Support case #') . $case->id . ' was sent to support and the admin team.');
    }

    public function adminIndex()
    {
        $cases = SupportCase::with(['user', 'deposit', 'assignedAdmin', 'messages'])->latest()->paginate(25);
        return view('admin.support-cases', compact('cases'));
    }

    public function respond(Request $request, SupportCase $supportCase)
    {
        $data = $request->validate([
            'admin_response' => 'required|string|min:2|max:4000',
            'status' => 'required|in:in_progress,resolved',
        ]);
        $this->ensureLegacyConversation($supportCase);
        $supportCase->update([
            ...$data, 'assigned_admin_id' => auth()->id(),
            'resolved_at' => $data['status'] === 'resolved' ? now() : null,
        ]);
        $supportCase->messages()->create([
            'sender_id' => auth()->id(),
            'sender_role' => 'admin',
            'message' => trim($data['admin_response']),
        ]);
        \App\Models\Notification::createForUser(
            (int) $supportCase->user_id,
            'info',
            'Support replied to your case',
            'A response was added to support case #' . $supportCase->id . '.',
            ['support_case_id' => $supportCase->id, 'url' => route('support.cases.show', $supportCase)]
        );
        return back()->with('success', 'Support case updated.');
    }

    private function ensureLegacyConversation(SupportCase $case): void
    {
        if ($case->messages()->exists()) return;
        $original = $case->messages()->create([
            'sender_id' => $case->user_id,
            'sender_role' => 'user',
            'message' => $case->message,
        ]);
        $original->created_at = $case->created_at;
        $original->save();
        if (trim((string)$case->admin_response) !== '') {
            $legacyReply = $case->messages()->create([
                'sender_id' => $case->assigned_admin_id,
                'sender_role' => 'admin',
                'message' => $case->admin_response,
            ]);
            $legacyReply->created_at = $case->updated_at;
            $legacyReply->save();
        }
    }

    private function notifySupportTelegram(SupportCase $case, string $customerName): void
    {
        $token = (string) config('services.telegram.token', '');
        $chatId = (string) (config('services.telegram.owner_chat_id') ?: config('services.telegram.chat_id', ''));
        if ($token === '' || $chatId === '') return;
        try {
            $kind = $case->category === 'complaint' ? 'CUSTOMER COMPLAINT' : 'CUSTOMER SUPPORT CASE';
            $escape = static fn (string $value): string => str_replace(['_', '*', '[', ']', '`'], ['\\_', '\\*', '\\[', '\\]', '\\`'], $value);
            $response = \Illuminate\Support\Facades\Http::timeout(6)->post("https://api.telegram.org/bot{$token}/sendMessage", [
                'chat_id' => $chatId,
                'parse_mode' => 'Markdown',
                'text' => "📩 *New {$kind}*\n\n"
                    . 'Case: #' . $case->id . "\n"
                    . 'Customer: ' . $escape($customerName) . "\n"
                    . 'Category: ' . $escape(ucfirst($case->category)) . "\n"
                    . 'Subject: ' . $escape($case->subject) . "\n"
                    . 'Review: ' . url('/admin/support-cases'),
            ]);
            if (!$response->successful() || !$response->json('ok')) {
                \Illuminate\Support\Facades\Log::warning('Support case Telegram alert rejected', ['case_id' => $case->id, 'http_status' => $response->status()]);
            }
        } catch (\Throwable $exception) {
            \Illuminate\Support\Facades\Log::warning('Support case Telegram alert failed', ['case_id' => $case->id, 'error' => $exception->getMessage()]);
        }
    }

    private function notifySupportEmail(SupportCase $case, string $customerName, string $customerEmail): void
    {
        try {
            $settings = EmailSetting::query()->first();
            $supportEmail = trim((string) ($settings?->support_email ?? ''));
            if ($supportEmail === '') return;
            if ($settings && $settings->mail_host) {
                config([
                    'mail.default' => $settings->mail_mailer ?: 'smtp',
                    'mail.mailers.smtp.host' => $settings->mail_host,
                    'mail.mailers.smtp.port' => $settings->mail_port,
                    'mail.mailers.smtp.username' => $settings->mail_username,
                    'mail.mailers.smtp.password' => $settings->mail_password ? decrypt($settings->mail_password) : null,
                    'mail.mailers.smtp.encryption' => $settings->mail_encryption ?: null,
                    'mail.from.address' => $settings->mail_from_address,
                    'mail.from.name' => $settings->mail_from_name ?: config('app.name'),
                ]);
            }
            $kind = $case->category === 'complaint' ? 'Customer complaint' : 'Customer support case';
            Mail::raw(
                "{$kind} #{$case->id}\n\nCustomer: {$customerName} ({$customerEmail})\nCategory: {$case->category}\nSubject: {$case->subject}\n\n{$case->message}\n\nReview in admin: " . url('/admin/support-cases'),
                function ($message) use ($supportEmail, $case, $kind): void {
                    $message->to($supportEmail)->subject('[KayXchange ' . $kind . ' #' . $case->id . '] ' . $case->subject);
                }
            );
        } catch (\Throwable $exception) {
            Log::warning('Support case email notification failed', ['case_id' => $case->id, 'error' => $exception->getMessage()]);
        }
    }
}
