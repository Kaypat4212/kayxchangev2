<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\BuyTrade;
use App\Models\SellTrade;


use App\Http\Controllers\CryptoRateController;
use App\Http\Controllers\AdminApiController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

use App\Http\Controllers\Api\TransactionController;
use App\Http\Controllers\Api\DashboardController;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/transactions', [TransactionController::class, 'index']);
    Route::middleware('admin')->group(function () {
        Route::patch('/transactions/{id}/status', [TransactionController::class, 'updateStatus']);
        Route::delete('/transactions/{id}', [TransactionController::class, 'destroy']);
    });
});

// Dashboard API Routes
Route::middleware('auth:sanctum')->prefix('dashboard')->group(function () {
    Route::get('/stats', [DashboardController::class, 'getStats']);
    Route::get('/recent-transactions', [DashboardController::class, 'getRecentTransactions']);
    Route::get('/trading-analytics', [DashboardController::class, 'getTradingAnalytics']);
    Route::get('/notifications', [DashboardController::class, 'getNotifications']);
});

// Notification API Routes
Route::middleware('auth:sanctum')->prefix('notifications')->group(function () {
    Route::get('/', [App\Http\Controllers\NotificationController::class, 'apiIndex']);
    Route::get('/unread-count', [App\Http\Controllers\NotificationController::class, 'getUnreadCount']);
    Route::post('/{id}/mark-read', [App\Http\Controllers\NotificationController::class, 'markAsRead']);
    Route::post('/mark-all-read', [App\Http\Controllers\NotificationController::class, 'markAllAsRead']);
    Route::delete('/{id}', [App\Http\Controllers\NotificationController::class, 'delete']);
});

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware(['auth:api', 'admin'])->group(function () {
    Route::get('/admin/pending-counts', [AdminApiController::class, 'getPendingCounts']);
});

Route::middleware('auth:sanctum')->get('/user/transactions', function (Request $request) {
    $user = $request->user();
    $perPage = $request->get('limit', 5);
    $buyTrades = BuyTrade::where('user_id', $user->id)
        ->select(['created_at', 'coin', 'usd_amount as amount_usd', 'naira_amount as amount_ngn', 'status'])
        ->take($perPage)
        ->get()
        ->map(fn($trade) => [
            'created_at' => $trade->created_at,
            'type' => 'buy',
            'coin' => $trade->coin,
            'amount_usd' => $trade->amount_usd ?? 0.00,
            'amount_ngn' => $trade->amount_ngn ?? 0.00,
            'status' => $trade->status ?? 'N/A',
        ]);

    $sellTrades = SellTrade::where('user_id', $user->id)
        ->select(['created_at', 'coin', 'usd_amount as amount_usd', 'naira_amount as amount_ngn', 'status'])
        ->take($perPage)
        ->get()
        ->map(fn($trade) => [
            'created_at' => $trade->created_at,
            'type' => 'sell',
            'coin' => $trade->coin,
            'amount_usd' => $trade->amount_usd ?? 0.00,
            'amount_ngn' => $trade->amount_ngn ?? 0.00,
            'status' => $trade->status ?? 'N/A',
        ]);

    return response()->json(
        $buyTrades->merge($sellTrades)->sortByDesc('created_at')->take($perPage)->values()
    );
});

Route::get('/rate', [CryptoRateController::class, 'index']);

// Telegram Webhook Routes
use App\Http\Controllers\TelegramWebhookController;

Route::post('/telegram/webhook', [TelegramWebhookController::class, 'handle']);
Route::get('/telegram/setup-webhook', [TelegramWebhookController::class, 'setup']);
Route::get('/telegram/bot-info', [TelegramWebhookController::class, 'botInfo']);

// Webhook health check (for monitoring)
Route::get('/telegram/health', function() {
    $telegramService = new \App\Services\TelegramService();
    $botInfo = $telegramService->getBotInfo();
    
    return response()->json([
        'status' => 'healthy',
        'environment' => app()->environment(),
        'is_production' => $telegramService->isProductionMode(),
        'bot_connected' => $botInfo && $botInfo['ok'],
        'webhook_ready' => $telegramService->isProductionMode(),
        'timestamp' => now()->toDateTimeString(),
        'app_url' => env('APP_URL'),
        'ssl_enabled' => request()->isSecure()
    ]);
});

// User Telegram status endpoint
Route::middleware('auth:sanctum')->get('/user/telegram-status', function (Request $request) {
    $user = $request->user();
    return response()->json([
        'has_username' => !empty($user->telegram_username),
        'has_chat_id' => !empty($user->telegram_chat_id),
        'notifications_enabled' => (bool) $user->telegram_notifications,
        'verified' => (bool) $user->telegram_verified,
        'status' => $user->telegram_verified && $user->telegram_notifications ? 'ready' : 'setup_needed'
    ]);
});

// Test route for email verification (local development only)
Route::get('/telegram/test-email/{email}', function($email) {
    if (!app()->environment('local')) {
        return response()->json(['error' => 'Only available in local development'], 403);
    }
    
    $telegramService = new \App\Services\TelegramService();
    
    // Simulate a fake chat ID and username for testing
    $fakeChatId = 123456789;
    $fakeUsername = 'testuser';
    
    // Create a fake update to test email verification
    $fakeUpdate = [
        'update_id' => 1,
        'message' => [
            'message_id' => 1,
            'from' => [
                'id' => $fakeChatId,
                'first_name' => 'Test',
                'username' => $fakeUsername
            ],
            'chat' => [
                'id' => $fakeChatId,
                'type' => 'private'
            ],
            'date' => time(),
            'text' => $email
        ]
    ];
    
    $result = $telegramService->processUpdate($fakeUpdate);
    
    return response()->json([
        'success' => $result,
        'message' => $result ? 'Email verification processed' : 'Failed to process email',
        'email' => $email
    ]);
});
