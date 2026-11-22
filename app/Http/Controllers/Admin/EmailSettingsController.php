<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\TestEmail;
use App\Models\EmailSetting;
use App\Models\EmailTemplate;
use App\Models\LoginLog;
use App\Models\User;
use App\Listeners\TrackOutboundMail;
use App\Services\EmailTransportConfigurator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class EmailSettingsController extends Controller
{
    public function index(Request $request)
    {
        $settings = EmailSetting::current();

        $logsQuery = LoginLog::with('user')->latest();
        if ($request->filled('status')) {
            $logsQuery->where('status', $request->status);
        }
        if ($request->filled('search')) {
            $logsQuery->where('email', 'like', '%' . $request->search . '%');
        }
        $logs = $logsQuery->paginate(20)->appends($request->query());

        $totalAttempts = LoginLog::count();
        $totalSuccess  = LoginLog::where('status', 'success')->count();
        $totalFailed   = LoginLog::where('status', 'failed')->count();

        return view('admin.email-settings', compact(
            'settings', 'logs', 'totalAttempts', 'totalSuccess', 'totalFailed'
        ));
    }

    public function update(Request $request)
    {
        $request->validate([
            'mail_mailer'                  => 'required|string|max:50',
            'mail_host'                    => 'required|string|max:255',
            'mail_port'                    => 'required|integer|min:1|max:65535',
            'mail_username'                => 'required|string|max:255',
            'mail_from_address'            => 'required|email|max:255',
            'mail_from_name'               => 'required|string|max:255',
            'mail_encryption'              => 'nullable|in:tls,ssl,',
            'support_email'                => 'nullable|email|max:255',
            'security_email'               => 'nullable|email|max:255',
            'welcome_email_enabled'        => 'sometimes|boolean',
            'login_success_email_enabled'  => 'sometimes|boolean',
            'login_failed_email_enabled'   => 'sometimes|boolean',
        ]);

        $settings = EmailSetting::current();

        $data = $request->only([
            'mail_mailer',
            'mail_host',
            'mail_port',
            'mail_username',
            'mail_from_address',
            'mail_from_name',
            'mail_encryption',
            'support_email',
            'security_email',
        ]);

        // Only update password if provided
        if ($request->filled('mail_password')) {
            $data['mail_password'] = encrypt($request->mail_password);
        }

        // Checkbox booleans
        $data['welcome_email_enabled']        = $request->boolean('welcome_email_enabled');
        $data['login_success_email_enabled']  = $request->boolean('login_success_email_enabled');
        $data['login_failed_email_enabled']   = $request->boolean('login_failed_email_enabled');

        $settings->update($data);

        return back()->with('success', 'Email settings saved successfully.');
    }

    public function sendTest(Request $request, EmailTransportConfigurator $transport)
    {
        $request->validate([
            'test_email' => 'required|email|max:255',
        ]);

        try {
            $settings = EmailSetting::current();
            $transport->applySavedSmtpSettings();

            // Warn if FROM domain doesn't match SMTP username domain (cPanel anti-spoofing)
            if ($settings->mail_username && $settings->mail_from_address) {
                $usernameDomain = substr(strrchr($settings->mail_username, '@'), 1);
                $fromDomain     = substr(strrchr($settings->mail_from_address, '@'), 1);
                if ($usernameDomain && $fromDomain && strtolower($usernameDomain) !== strtolower($fromDomain)) {
                    Log::warning('Email FROM domain mismatch — cPanel may reject this', [
                        'smtp_username' => $settings->mail_username,
                        'from_address'  => $settings->mail_from_address,
                    ]);
                }
            }

            Mail::to($request->test_email)->send(new TestEmail($request->test_email));

            return back()->with('success', 'Test email sent successfully to ' . $request->test_email);
        } catch (\Throwable $e) {
            TrackOutboundMail::markLatestFailure($request->test_email, 'SMTP Configuration Test', $e->getMessage());
            Log::error('Test email failed.', ['exception_class' => $e::class, 'recipient' => '[redacted]']);
            return back()->with('error', 'The SMTP test failed. Open Mail Delivery to inspect the redacted provider response and suggested fixes.');
        }
    }

    // ─── Email Templates ────────────────────────────────────────────
    public function templates()
    {
        $templates = EmailTemplate::orderBy('key')->get();
        return view('admin.email-templates', compact('templates'));
    }

    public function editTemplate(string $key)
    {
        $template = EmailTemplate::where('key', $key)->firstOrFail();
        return view('admin.email-template-edit', compact('template'));
    }

    public function updateTemplate(Request $request, string $key)
    {
        $template = EmailTemplate::where('key', $key)->firstOrFail();

        $request->validate([
            'subject' => 'required|string|max:255',
            'body'    => 'required|string',
        ]);

        $template->update([
            'subject' => $request->subject,
            'body'    => $request->body,
        ]);

        return redirect()->route('admin.email-templates')
            ->with('success', 'Template "' . $template->key . '" updated successfully.');
    }
}
