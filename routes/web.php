<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{
    ProfileController,
    RatesController,
    CryptoController,
    AdminController,
    BuyController,
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
};
use App\Http\Controllers\Admin\AdminCryptoRateController;
use App\Http\Controllers\Admin\AnalyticsController;
use App\Http\Controllers\Api\TransactionController;
use App\Http\Controllers\Api\CryptoRateupdateController;

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
Route::get('/home', fn() => view('home'));
Route::get('/offline', fn() => view('offline'))->name('offline');

Route::get('/crypto-prices', [CryptoRateController::class, 'prices'])->name('crypto.prices');
// General Rates Pages
Route::get('/crypto-rates', [CryptoRateController::class, 'index'])->name('rates.crypto');
Route::get('/gift-card-rates', [RatesController::class, 'giftCardRates'])->name('giftcard.rates');

Route::patch('admin/withdrawals/{id}/status', [WithdrawalController::class, 'updateStatus'])->name('withdrawal.updateStatus')->middleware(['auth', 'admin']);

Route::middleware('auth')->group(function () {
    Route::get('deposits', [DepositController::class, 'index'])->name('deposits.index');
    Route::get('deposits/create', [DepositController::class, 'create'])->name('deposits.create');
    Route::post('deposits', [DepositController::class, 'store'])->name('deposits.store');
});

Route::prefix('admin')->middleware(['auth', 'admin'])->group(function () {
    Route::get('deposits', [AdminDepositController::class, 'index'])->name('admin.deposits.index');
    Route::put('deposits/{deposit}', [AdminDepositController::class, 'update'])->name('admin.deposits.update');
});
// Withdrawal Routes (User)
Route::middleware(['auth'])->group(function () {
    Route::get('/withdraw/form', [WithdrawalController::class, 'withdraw'])->name('withdraw');
    Route::post('/withdraw/process', [WithdrawalController::class, 'processWithdrawal'])
        ->middleware('throttle:10,1')
        ->name('withdraw.process');
    Route::get('/withdraw/summary/{withdrawal}', [WithdrawalController::class, 'summary'])
        ->name('withdraw.summary')
        ->where('withdrawal', '[0-9]+');
    Route::get('/withdraw/success/{withdrawal}', [WithdrawalController::class, 'success'])
        ->name('withdraw.success')
        ->where('withdrawal', '[0-9]+');
});

Route::get('/offline', function () {
    return view('offline');
});

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

    Route::get('/sells', [SellController::class, 'viewSellTrades'])->name('sells');
    Route::patch('/sells/{id}/update-status', [SellController::class, 'updateSellStatus'])->name('sells.updateStatus');
    Route::get('/trades', [AdminController::class, 'showTrades'])->name('trades');
    Route::get('/set-rates', [AdminController::class, 'setRates'])->name('set-rates');
    Route::get('/edit-rate/{id}', [AdminController::class, 'editRate'])->name('edit-rate');
    Route::get('/rate', [CryptoRateupdateController::class, 'index'])->name('rates');
    Route::post('/rate', [CryptoRateupdateController::class, 'update'])->name('rates.update');
    Route::post('/trade/{trade_id}/status', [AdminTradeController::class, 'updateStatus'])->name('trade.updateStatus');

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
});

// Admin Login Routes (Outside middleware)
Route::get('/admin', [AdminController::class, 'showLoginForm'])->name('admin.login');
Route::post('/admin', [AdminController::class, 'login']);

