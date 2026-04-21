<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;
use App\Http\Controllers\{
    ProfileController,
    RatesController,
    CryptoController,
    AdminController,
    BuyController,
    OnboardingController,
    PinController,
    UserController,
    SellController,
    KycController,
    CryptoRateController,
    WithdrawalController,
    AdminTradeController,
    FeatureRequestController,
    UserTransactionController,
    RateDisplayPageController,
    EditBankController,
    ErrorLogController,
    PaystackController,
    ChatController,
    ReferralController,
    AdminDepositController,
    DepositController,
    TelegramSettingsController,
    InstallController,
    TradeController,
};
use App\Http\Controllers\Admin\AdminCryptoRateController;
use App\Http\Controllers\RateCalculatorController;
use App\Http\Controllers\Admin\AdminGiftCardRateController;
use App\Http\Controllers\Admin\AnalyticsController;
use App\Http\Controllers\Admin\BlogAiController;
use App\Http\Controllers\Admin\AdminAiController;
use App\Http\Controllers\Admin\AdminAiBotController;
use App\Http\Controllers\Admin\ReferralSettingsController;
use App\Http\Controllers\Admin\NotificationAiController;
use App\Http\Controllers\BugReportController;
use App\Http\Controllers\Admin\AdminTerminalController;
use App\Http\Controllers\UserAiController;
use App\Http\Controllers\Api\TransactionController;
use App\Http\Controllers\Api\CryptoRateupdateController;
use App\Http\Controllers\TelegramLoginController;

use Illuminate\Support\Facades\Http;



// Installation Routes (must be first)
Route::prefix('install')->name('install.')->group(function () {
    Route::get('/', [InstallController::class, 'index'])->name('index');
    Route::get('/requirements', [InstallController::class, 'requirements'])->name('requirements');
    Route::get('/database', [InstallController::class, 'database'])->name('database');
    Route::post('/database', [InstallController::class, 'databaseStore'])->name('database.store');
    Route::post('/database/test', [InstallController::class, 'testDatabaseConnection'])->name('database.test');
    Route::get('/application', [InstallController::class, 'application'])->name('application');
    Route::post('/application', [InstallController::class, 'applicationStore'])->name('application.store');
    Route::get('/final', [InstallController::class, 'final'])->name('final');
    Route::post('/install', [InstallController::class, 'install'])->name('install');
    Route::get('/complete', [InstallController::class, 'complete'])->name('complete');
});

Route::get('/', fn() => view('index'))->name('home');
Route::get('/home', function () {
    $blogPosts = \App\Models\BlogPost::published()->limit(8)->get();
    return view('home', compact('blogPosts'));
});
Route::get('/faqs', fn() => view('faqs'))->name('faqs');
Route::get('/about', fn() => view('about'))->name('about');

// Secure file serving — serves files from storage/app/public without requiring a symlink
Route::get('/file/{path}', function (string $path) {
    $disk = \Illuminate\Support\Facades\Storage::disk('public');
    // Prevent directory traversal
    $path = ltrim($path, '/');
    if (!$disk->exists($path)) {
        abort(404);
    }
    return $disk->response($path);
})->where('path', '.*')->name('storage.file');

// Public Blog Routes
Route::get('/blog', [\App\Http\Controllers\BlogController::class, 'index'])->name('blog.index');
Route::get('/blog/{slug}', [\App\Http\Controllers\BlogController::class, 'show'])->name('blog.show');
Route::get('/offline', fn() => view('offline'))->name('offline');
Route::get('/privacy', fn() => view('privacy'))->name('privacy');
Route::get('/terms', fn() => view('terms'))->name('terms');

// ── Telegram Login Widget callback (no auth required) ───────────────────────
Route::match(['get', 'post'], '/auth/telegram/callback', [TelegramLoginController::class, 'callback'])->name('telegram.login.callback');

// ── WhatsApp Bot Webhook (no auth required — called by Meta servers) ─────────
Route::get('/whatsapp/webhook',  [\App\Http\Controllers\WhatsAppController::class, 'verify'])->name('whatsapp.verify');
Route::post('/whatsapp/webhook', [\App\Http\Controllers\WhatsAppController::class, 'webhook'])->name('whatsapp.webhook');

// ── Onboarding ──────────────────────────────────────────────────────────────
Route::middleware('auth')->group(function () {
    Route::get('/onboard', [OnboardingController::class, 'show'])->name('onboard');
    Route::post('/onboard/pin', [OnboardingController::class, 'savePin'])->name('onboard.pin');
    Route::post('/onboard/bank', [OnboardingController::class, 'saveBank'])->name('onboard.bank');
    Route::post('/onboard/complete', [OnboardingController::class, 'complete'])->name('onboard.complete');
});

// ── PIN security ─────────────────────────────────────────────────────────────
Route::middleware('auth')->group(function () {
    Route::get('/verify-pin', [PinController::class, 'showVerify'])->name('pin.verify');
    Route::post('/verify-pin', [PinController::class, 'verify']);
    Route::post('/verify-pin/ajax', [PinController::class, 'ajaxVerify'])->name('pin.ajax');
    Route::get('/setup-pin', [PinController::class, 'showSetup'])->name('pin.setup');
    Route::post('/setup-pin', [PinController::class, 'setup']);
    Route::get('/settings/change-pin', [PinController::class, 'showChange'])->name('pin.change');
    Route::post('/settings/change-pin', [PinController::class, 'change']);
});

