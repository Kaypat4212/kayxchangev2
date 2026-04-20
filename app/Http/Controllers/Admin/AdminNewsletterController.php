<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\NewsletterWelcomeMail;
use App\Models\NewsletterSubscriber;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminNewsletterController extends Controller
{
    public function index(Request $request)
    {
        $query = NewsletterSubscriber::query();

        if ($request->filled('search')) {
            $q = '%' . $request->search . '%';
            $query->where(fn($q2) => $q2->where('email', 'like', $q)->orWhere('name', 'like', $q));
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $subscribers = $query->latest('subscribed_at')->paginate(30)->withQueryString();

        $stats = [
            'total'   => NewsletterSubscriber::count(),
            'active'  => NewsletterSubscriber::where('is_active', true)->count(),
            'today'   => NewsletterSubscriber::whereDate('subscribed_at', today())->count(),
        ];

        return view('admin.newsletter.index', compact('subscribers', 'stats'));
    }

    public function export()
    {
        $filename = 'newsletter-subscribers-' . now()->format('Y-m-d') . '.csv';

        return new StreamedResponse(function () {
            $handle = fopen('php://output', 'w');
            // BOM for Excel UTF-8 compatibility
            fputs($handle, "\xEF\xBB\xBF");
            fputcsv($handle, ['Name', 'Email', 'Status', 'Subscribed At', 'Unsubscribed At']);

            NewsletterSubscriber::orderByDesc('subscribed_at')
                ->chunk(500, function ($rows) use ($handle) {
                    foreach ($rows as $row) {
                        fputcsv($handle, [
                            $row->name ?? '',
                            $row->email,
                            $row->is_active ? 'Active' : 'Unsubscribed',
                            $row->subscribed_at?->format('Y-m-d H:i'),
                            $row->unsubscribed_at?->format('Y-m-d H:i') ?? '',
                        ]);
                    }
                });

            fclose($handle);
        }, 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Cache-Control'       => 'no-cache, no-store',
            'Pragma'              => 'no-cache',
        ]);
    }

    public function destroy(NewsletterSubscriber $subscriber)
    {
        $subscriber->delete();
        return back()->with('success', "Subscriber {$subscriber->email} deleted.");
    }

    public function sendCampaign(Request $request)
    {
        $data = $request->validate([
            'subject' => 'required|string|max:200',
            'body'    => 'required|string',
        ]);

        $recipients = NewsletterSubscriber::where('is_active', true)->get();

        if ($recipients->isEmpty()) {
            return back()->withErrors(['campaign' => 'No active subscribers to send to.']);
        }

        $sent   = 0;
        $failed = 0;

        foreach ($recipients as $sub) {
            try {
                Mail::send([], [], function ($mail) use ($sub, $data) {
                    $mail->to($sub->email, $sub->name ?? '')
                         ->subject($data['subject'])
                         ->html($this->buildCampaignHtml($data['subject'], $data['body'], $sub->email));
                });
                $sent++;
            } catch (\Throwable $e) {
                $failed++;
                Log::warning("Campaign mail failed for {$sub->email}: " . $e->getMessage());
            }
        }

        return back()->with('success', "Campaign sent to {$sent} subscriber(s)." . ($failed ? " {$failed} failed." : ''));
    }

    private function buildCampaignHtml(string $subject, string $body, string $email): string
    {
        $appName    = config('app.name');
        $appUrl     = config('app.url');
        $unsubUrl   = $appUrl . '/newsletter/unsubscribe?email=' . urlencode($email);
        $year       = date('Y');
        $bodyHtml   = nl2br(e($body));

        return <<<HTML
<!DOCTYPE html>
<html><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"></head>
<body style="margin:0;padding:0;background:#0d1117;font-family:'Segoe UI',Helvetica,Arial,sans-serif;color:#e4e8f0;">
<table width="100%" cellpadding="0" cellspacing="0" style="background:#0d1117;padding:32px 0;">
<tr><td align="center" style="padding:0 16px;">
<table width="600" cellpadding="0" cellspacing="0" style="max-width:600px;width:100%;">
  <tr><td style="background:linear-gradient(135deg,#0a1628,#0d1f1a);border-radius:16px 16px 0 0;padding:28px 40px 22px;text-align:center;border:1px solid rgba(255,255,255,0.08);border-bottom:none;">
    <div style="font-size:22px;font-weight:800;color:#00cc00;">{$appName}</div>
    <div style="color:#7a8599;font-size:13px;margin-top:5px;">Secure Crypto Exchange &middot; Nigeria</div>
  </td></tr>
  <tr><td style="background:#161b27;padding:36px 40px;border:1px solid rgba(255,255,255,0.08);border-top:none;border-bottom:none;">
    <h2 style="color:#fff;font-size:20px;font-weight:700;margin:0 0 20px;">{$subject}</h2>
    <div style="color:#e4e8f0;font-size:14px;line-height:1.9;">{$bodyHtml}</div>
    <div style="margin-top:32px;">
      <a href="{$appUrl}" style="display:inline-block;background:#00cc00;color:#081108;font-size:14px;font-weight:700;text-decoration:none;padding:12px 30px;border-radius:8px;">Trade Now &rarr;</a>
    </div>
  </td></tr>
  <tr><td style="background:#0d1117;border-radius:0 0 16px 16px;padding:20px 40px;text-align:center;border:1px solid rgba(255,255,255,0.08);border-top:none;">
    <p style="color:#4a5568;font-size:12px;margin:0;">&copy; {$year} {$appName} &bull; <a href="{$unsubUrl}" style="color:#4a5568;">Unsubscribe</a></p>
  </td></tr>
</table></td></tr></table>
</body></html>
HTML;
    }
}