// Authenticated and Verified Routes
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [CryptoController::class, 'dashboard'])->name('dashboard');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Transaction History
    Route::get('/transactions/history', [UserTransactionController::class, 'showTransactions'])->name('transactions.history');

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
    
    // Debug route removed for security - use 'php artisan route:list' or logging instead
    // Route::get('/debug-env', function () { ... });
    
    // KYC Routes
    Route::middleware(['auth', 'admin'])->group(function () {
        Route::get('/admin/pending-counts', [AdminController::class, 'getPendingCounts'])->name('admin.pending-counts');
        Route::get('/admin/kyc', [KycController::class, 'adminIndex'])->name('admin.kyc');
        Route::post('/admin/kyc/{kyc}/verify', [KycController::class, 'verify'])->name('kyc.verify');
    });

    Route::middleware(['auth', 'admin'])->group(function () {
        Route::get('/admin/chat', [ChatController::class, 'adminChat'])->name('admin.chat');
        Route::post('/chat/send/admin', [ChatController::class, 'sendMessage'])->name('chat.send.admin');
    });

    // Buy Routes
    Route::get('/buy', [CryptoController::class, 'buy'])->name('buy');
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
        Route::post('/validate-bank', [SellController::class, 'validateBank'])->name('sell.validateBank');
        Route::post('/finalize', [SellController::class, 'finalize'])->name('sell.finalize');
        Route::get('/summary/{trade_id}', [SellController::class, 'tradeSummary'])->name('trade.summary');
        Route::post('/sell', [SellController::class, 'sellCrypto'])->name('sell.crypto'); // Consolidated duplicate POST /sell
        Route::get('/payment/{id}', [SellController::class, 'paymentPage'])->name('sell.payment');
        Route::post('/upload/{id}', [SellController::class, 'uploadPayment'])->name('sell.upload');
        Route::patch('/update-status/{id}', [SellController::class, 'updateSellStatus'])->name('sell.updateStatus');
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
        Route::get('/company-account', [AdminController::class, 'getCompanyAccount'])->name('company-account');
        Route::post('/company-account', [AdminController::class, 'updateCompanyAccount'])->name('company-account');
        Route::get('/referrals-stats', [AdminController::class, 'getReferralStats'])->name('referrals-stats');
    });

    Route::get('/referrals', [ReferralController::class, 'index'])->name('referrals');
});

// Feature Request Routes
Route::middleware(['auth'])->group(function () {
    Route::get('/feature-request', [FeatureRequestController::class, 'showForm'])->name('feature.request.form');
    Route::post('/feature-request', [FeatureRequestController::class, 'submit'])->name('feature.request.submit');
});

// Feature Request Routes
Route::middleware(['auth'])->group(function () {
    Route::get('/kyc', [KycController::class, 'showForm'])->name('kyc.form');
    Route::post('/kyc/submit', [KycController::class, 'submit'])->name('kyc.submit');
});

Route::middleware('auth')->group(function () {
    Route::get('/settings/edit-bank', [EditBankController::class, 'showEditBankForm'])->name('edit-bank');
    Route::post('/settings/update-bank', [EditBankController::class, 'updateBankDetails'])->name('update.bank');
    Route::get('/paystack/banks', [EditBankController::class, 'getPaystackBanks'])->name('paystack.banks');
    Route::post('/paystack/resolve-account', [EditBankController::class, 'resolvePaystackAccount'])->name('paystack.resolve-account');

    Route::post('/chat/send', [ChatController::class, 'sendMessage'])->name('chat.send');
    Route::get('/chat/history', [ChatController::class, 'getHistory'])->name('chat.history');
    Route::get('/chat/history/{userId}', [ChatController::class, 'getHistory'])->name('chat.history.user');
    
    // Notification Routes
    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/', [App\Http\Controllers\NotificationController::class, 'index'])->name('index');
        Route::get('/{id}', [App\Http\Controllers\NotificationController::class, 'show'])->name('show');
        Route::post('/{id}/mark-read', [App\Http\Controllers\NotificationController::class, 'markAsRead'])->name('mark-read');
        Route::post('/mark-all-read', [App\Http\Controllers\NotificationController::class, 'markAllAsRead'])->name('mark-all-read');
        Route::delete('/{id}', [App\Http\Controllers\NotificationController::class, 'delete'])->name('delete');
        Route::get('/unread-count', [App\Http\Controllers\NotificationController::class, 'getUnreadCount'])->name('unread-count');
    });
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
