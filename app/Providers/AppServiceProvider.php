<?php

namespace App\Providers;
use App\Observers\UserObserver;
use App\Observers\AdminSettingObserver;
use App\Observers\AdminAuditedModelObserver;
use App\Observers\AdminDepositObserver;
use App\Models\User;
use App\Models\AdminSetting;
use App\Models\CryptoRate;
use App\Models\GiftCardRate;
use App\Models\GiftCardOrder;
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
        foreach ([CryptoRate::class, GiftCardRate::class, GiftCardOrder::class, BillProviderSetting::class, CompanyAccount::class, CryptoDeposit::class, CryptoWithdrawal::class, Deposit::class, Withdrawal::class, Conversion::class, SellTrade::class, BuyTrade::class, BillPayment::class, Wallet::class, User::class] as $model) {
            $model::observe(AdminAuditedModelObserver::class);
        }
        Deposit::observe(AdminDepositObserver::class);
        CryptoDeposit::observe(AdminDepositObserver::class);

        // Keep the selected brand mark consistent across customer and admin
        // layouts, with a safe fallback during first install/database outages.
        try {
            $faviconPath = AdminSetting::getSetting('site_favicon_path', 'Assests/favicon.png');
            $faviconPath = ltrim((string) ($faviconPath ?: 'Assests/favicon.png'), '/');
            $faviconExtension = strtolower(pathinfo($faviconPath, PATHINFO_EXTENSION));
            $faviconMime = ['jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'webp' => 'image/webp'][$faviconExtension] ?? 'image/png';
            View::share('siteFaviconUrl', asset($faviconPath));
            View::share('siteFaviconMime', $faviconMime);
            $loaderTheme = AdminSetting::getSetting('loader_theme', 'classic');
            View::share('siteLoaderTheme', in_array($loaderTheme, ['classic', 'festive', 'christmas'], true) ? $loaderTheme : 'classic');
        } catch (\Throwable $e) {
            View::share('siteFaviconUrl', asset('Assests/favicon.png'));
            View::share('siteFaviconMime', 'image/png');
            View::share('siteLoaderTheme', 'classic');
        }

        // Share homepage editable content with home/index views
        View::composer(['home', 'index'], SiteContentComposer::class);
    }
}