Route::get('/crypto-prices', [CryptoRateController::class, 'prices'])->name('crypto.prices');
// General Rates Pages
Route::get('/crypto-rates', [CryptoRateController::class, 'index'])->name('rates.crypto');
// Rate Calculator
Route::get('/rate-calculator', [RateCalculatorController::class, 'index'])->name('calc.index');
Route::get('/rate-calculator/rates', [RateCalculatorController::class, 'apiRates'])->name('calc.rates');
Route::get('/gift-card-rates', [RatesController::class, 'giftCardRates'])->name('giftcard.rates');

Route::patch('admin/withdrawals/{id}/status', [WithdrawalController::class, 'updateStatus'])->name('withdrawal.updateStatus')->middleware(['auth', 'admin']);

Route::middleware('auth')->group(function () {
    Route::get('deposits', [DepositController::class, 'index'])->name('deposits.index');
    Route::get('deposits/create', [DepositController::class, 'create'])->name('deposits.create');
    Route::post('deposits', [DepositController::class, 'store'])->name('deposits.store');
    Route::post('deposits/initiate', [DepositController::class, 'initiate'])->name('deposits.initiate');
    Route::post('deposits/charge-authorization', [DepositController::class, 'chargeAuthorization'])->name('deposits.charge-authorization');
    Route::get('deposits/callback', [DepositController::class, 'callback'])->name('deposits.callback');
});

// Payment gateway webhooks — no auth or CSRF required
Route::post('deposits/webhook/{gateway}', [DepositController::class, 'webhook'])
    ->name('deposits.webhook')
    ->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);

// Payout/Transfer webhooks — no auth or CSRF required
Route::post('withdrawals/webhook/{gateway}', [WithdrawalController::class, 'payoutWebhook'])
    ->name('withdrawals.payout.webhook')
    ->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);

use App\Http\Controllers\Admin\SiteContentController;
use App\Http\Controllers\Admin\EmailSettingsController;
use App\Http\Controllers\Admin\EnvEditorController;
use App\Http\Controllers\Admin\AdminTelegramController;
use App\Http\Controllers\Admin\AdminBackupController;
use App\Http\Controllers\Admin\AdminProfileController;

