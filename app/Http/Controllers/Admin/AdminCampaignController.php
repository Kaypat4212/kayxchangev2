<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminCampaign;
use App\Models\BuyTrade;
use App\Models\CryptoDeposit;
use App\Models\Deposit;
use App\Models\NewsletterSubscriber;
use App\Models\Notification;
use App\Models\SellTrade;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class AdminCampaignController extends Controller
{
    public function index()
    {
        $counts = [
            'users' => User::where('is_admin', false)->count(),
            'transacting_users' => $this->transactingUsers()->count(),
            'newsletter_subscribers' => NewsletterSubscriber::where('is_active', true)->count(),
        ];
        $campaigns = AdminCampaign::with('creator')->latest()->paginate(20);

        return view('admin.campaigns.index', compact('counts', 'campaigns'));
    }

    public function send(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:160',
            'body' => 'required|string|max:5000',
            'channel' => 'required|in:in_app,email',
            'audience' => 'required|in:users,transacting_users,newsletter_subscribers',
        ]);

        if ($data['channel'] === 'email' && $data['audience'] !== 'newsletter_subscribers') {
            return back()->withInput()->withErrors(['audience' => 'Email campaigns can only go to active newsletter subscribers who opted in.']);
        }
        if ($data['channel'] === 'in_app' && $data['audience'] === 'newsletter_subscribers') {
            return back()->withInput()->withErrors(['audience' => 'Choose registered users for an in-app campaign.']);
        }

        $targetCount = match ($data['audience']) {
            'users' => User::where('is_admin', false)->count(),
            'transacting_users' => $this->transactingUsers()->count(),
            default => NewsletterSubscriber::where('is_active', true)->count(),
        };
        if ($targetCount === 0) {
            return back()->withInput()->withErrors(['campaign' => 'This audience has no eligible recipients.']);
        }

        $campaign = AdminCampaign::create([
            'created_by' => $request->user()->id,
            'title' => $data['title'],
            'body' => $data['body'],
            'channel' => $data['channel'],
            'audience' => $data['audience'],
            'status' => 'sending',
            'target_count' => $targetCount,
        ]);

        $sent = 0;
        $failed = 0;

        if ($data['channel'] === 'in_app') {
            $recipients = $data['audience'] === 'transacting_users'
                ? $this->transactingUsers()
                : User::where('is_admin', false);

            $recipients->select(['id'])->chunkById(300, function ($users) use ($campaign, &$sent, &$failed) {
                foreach ($users as $user) {
                    try {
                        Notification::create([
                            'user_id' => $user->id,
                            'admin_id' => $campaign->created_by,
                            'type' => 'system',
                            'title' => $campaign->title,
                            'message' => $campaign->body,
                            'data' => ['campaign_id' => $campaign->id, 'url' => url('/support/chat')],
                            'is_read' => false,
                            'is_broadcast' => false,
                        ]);
                        $sent++;
                    } catch (\Throwable $e) {
                        $failed++;
                        Log::warning('Campaign in-app delivery failed', ['campaign_id' => $campaign->id, 'user_id' => $user->id, 'error' => $e->getMessage()]);
                    }
                }
            });
        } else {
            NewsletterSubscriber::where('is_active', true)->orderBy('id')->chunkById(100, function ($subscribers) use ($data, &$sent, &$failed) {
                foreach ($subscribers as $subscriber) {
                    try {
                        $html = $this->buildEmail($data['title'], $data['body'], $subscriber->email);
                        Mail::send([], [], function ($mail) use ($subscriber, $data, $html) {
                            $mail->to($subscriber->email, $subscriber->name ?? '')
                                ->subject($data['title'])
                                ->html($html);
                        });
                        $sent++;
                    } catch (\Throwable $e) {
                        $failed++;
                        Log::warning('Campaign email delivery failed', ['email' => $subscriber->email, 'error' => $e->getMessage()]);
                    }
                }
            });
        }

        $campaign->update([
            'sent_count' => $sent,
            'failed_count' => $failed,
            'status' => $failed ? 'completed_with_errors' : 'completed',
            'sent_at' => now(),
        ]);

        return redirect()->route('admin.campaigns.index')->with('success', "Campaign finished: {$sent} sent, {$failed} failed.");
    }

    private function transactingUsers()
    {
        return User::where('is_admin', false)->where(function ($query) {
            $query->whereIn('id', BuyTrade::select('user_id')->where('status', 'completed'))
                ->orWhereIn('id', SellTrade::select('user_id')->where('status', 'completed'))
                ->orWhereIn('id', Deposit::select('user_id')->where('status', 'approved'))
                ->orWhereIn('id', CryptoDeposit::select('user_id')->where('status', 'completed'));
        });
    }

    private function buildEmail(string $title, string $body, string $email): string
    {
        $appName = e(config('app.name'));
        $appUrl = e(config('app.url'));
        $safeTitle = e($title);
        $safeBody = nl2br(e($body));
        $unsubscribe = e(rtrim(config('app.url'), '/') . '/newsletter/unsubscribe?email=' . urlencode($email));

        return <<<HTML
<!doctype html><html><body style="margin:0;background:#0d1117;color:#e4e8f0;font-family:Arial,sans-serif;padding:28px">
<div style="max-width:620px;margin:auto;background:#161b27;border-radius:14px;padding:30px">
<p style="color:#00cc00;font-size:20px;font-weight:bold">{$appName}</p>
<h1 style="font-size:22px;color:#fff">{$safeTitle}</h1>
<div style="font-size:15px;line-height:1.8">{$safeBody}</div>
<p style="margin-top:30px"><a href="{$appUrl}" style="background:#00cc00;color:#081108;padding:12px 22px;border-radius:7px;text-decoration:none;font-weight:bold">Visit {$appName}</a></p>
<p style="margin-top:28px;color:#8590a3;font-size:12px">You received this because you subscribed to KayXchange updates. <a style="color:#aab3c2" href="{$unsubscribe}">Unsubscribe</a></p>
</div></body></html>
HTML;
    }
}
