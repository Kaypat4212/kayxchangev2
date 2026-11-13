<?php

namespace App\Console\Commands;

use App\Models\Conversion;
use App\Services\NowPaymentsConversionService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SyncNowPaymentsConversions extends Command
{
    protected $signature = 'nowpayments:sync-conversions {--limit=100 : Maximum pending swaps to refresh per run}';

    protected $description = 'Refresh pending NOWPayments Custody swaps and settle confirmed wallet balances';

    public function handle(NowPaymentsConversionService $service): int
    {
        $conversions = Conversion::query()
            ->where('mode', 'nowpayments')
            ->whereIn('status', ['pending', 'processing'])
            ->whereNotNull('provider_conversion_id')
            ->oldest('updated_at')
            ->limit(max(1, min(500, (int) $this->option('limit'))))
            ->get();

        $completed = 0;
        $failed = 0;
        $errors = 0;
        foreach ($conversions as $conversion) {
            try {
                $refreshed = $service->refresh($conversion);
                if ($refreshed->status === 'completed') $completed++;
                if ($refreshed->status === 'failed') $failed++;
            } catch (\Throwable $e) {
                $errors++;
                Log::warning('Scheduled NOWPayments swap refresh failed', [
                    'conversion_id' => $conversion->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $this->info(sprintf(
            'NOWPayments swaps checked: %d; completed: %d; refunded: %d; refresh errors: %d.',
            $conversions->count(), $completed, $failed, $errors
        ));
        return self::SUCCESS;
    }
}