Route::prefix('admin')->middleware(['auth', 'admin'])->group(function () {
    // ── Telegram Bot Management ─────────────────────────────────────────────
    Route::get('telegram',               [AdminTelegramController::class, 'index'])->name('admin.telegram.index');
    Route::post('telegram/broadcast',    [AdminTelegramController::class, 'broadcast'])->name('admin.telegram.broadcast');
    Route::post('telegram/send-direct',  [AdminTelegramController::class, 'sendDirect'])->name('admin.telegram.send-direct');
    Route::post('telegram/set-webhook',  [AdminTelegramController::class, 'setWebhook'])->name('admin.telegram.set-webhook');
    Route::post('telegram/delete-webhook', [AdminTelegramController::class, 'deleteWebhook'])->name('admin.telegram.delete-webhook');
    Route::post('telegram/unlink-user',  [AdminTelegramController::class, 'unlinkUser'])->name('admin.telegram.unlink-user');
    Route::get('telegram/messages',      [AdminTelegramController::class, 'messages'])->name('admin.telegram.messages');
    Route::post('telegram/reply',        [AdminTelegramController::class, 'replyToChatId'])->name('admin.telegram.reply');
    Route::get('telegram/file/{fileId}', [AdminTelegramController::class, 'serveFile'])->name('admin.telegram.file')
         ->where('fileId', '[A-Za-z0-9_\-]+');
    Route::get('proof/{path}', [AdminTelegramController::class, 'serveProof'])->name('admin.proof')
         ->where('path', '.*');
    Route::post('telegram/ai-suggest',   [AdminTelegramController::class, 'aiSuggestReply'])->name('admin.telegram.ai-suggest');
    // AI Bot Config
    Route::get('telegram/ai-config',       [AdminAiBotController::class, 'index'])->name('admin.telegram.ai-config');
    Route::put('telegram/ai-config',       [AdminAiBotController::class, 'update'])->name('admin.telegram.ai-config.update');
    Route::post('telegram/ai-config/test', [AdminAiBotController::class, 'testChat'])->name('admin.telegram.ai-config.test');

    Route::get('deposits', [AdminDepositController::class, 'index'])->name('admin.deposits.index');
    Route::put('deposits/{deposit}', [AdminDepositController::class, 'update'])->name('admin.deposits.update');
    Route::post('deposits/{deposit}/update', [AdminDepositController::class, 'update'])->name('admin.deposits.update.post');
    Route::get('deposits/{deposit}', function () {
        return redirect()->route('admin.deposits.index')
            ->with('error', 'Use the action button to update deposit status.');
    })->name('admin.deposits.show');
    // Homepage Content Editor
    Route::get('site-content', [SiteContentController::class, 'index'])->name('admin.site-content.index');
    Route::post('site-content', [SiteContentController::class, 'update'])->name('admin.site-content.update');

    // Email Settings & Login Logs
    Route::get('email-settings', [EmailSettingsController::class, 'index'])->name('admin.email-settings.index');
    Route::put('email-settings', [EmailSettingsController::class, 'update'])->name('admin.email-settings.update');
    Route::post('email-settings/test', [EmailSettingsController::class, 'sendTest'])->name('admin.email-settings.test');

    // Email Templates
    Route::get('email-templates', [EmailSettingsController::class, 'templates'])->name('admin.email-templates');
    Route::get('email-templates/{key}/edit', [EmailSettingsController::class, 'editTemplate'])->name('admin.email-templates.edit');
    Route::put('email-templates/{key}', [EmailSettingsController::class, 'updateTemplate'])->name('admin.email-templates.update');

    // API Keys / Env Editor
    Route::get('env-editor', [EnvEditorController::class, 'index'])->name('admin.env.index');
    Route::put('env-editor', [EnvEditorController::class, 'update'])->name('admin.env.update');
    Route::post('env-editor/toggle-payment-method', [EnvEditorController::class, 'togglePaymentMethod'])->name('admin.env.toggle-pm');

    // API Diagnostics
    Route::get('diagnostics', [EnvEditorController::class, 'diagnostics'])->name('admin.diagnostics');
    Route::post('diagnostics/run', [EnvEditorController::class, 'runDiagnostics'])->name('admin.diagnostics.run');

    // Backup
    Route::get('backup',                   [AdminBackupController::class, 'index'])->name('admin.backup.index');
    Route::post('backup/run',              [AdminBackupController::class, 'run'])->name('admin.backup.run');
    Route::get('backup/download/{filename}', [AdminBackupController::class, 'download'])->name('admin.backup.download')->where('filename', '[\w.\-]+');
    Route::delete('backup/{filename}',     [AdminBackupController::class, 'delete'])->name('admin.backup.delete')->where('filename', '[\w.\-]+');

    // Newsletter Subscribers
    Route::get('newsletter',               [App\Http\Controllers\Admin\AdminNewsletterController::class, 'index'])->name('admin.newsletter.index');
    Route::get('newsletter/export',        [App\Http\Controllers\Admin\AdminNewsletterController::class, 'export'])->name('admin.newsletter.export');
    Route::post('newsletter/campaign',     [App\Http\Controllers\Admin\AdminNewsletterController::class, 'sendCampaign'])->name('admin.newsletter.campaign');
    Route::delete('newsletter/{subscriber}', [App\Http\Controllers\Admin\AdminNewsletterController::class, 'destroy'])->name('admin.newsletter.destroy');

    // Visitor Logs
    Route::get('visitor-logs',             [App\Http\Controllers\Admin\AdminVisitorLogController::class, 'index'])->name('admin.visitor-logs.index');
    Route::get('visitor-logs/export',      [App\Http\Controllers\Admin\AdminVisitorLogController::class, 'export'])->name('admin.visitor-logs.export');
    Route::delete('visitor-logs/clear',    [App\Http\Controllers\Admin\AdminVisitorLogController::class, 'clear'])->name('admin.visitor-logs.clear');

    // Admin Profile
    Route::get('profile',                  [AdminProfileController::class, 'index'])->name('admin.profile.index');
    Route::post('profile/email',           [AdminProfileController::class, 'updateEmail'])->name('admin.profile.email');
    Route::post('profile/password',        [AdminProfileController::class, 'updatePassword'])->name('admin.profile.password');
    Route::post('profile/2fa/setup',       [AdminProfileController::class, 'setup2fa'])->name('admin.profile.2fa.setup');
    Route::post('profile/2fa/confirm',     [AdminProfileController::class, 'confirm2fa'])->name('admin.profile.2fa.confirm');
    Route::post('profile/2fa/disable',     [AdminProfileController::class, 'disable2fa'])->name('admin.profile.2fa.disable');

    // Auto-payout settings toggle
    Route::post('withdrawals/auto-payout/toggle', [WithdrawalController::class, 'toggleAutoPayout'])
        ->name('admin.withdrawals.auto-payout.toggle');
    Route::post('withdrawals/{id}/retry-payout', [WithdrawalController::class, 'retryPayout'])
        ->name('admin.withdrawals.retry-payout');
});
// Bank Verification AJAX Routes
Route::middleware(['auth'])->prefix('ajax')->name('ajax.')->group(function () {
    Route::get('/banks', [\App\Http\Controllers\BankVerificationController::class, 'banks'])->name('banks');
    Route::get('/verify-account', [\App\Http\Controllers\BankVerificationController::class, 'verifyAccount'])->name('verify-account');
});

// Withdrawal Routes (User)
Route::middleware(['auth'])->group(function () {
    Route::get('/withdraw/form', [WithdrawalController::class, 'withdraw'])
        ->middleware('pin')
        ->name('withdraw');
    Route::post('/withdraw/process', [WithdrawalController::class, 'processWithdrawal'])
        ->middleware(['throttle:10,1', 'pin'])
        ->name('withdraw.process');
    Route::get('/withdraw/summary/{withdrawal}', [WithdrawalController::class, 'summary'])
        ->name('withdraw.summary')
        ->where('withdrawal', '[0-9]+');
    Route::get('/withdraw/success/{withdrawal}', [WithdrawalController::class, 'success'])
        ->name('withdraw.success')
        ->where('withdrawal', '[0-9]+');
});

// Price Alerts
Route::middleware(['auth'])->group(function () {
    Route::get('/price-alerts', [App\Http\Controllers\PriceAlertController::class, 'index'])->name('price-alerts.index');
    Route::post('/price-alerts', [App\Http\Controllers\PriceAlertController::class, 'store'])->name('price-alerts.store');
    Route::patch('/price-alerts/{priceAlert}', [App\Http\Controllers\PriceAlertController::class, 'toggle'])->name('price-alerts.toggle');
    Route::delete('/price-alerts/{priceAlert}', [App\Http\Controllers\PriceAlertController::class, 'destroy'])->name('price-alerts.destroy');
});

