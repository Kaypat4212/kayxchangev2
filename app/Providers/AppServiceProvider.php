<?php

namespace App\Providers;
use App\Observers\UserObserver;
use App\Observers\AdminSettingObserver;
use App\Observers\AdminAuditedModelObserver;
use App\Models\User;
use App\Models\AdminSetting;
use App\Models\CryptoRate;
use App\Models\GiftCardRate;
use App\Models\BillProviderSetting;
use App\Models\CompanyAccount;
use App\Models\CryptoDeposit;
use App\Models\CryptoWithdrawal;
use App\Models\Deposit;
use App\Models\Withdrawal;
use App\Models\Conversion;
use App\Models\SellTrade;
use App\Models\BuyTrade;
use App\Models\BillPayment;
use App\Models\Wallet;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use App\View\Composers\SiteContentComposer;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Paginator::useBootstrapFour();
        User::observe(UserObserver::class);
        AdminSetting::observe(AdminSettingObserver::class);
        foreach ([CryptoRate::class, GiftCardRate::class, BillProviderSetting::class, CompanyAccount::class, CryptoDeposit::class, CryptoWithdrawal::class, Deposit::class, Withdrawal::class, Conversion::class, SellTrade::class, BuyTrade::class, BillPayment::class, Wallet::class, User::class] as $model) {
            $model::observe(AdminAuditedModelObserver::class);
        }

        // Share homepage editable content with home/index views
        View::composer(['home', 'index'], SiteContentComposer::class);
    }
}
