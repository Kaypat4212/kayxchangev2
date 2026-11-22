<?php

namespace App\Console\Commands;

use App\Models\BuyTrade;
use App\Models\SellTrade;
use App\Models\User;
use App\Listeners\TrackOutboundMail;
use App\Services\EmailTransportConfigurator;
use Illuminate\Console\Command;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Carbon;

class SendDailyTradeSummary extends Command
{
    protected $signature = 'digest:daily {--to= : Optional admin retry recipient} {--retry-of= : Failed delivery log id being retried}';
    protected $description = 'Send daily trade summary email digest to admin';

    public function handle(EmailTransportConfigurator $transport): int
    {
        $yesterday = Carbon::yesterday();
        $todayStart = $yesterday->copy()->startOfDay();
        $todayEnd   = $yesterday->copy()->endOfDay();

        try {
            $sellTrades = SellTrade::whereBetween('created_at', [$todayStart, $todayEnd])->get();
            $buyTrades  = BuyTrade::whereBetween('created_at', [$todayStart, $todayEnd])->get();
            $newUsers   = User::whereBetween('created_at', [$todayStart, $todayEnd])->count();
        } catch (QueryException $exception) {
            Log::error('Daily trade digest skipped because the database is unavailable.', [
                'exception_class' => $exception::class,
                'sql_state' => $exception->errorInfo[0] ?? null,
            ]);
            $this->error('Could not connect to the application database. Check the production DB_HOST, DB_PORT, and DB_SOCKET settings. No digest was sent.');
            return self::FAILURE;
        }

        $sellCompleted  = $sellTrades->where('status', 'completed');
        $buyApproved    = $buyTrades->whereIn('status', ['completed', 'approved']);

        $data = [
            'date'              => $yesterday->format('D, d M Y'),
            'total_sell'        => $sellTrades->count(),
            'completed_sell'    => $sellCompleted->count(),
            'pending_sell'      => $sellTrades->where('status', 'pending')->count(),
            'rejected_sell'     => $sellTrades->whereIn('status', ['rejected', 'cancelled'])->count(),
            'total_buy'         => $buyTrades->count(),
            'approved_buy'      => $buyApproved->count(),
            'pending_buy'       => $buyTrades->where('status', 'pending')->count(),
            'sell_volume_ngn'   => $sellCompleted->sum('naira_amount'),
            'buy_volume_ngn'    => $buyApproved->sum('naira_amount'),
            'sell_volume_usd'   => $sellCompleted->sum('usd_amount'),
            'buy_volume_usd'    => $buyApproved->sum('usd_amount'),
            'new_users'         => $newUsers,
            'total_volume_ngn'  => $sellCompleted->sum('naira_amount') + $buyApproved->sum('naira_amount'),
        ];

        $adminEmail = trim((string) ($this->option('to') ?: config('mail.admin_email', env('ADMIN_EMAIL', config('mail.from.address')))));
        if (!filter_var($adminEmail, FILTER_VALIDATE_EMAIL)) {
            $this->error('Set a valid ADMIN_EMAIL or MAIL_FROM_ADDRESS before sending the digest.');
            return self::FAILURE;
        }

        try {
            $transport->applySavedSmtpSettings();
            Mail::send(['html' => 'emails.daily-summary', 'text' => 'emails.daily-summary-text'], $data, function ($m) use ($adminEmail, $data) {
            $m->to($adminEmail)
              ->subject('Daily KayXchange trade summary - ' . $data['date']);
            $retryOf = $this->option('retry-of');
            if (ctype_digit((string) $retryOf) && method_exists($m, 'getSymfonyMessage')) {
                $m->getSymfonyMessage()->getHeaders()->addTextHeader('X-KayXchange-Retry-Of', (string) $retryOf);
            }
            });
        } catch (\Throwable $exception) {
            TrackOutboundMail::markLatestFailure($adminEmail, 'Daily KayXchange trade summary - ' . $data['date'], $exception->getMessage());
            Log::warning('Daily trade digest was rejected by the configured mail transport.', [
                'exception_class' => $exception::class,
                'transport_message' => mb_substr($exception->getMessage(), 0, 500),
            ]);
            $this->error('The SMTP provider rejected the digest. Check the mail provider response and sender domain authentication (SPF, DKIM, and DMARC).');
            return self::FAILURE;
        }

        $this->info('Daily digest accepted by the configured mail transport for ' . $adminEmail);
        return self::SUCCESS;
    }
}