// Newsletter
Route::post('/newsletter/subscribe', [App\Http\Controllers\NewsletterController::class, 'subscribe'])
    ->middleware('throttle:newsletter')
    ->name('newsletter.subscribe');
Route::match(['get','post'], '/newsletter/unsubscribe', [App\Http\Controllers\NewsletterController::class, 'unsubscribe'])->name('newsletter.unsubscribe');

// Test routes (remove in production)
Route::get('/test-deposit', [App\Http\Controllers\DepositTestController::class, 'testDeposit'])->name('test.deposit');
Route::post('/test-validation', [App\Http\Controllers\DepositTestController::class, 'testValidation'])->name('test.validation');

// // Withdrawal Routes (Admin)
// Route::middleware(['auth', 'is_admin'])->prefix('admin')->name('admin.')->group(function () {
//     Route::get('/withdrawals', [WithdrawalController::class, 'listWithdrawals'])->name('withdrawals');
//     // Route::post('/withdrawal/{id}/approve', [WithdrawalController::class, 'approveWithdrawal'])->name('withdrawals.approve');
//     Route::post('/admin/withdraw/approve/{id}', [WithdrawalController::class, 'approveWithdrawal'])
//         ->name('withdraw.approve')
//         ->middleware('admin');

//     Route::post('/admin/withdraw/cancel/{id}', [WithdrawalController::class, 'cancelWithdrawal'])
//         ->name('withdraw.cancel')
//         ->middleware('admin');
// });


Route::get('/admin/withdrawals', [WithdrawalController::class, 'listWithdrawals'])
    ->name('admin.withdrawals')
    ->middleware('admin');

Route::post('/admin/withdraw/approve/{id}', [WithdrawalController::class, 'approveWithdrawal'])
    ->name('withdraw.approve')
    ->middleware('admin');

Route::post('/admin/withdraw/cancel/{id}', [WithdrawalController::class, 'cancelWithdrawal'])
    ->name('withdraw.cancel')
    ->middleware('admin');

Route::get('/rate', [RatesController::class, 'index'])->name('rates.index')->middleware(['web', 'auth']);;


// Admin Routes
Route::middleware(['auth', 'is_admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/enhanced-dashboard', function() {
        return view('admin.enhanced-dashboard');
    })->name('enhanced-dashboard');
    Route::get('/users', [AdminController::class, 'users'])->name('users');

    Route::get('/sells', [AdminController::class, 'showTrades'])->name('sells');
    Route::patch('/sells/{id}/update-status', [AdminController::class, 'updateSellStatus'])->name('sells.updateStatus');
    Route::get('/trades', [AdminController::class, 'showTrades'])->name('trades');
    Route::get('/set-rates', [AdminController::class, 'setRates'])->name('set-rates');
    Route::get('/edit-rate/{id}', [AdminController::class, 'editRate'])->name('edit-rate');
    Route::get('/rate', [CryptoRateupdateController::class, 'index'])->name('rates');
    Route::post('/rate', [CryptoRateupdateController::class, 'update'])->name('rates.update');
    Route::post('/trade/{trade_id}/status', [AdminTradeController::class, 'updateStatus'])->name('trade.updateStatus');
    Route::post('/buy/{id}/update-status', [AdminController::class, 'updateBuyStatus'])->name('buy.updateStatus');
    // Admin trade cancellation (immediate)
    Route::post('/buy/{id}/cancel',  [TradeController::class, 'adminCancelBuy'])->name('buy.cancel');
    Route::post('/sell/{id}/cancel', [TradeController::class, 'adminCancelSell'])->name('sell.cancel');
    Route::get('/trade/{trade_id}/status', function ($trade_id) {
        return redirect()->route('admin.trades')->with('info', 'Use the form buttons to update a trade status.');
    });

    // Optional: Uncomment if sell trades use Paystack
    // Route::post('/sell/{tradeId}/pay', [AdminController::class, 'processSellPayment'])->name('sells.pay');
});


