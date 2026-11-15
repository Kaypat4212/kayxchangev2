<?php

namespace App\Observers;

use App\Models\CryptoDeposit;
use App\Models\Deposit;
use App\Services\AdminDepositAlertService;
use Illuminate\Support\Facades\DB;

class AdminDepositObserver
{
    public function created(Deposit|CryptoDeposit $deposit): void
    {
        $notify = static function () use ($deposit): void {
            app()->terminating(static function () use ($deposit): void {
                app(AdminDepositAlertService::class)->created($deposit);
            });
        };

        if (DB::transactionLevel() > 0) DB::afterCommit($notify);
        else $notify();
    }
}