Route::prefix('admin')->middleware(['auth', 'admin'])->group(function () {
    Route::get('/users', [AdminController::class, 'usersIndex'])->name('admin.users.index');
    Route::get('/users/{user}', [AdminController::class, 'usersShow'])->name('admin.users.show');
    Route::get('/users/{user}/edit', [AdminController::class, 'usersEdit'])->name('admin.users.edit');
    Route::put('/users/{user}', [AdminController::class, 'usersUpdate'])->name('admin.users.update');
    Route::delete('/users/{user}', [AdminController::class, 'usersDestroy'])->name('admin.users.destroy');
    Route::patch('/users/{user}/balance', [AdminController::class, 'updateBalance'])->name('admin.users.balance.update');
    Route::patch('/users/{user}/balance/adjust', [AdminController::class, 'adjustBalance'])->name('admin.users.balance.adjust');
    Route::patch('/users/{user}/bank', [AdminController::class, 'updateUserBankDetails'])->name('admin.users.bank.update');
    Route::get('/users/{user}/backdoor', [AdminController::class, 'backdoor'])->name('admin.users.backdoor');
    Route::get('/revert', [AdminController::class, 'revertBackdoor'])->name('admin.revert');
    
    // Admin Rate Routes (for dashboard API calls)
    Route::get('/rate', [CryptoRateupdateController::class, 'index'])->name('admin.rates');
    Route::post('/rate', [CryptoRateupdateController::class, 'update'])->name('admin.rates.update');
    
    // Crypto Rate Management
    Route::get('/crypto-rates', [AdminCryptoRateController::class, 'index'])->name('admin.crypto-rates.index');
    Route::post('/crypto-rates/add', [AdminCryptoRateController::class, 'add'])->name('admin.crypto-rates.add');
    Route::post('/crypto-rates/{id}/update', [AdminCryptoRateController::class, 'update'])->name('admin.crypto-rates.update');
    Route::post('/crypto-rates/bulk-update', [AdminCryptoRateController::class, 'bulkUpdate'])->name('admin.crypto-rates.bulk-update');
    Route::delete('/crypto-rates/{id}', [AdminCryptoRateController::class, 'delete'])->name('admin.crypto-rates.delete');
    Route::get('/crypto-rates/current-prices', [AdminCryptoRateController::class, 'getCurrentPrices'])->name('admin.crypto-rates.current-prices');
    
    // Gift Card Rate Management
    Route::get('/gift-card-rates', [AdminGiftCardRateController::class, 'index'])->name('admin.gift-card-rates.index');
    Route::post('/gift-card-rates/bulk-update', [AdminGiftCardRateController::class, 'bulkUpdate'])->name('admin.gift-card-rates.bulk-update');
    Route::post('/gift-card-rates/add', [AdminGiftCardRateController::class, 'store'])->name('admin.gift-card-rates.store');
    Route::post('/gift-card-rates/{id}/update', [AdminGiftCardRateController::class, 'update'])->name('admin.gift-card-rates.update');
    Route::delete('/gift-card-rates/{id}', [AdminGiftCardRateController::class, 'destroy'])->name('admin.gift-card-rates.destroy');

    // Admin Notification Management
    Route::prefix('notifications')->name('admin.notifications.')->group(function () {
        Route::get('/', [App\Http\Controllers\NotificationController::class, 'adminIndex'])->name('index');
        Route::get('/create', [App\Http\Controllers\NotificationController::class, 'adminCreate'])->name('create');
        Route::post('/', [App\Http\Controllers\NotificationController::class, 'adminStore'])->name('store');
        Route::get('/{id}/edit', [App\Http\Controllers\NotificationController::class, 'adminEdit'])->name('edit');
        Route::put('/{id}', [App\Http\Controllers\NotificationController::class, 'adminUpdate'])->name('update');
        Route::delete('/{id}', [App\Http\Controllers\NotificationController::class, 'adminDelete'])->name('delete');
        Route::post('/bulk-action', [App\Http\Controllers\NotificationController::class, 'adminBulkAction'])->name('bulk-action');
    });
    Route::post('/crypto-rates/auto-update', [AdminCryptoRateController::class, 'autoUpdate'])->name('admin.crypto-rates.auto-update');
    Route::get('/crypto-rates/export', [AdminCryptoRateController::class, 'export'])->name('admin.crypto-rates.export');
    Route::post('/crypto-rates/import', [AdminCryptoRateController::class, 'import'])->name('admin.crypto-rates.import');
    Route::get('/crypto-rates/live-rates', [AdminCryptoRateController::class, 'getLiveRates'])->name('admin.crypto-rates.live-rates');
    
    // Transaction Status Updates
    Route::patch('/deposits/{id}/status', [DepositController::class, 'updateStatus'])->name('deposits.updateStatus');
    Route::patch('/withdrawals/{id}/status', [WithdrawalController::class, 'updateStatus'])->name('withdrawals.updateStatus');
    
    // Enhanced Analytics Routes
    Route::get('/analytics/dashboard-data', [AnalyticsController::class, 'getDashboardData'])->name('analytics.dashboard-data');

    // Dashboard utility endpoints (fetch/save company account + pending counts)
    Route::get('/company-account',  [AdminController::class, 'getCompanyAccount'])->name('admin.company-account');
    Route::post('/company-account', [AdminController::class, 'updateCompanyAccount'])->name('admin.company-account.update');
    Route::get('/pending-counts',   [AdminController::class, 'getPendingCounts'])->name('admin.pending-counts');
    Route::post('/site-mode',       [AdminController::class, 'toggleSiteMode'])->name('admin.site-mode.toggle');

    // Manual trigger: scan pending sell trades for confirmed crypto receipts
    Route::post('/run-crypto-monitor', function () {
        Artisan::call('monitor:sell-trades');
        $output = trim(Artisan::output());
        return response()->json(['message' => $output ?: 'Scan complete.']);
    })->name('admin.run-crypto-monitor');

    // Manual trigger: escalate pending trades above configured threshold
    Route::post('/run-trade-escalation', function () {
        Artisan::call('trades:escalate-pending');
        $output = trim(Artisan::output());
        return response()->json(['message' => $output ?: 'Escalation scan complete.']);
    })->name('admin.run-trade-escalation');

    // Referral management
    Route::get('/referrals', [ReferralSettingsController::class, 'referrals'])->name('admin.referrals.index');
    Route::post('/referrals/{referral}/block', [ReferralSettingsController::class, 'block'])->name('admin.referrals.block');
    Route::post('/referrals/{referral}/unblock', [ReferralSettingsController::class, 'unblock'])->name('admin.referrals.unblock');
    Route::get('/referrals/settings', [ReferralSettingsController::class, 'index'])->name('admin.referrals.settings');
    Route::put('/referrals/settings/defaults', [ReferralSettingsController::class, 'updateDefaults'])->name('admin.referrals.defaults.update');
    Route::post('/referrals/settings/codes', [ReferralSettingsController::class, 'storeCode'])->name('admin.referrals.codes.store');
    Route::put('/referrals/settings/codes/{specialReferralCode}', [ReferralSettingsController::class, 'updateCode'])->name('admin.referrals.codes.update');
    Route::delete('/referrals/settings/codes/{specialReferralCode}', [ReferralSettingsController::class, 'destroyCode'])->name('admin.referrals.codes.destroy');

    // ── Admin AI Routes ──────────────────────────────────────────────────────
    Route::prefix('ai')->name('ai.')->group(function () {
        Route::post('/trade-summary',      [AdminAiController::class, 'tradeSummary'])->name('trade-summary');
        Route::post('/spot-suspicious',    [AdminAiController::class, 'spotSuspicious'])->name('spot-suspicious');
        Route::post('/report',             [AdminAiController::class, 'report'])->name('report');
        Route::post('/kyc-analyze',        [AdminAiController::class, 'kycAnalyze'])->name('kyc-analyze');
        Route::post('/notification-copy',  [NotificationAiController::class, 'generateCopy'])->name('notification-copy');
        Route::post('/email-subject',      [NotificationAiController::class, 'optimizeSubject'])->name('email-subject');
    });

    // Blog Management
    Route::prefix('blog')->name('admin.blog.')->group(function () {
        Route::get('/',                [\App\Http\Controllers\Admin\AdminBlogController::class, 'index'])  ->name('index');
        Route::get('/create',          [\App\Http\Controllers\Admin\AdminBlogController::class, 'create']) ->name('create');
        Route::post('/',               [\App\Http\Controllers\Admin\AdminBlogController::class, 'store'])  ->name('store');
        Route::get('/{post}/edit',     [\App\Http\Controllers\Admin\AdminBlogController::class, 'edit'])   ->name('edit');
        Route::put('/{post}',          [\App\Http\Controllers\Admin\AdminBlogController::class, 'update']) ->name('update');
        Route::delete('/{post}',       [\App\Http\Controllers\Admin\AdminBlogController::class, 'destroy'])->name('destroy');
        Route::patch('/{post}/toggle', [\App\Http\Controllers\Admin\AdminBlogController::class, 'togglePublish'])->name('toggle');

        Route::prefix('ai')->name('ai.')->group(function () {
            Route::post('/generate',        [BlogAiController::class, 'generate'])->name('generate');
            Route::post('/improve',          [BlogAiController::class, 'improve'])->name('improve');
            Route::post('/excerpt',          [BlogAiController::class, 'excerpt'])->name('excerpt');
            Route::post('/titles',           [BlogAiController::class, 'titles'])->name('titles');
            Route::post('/outline',          [BlogAiController::class, 'outline'])->name('outline');
            Route::post('/seo-tags',         [BlogAiController::class, 'seoTags'])->name('seo-tags');
            Route::post('/social-caption',   [BlogAiController::class, 'socialCaption'])->name('social-caption');
            Route::post('/content-planner',  [BlogAiController::class, 'contentPlanner'])->name('content-planner');
        });
    });
});

// Admin Login Routes (Outside middleware)
Route::get('/admin', [AdminController::class, 'showLoginForm'])->name('admin.login');
Route::post('/admin', [AdminController::class, 'login'])->middleware('throttle:auth-endpoints');
Route::get('/admin/forgot-password', [AdminController::class, 'showForgotPasswordForm'])->name('admin.password.request');
Route::post('/admin/forgot-password', [AdminController::class, 'resetPasswordWithSecret'])->name('admin.password.reset.secret');

// Authenticated and Verified Routes
// User AI Route
Route::middleware(['auth', 'verified'])->group(function () {
    Route::post('/ai/dashboard-insight', [UserAiController::class, 'dashboardInsight'])->name('ai.dashboard-insight');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [CryptoController::class, 'dashboard'])->name('dashboard');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Transaction History
    Route::get('/transactions/history', [UserTransactionController::class, 'showTransactions'])->name('transactions.history');
    Route::get('/transactions/detail/{type}/{id}', [UserTransactionController::class, 'show'])->name('transactions.show');

    // Settings
    Route::get('/settings', [UserController::class, 'settings'])->name('settings.index');
    Route::post('/settings', [UserController::class, 'updateSettings'])->name('update.settings');
    // Route::middleware('auth')->group(function () {
    //     Route::get('/settings/edit-bank', [UserController::class, 'editBank'])->name('edit.bank');
    //     Route::post('/settings/update-bank', [UserController::class, 'updateBank'])->name('update.bank');
    // });
    Route::get('/settings/change-password', [UserController::class, 'changePasswordForm'])->name('change.password.form');
    Route::post('/settings/change-password', [UserController::class, 'changePassword'])->name('change.password');
    
    // Telegram Settings
    Route::get('/settings/telegram', [TelegramSettingsController::class, 'show'])->name('settings.telegram');
    Route::put('/settings/telegram', [TelegramSettingsController::class, 'update'])->name('settings.telegram.update');
    Route::post('/settings/telegram/test', [TelegramSettingsController::class, 'test'])->name('settings.telegram.test');
    Route::put('/settings/telegram/ai',    [TelegramSettingsController::class, 'updateAi'])->name('settings.telegram.ai.update');
    
    // Debug route removed for security - use 'php artisan route:list' or logging instead
    // Route::get('/debug-env', function () { ... });
    
    // KYC Routes
    Route::middleware(['auth', 'admin'])->group(function () {
        Route::get('/admin/kyc', [KycController::class, 'adminIndex'])->name('admin.kyc');
        Route::post('/admin/kyc/{kyc}/verify', [KycController::class, 'verify'])->name('kyc.verify');
        Route::post('/admin/kyc/{kyc}/revoke', [KycController::class, 'revoke'])->name('kyc.revoke');
    });

    Route::middleware(['auth', 'admin'])->group(function () {
        Route::get('/admin/chat', [ChatController::class, 'adminChat'])->name('admin.chat');
        Route::post('/chat/send/admin', [ChatController::class, 'adminReply'])->name('chat.send.admin');
        Route::post('/chat/ai-assist', [ChatController::class, 'aiAssist'])->name('chat.ai.assist');
    });

    // Buy Routes
    Route::get('/buy', [CryptoController::class, 'buy'])->name('buy');
    Route::get('/api/crypto-prices', [BuyController::class, 'cryptoPricesApi'])->name('api.crypto.prices');
    Route::post('/buy/submit', [BuyController::class, 'submit'])->name('buy.submit');
    Route::get('/buy/summary/{id}', [BuyController::class, 'summary'])->name('buy.summary');
    Route::get('/buy/payment/{id}', [BuyController::class, 'paymentPage'])->name('buy.payment');
    Route::post('/buy/payment/upload/{id}', [BuyController::class, 'uploadPayment'])->name('buy.uploadPayment');
    Route::patch('/buy/update-status/{id}', [BuyController::class, 'updateStatus'])->name('buy.updateStatus');
    Route::get('/buy/success/{id}', [BuyController::class, 'success'])->name('buy.success');

    // Sell Routes
    Route::prefix('sell')->group(function () {
        Route::get('/', [SellController::class, 'step1'])->name('sell.form');
        Route::get('/step1', [SellController::class, 'step1'])->name('sell.step1');
        Route::post('/step1', [SellController::class, 'postStep1'])->name('sell.postStep1');
        Route::get('/step2', [SellController::class, 'step2'])->name('sell.step2');
        Route::post('/step2', [SellController::class, 'postStep2'])->name('sell.postStep2');
        Route::get('/step3', [SellController::class, 'step3'])->name('sell.step3');
        Route::get('/fetch-banks', [SellController::class, 'fetchBanks'])->name('sell.fetchBanks');
        Route::post('/validate-bank', [SellController::class, 'validateBank'])->name('sell.validateBank');
        Route::post('/finalize', [SellController::class, 'finalize'])->name('sell.finalize');
        Route::get('/summary/{trade_id}', [SellController::class, 'tradeSummary'])->name('trade.summary');
        Route::post('/sell', [SellController::class, 'sellCrypto'])->name('sell.crypto'); // Consolidated duplicate POST /sell
        Route::get('/payment/{id}', [SellController::class, 'paymentPage'])->name('sell.payment');
        Route::post('/upload/{id}', [SellController::class, 'uploadPayment'])->name('sell.upload');
        Route::patch('/update-status/{id}', [AdminController::class, 'updateSellStatus'])->name('sell.updateStatus');
        Route::get('/success/{trade_id}', [SellController::class, 'success'])->name('sell.success');
        Route::get('/waiting', [SellController::class, 'waitingPage'])->name('waiting.page');
    });
});


Route::middleware(['auth', 'verified'])->group(function () {
    Route::prefix('admin')->as('admin.')->group(function () {
        // Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
        // Route::get('/pending-counts', [AdminController::class, 'getPendingCounts'])->name('pending-counts');
        // Route::get('/users', [AdminController::class, 'users'])->name('users');
        // Route::get('/trades', [AdminController::class, 'showTrades'])->name('trades');
        Route::post('/buy-trades/{id}/status', [AdminController::class, 'updateStatus'])->name('buy.update-status');
        Route::post('/sell-trades/{id}/status', [AdminController::class, 'updateStatus'])->name('sell.update-status');
        Route::get('/referrals-stats', [AdminController::class, 'getReferralStats'])->name('referrals-stats');
    });

    Route::get('/referrals', [ReferralController::class, 'index'])->name('referrals');
});

// Feature Request Routes
Route::middleware(['auth'])->group(function () {
    Route::get('/feature-request', [FeatureRequestController::class, 'showForm'])->name('feature.request.form');
    Route::post('/feature-request', [FeatureRequestController::class, 'submit'])->name('feature.request.submit');
    Route::get('/feature-requests/history', [FeatureRequestController::class, 'myRequests'])->name('feature.request.history');

    Route::get('/bug-report', [BugReportController::class, 'showForm'])->name('bug.report.form');
    Route::post('/bug-report', [BugReportController::class, 'submit'])->name('bug.report.submit');
    Route::get('/bug-reports/history', [BugReportController::class, 'myReports'])->name('bug.report.history');
});

// Feature Request Routes
Route::middleware(['auth'])->group(function () {
    Route::get('/kyc', [KycController::class, 'showForm'])->name('kyc.form');
    Route::post('/kyc/submit', [KycController::class, 'submit'])->name('kyc.submit');
});

Route::middleware('auth')->group(function () {
    Route::get('/support/chat', [ChatController::class, 'supportChat'])->name('support.chat');
    Route::get('/settings/edit-bank', [EditBankController::class, 'showEditBankForm'])->name('edit-bank');
    Route::post('/settings/update-bank', [EditBankController::class, 'updateBankDetails'])->name('update.bank');
    Route::get('/paystack/banks', [EditBankController::class, 'getPaystackBanks'])->name('paystack.banks');
    Route::post('/paystack/resolve-account', [EditBankController::class, 'resolvePaystackAccount'])->name('paystack.resolve-account');

    Route::post('/chat/send', [ChatController::class, 'sendMessage'])->name('chat.send');
    Route::get('/chat/history', [ChatController::class, 'getHistory'])->name('chat.history');
    Route::get('/chat/history/{userId}', [ChatController::class, 'getHistory'])->name('chat.history.user');
    Route::get('/chat/poll', [ChatController::class, 'pollNew'])->name('chat.poll');
    
    // Notification Routes
    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/',                [App\Http\Controllers\NotificationController::class, 'index'])->name('index');
        Route::get('/api',             [App\Http\Controllers\NotificationController::class, 'apiIndex'])->name('api');
        Route::get('/unread-count',    [App\Http\Controllers\NotificationController::class, 'getUnreadCount'])->name('unread-count');
        Route::post('/mark-all-read',  [App\Http\Controllers\NotificationController::class, 'markAllAsRead'])->name('mark-all-read');
        Route::get('/{id}',            [App\Http\Controllers\NotificationController::class, 'show'])->name('show');
        Route::post('/{id}/mark-read', [App\Http\Controllers\NotificationController::class, 'markAsRead'])->name('mark-read');
        Route::delete('/{id}',         [App\Http\Controllers\NotificationController::class, 'delete'])->name('delete');
    });
});

// ── Admin Settings (API Keys, Cloudflare, AI) ───────────────────────────────
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('settings',                    [\App\Http\Controllers\Admin\AdminSettingsController::class, 'index'])->name('settings.index');
    Route::post('settings',                   [\App\Http\Controllers\Admin\AdminSettingsController::class, 'update'])->name('settings.update');
    Route::post('settings/cloudflare-action', [\App\Http\Controllers\Admin\AdminSettingsController::class, 'cloudflareAction'])->name('settings.cloudflare-action');
    Route::get('settings/ai-usage',           [\App\Http\Controllers\Admin\AdminSettingsController::class, 'aiUsage'])->name('settings.ai-usage');
    Route::get('settings/ai-test',            [\App\Http\Controllers\Admin\AdminSettingsController::class, 'aiTest'])->name('settings.ai-test');
    Route::get('settings/groq-test',          [\App\Http\Controllers\Admin\AdminSettingsController::class, 'groqTest'])->name('settings.groq-test');
});

// ── AI Chatbot (authenticated users) ─────────────────────────────────────────
Route::middleware('auth')->group(function () {
    Route::post('/ai-chat',         [\App\Http\Controllers\AiChatController::class, 'chat'])->name('ai.chat');
    Route::post('/ai-chat/clear',   [\App\Http\Controllers\AiChatController::class, 'clearSession'])->name('ai.chat.clear');

    // Trade cancellation (user: 30-min rule)
    Route::post('/trades/buy/{id}/cancel',  [TradeController::class, 'cancelBuy'])->name('trade.buy.cancel');
    Route::post('/trades/sell/{id}/cancel', [TradeController::class, 'cancelSell'])->name('trade.sell.cancel');
});

// Admin Terminal + Feedback Moderation
Route::middleware(['auth', 'admin'])->prefix('admin')->group(function () {
    Route::get('/terminal', [AdminTerminalController::class, 'index'])->name('admin.terminal');
    Route::post('/terminal/unlock', [AdminTerminalController::class, 'unlock'])->name('admin.terminal.unlock');
    Route::post('/terminal/lock', [AdminTerminalController::class, 'lock'])->name('admin.terminal.lock');
    Route::post('/terminal/artisan', [AdminTerminalController::class, 'runArtisan'])->name('admin.terminal.artisan');

    Route::get('/bug-reports', [AdminTerminalController::class, 'bugReports'])->name('admin.bug-reports');
    Route::patch('/bug-reports/{bugReport}', [AdminTerminalController::class, 'updateBugReport'])->name('admin.bug-reports.update');

    Route::get('/feature-requests', [AdminTerminalController::class, 'featureRequests'])->name('admin.feature-requests');
    Route::patch('/feature-requests/{featureRequest}', [AdminTerminalController::class, 'updateFeatureRequest'])->name('admin.feature-requests.update');
});

// Route::get('/paystack/banks', [PaystackController::class, 'getBanks'])->name('paystack.banks');
// Route::post('/paystack/resolve-account', [PaystackController::class, 'resolveAccount'])->name('paystack.resolve-account');
// Optional: Paystack Webhook for Sell Trades (Uncomment if Paystack is used)
// Route::post('/webhooks/paystack', [AdminController::class, 'handlePaystackWebhook'])->name('webhooks.paystack');
// API Routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/api/transactions', [TransactionController::class, 'index'])->name('api.transactions');
    Route::get('/user/transactions', function (Request $request) {
        return response()->json($request->user()->transactions()->latest()->take($request->get('limit', 5))->get());
    });
});

Route::post('/log-wallet-error', [BuyController::class, 'logWalletError']);

// Error Logging Route
Route::post('/log-error', [ErrorLogController::class, 'log'])->name('log.error');

// Test Telegram Route
Route::get('/test-telegram', function () {
    $message = "Test message from Laravel\nSent at: " . now()->toDateTimeString();
    $response = Http::post("https://api.telegram.org/bot" . env('TELEGRAM_TOKEN') . "/sendMessage", [
        'chat_id' => env('TELEGRAM_CHAT_ID'),
        'text' => $message,
        'parse_mode' => 'HTML',
    ]);
    return $response->successful() ? 'Sent: ' . $response->body() : 'Failed: ' . $response->body();
});

require __DIR__ . '/auth.php';
