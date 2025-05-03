<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RatesController;
use App\Http\Controllers\CryptoController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\BuyController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\SellController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
| Here is where you can register web routes for your application.
| These routes are loaded by the RouteServiceProvider and assigned to the "web" middleware group.
|--------------------------------------------------------------------------
*/
// Step-by-step Sell Crypto Form (new flow)
Route::middleware(['auth'])->prefix('sell')->group(function () {
    Route::get('/start', [SellController::class, 'step1'])->name('sell.step1');          // Step 1: Select coin and amount
    Route::post('/step1', [SellController::class, 'postStep1'])->name('sell.postStep1'); // Post step 1

    Route::get('/step2', [SellController::class, 'step2'])->name('sell.step2');          // Step 2: Show wallet, upload proof
    Route::post('/step2', [SellController::class, 'postStep2'])->name('sell.postStep2'); // Post step 2

    Route::get('/step3', [SellController::class, 'step3'])->name('sell.step3');          // Step 3: Select payout method
    Route::post('/finalize', [SellController::class, 'finalize'])->name('sell.finalize'); // Final step: Create trade

    Route::get('/summary/{trade_id}', [SellController::class, 'tradeSummary'])->name('trade.summary'); // Trade summary
});

Route::post('/sell', [SellController::class, 'store']);
Route::get('/waiting', [SellController::class, 'waitingPage'])->name('waiting.page');
Route::get('/sell/success/{trade_id}', [SellController::class, 'sellSuccess'])->name('sell.success');
Route::get('/transactions', [SellController::class, 'transactionHistory'])->name('transaction.history');
Route::post('/settings', [SellController::class, 'updateSettings'])->name('update.settings');
Route::post('/sell/submit', [SellController::class, 'submit'])->name('sell.submit');


Route::get('/settings', [UserController::class, 'settings'])->name('settings');

// Settings overview

Route::get('/settings/edit-bank', [UserController::class, 'editBank'])->name('edit.bank');
Route::post('/settings/update-bank', [UserController::class, 'updateBank'])->name('update.bank');

Route::get('/settings/change-password', [UserController::class, 'changePasswordForm'])->name('change.password.form');
Route::post('/settings/change-password', [UserController::class, 'changePassword'])->name('change.password');



/**
 * Sell Trade Routes
 */
Route::post('/sell/submit', [SellController::class, 'submit'])->name('sell.submit'); // Submit sell form
Route::get('/sell/payment/{id}', [SellController::class, 'paymentPage'])->name('sell.payment'); // Sell payment page
Route::post('/sell/upload/{id}', [SellController::class, 'uploadPayment'])->name('sell.upload'); // Upload sell payment proof
Route::patch('/sell/update-status/{id}', [SellController::class, 'updateSellStatus'])->name('sell.updateStatus'); // Update sell trade status
Route::get('/sell/success', function () {
    return view('sell.success');
})->name('sell.success'); // Sell success page

// SELL - user submits sell
Route::post('/sell', [SellController::class, 'sellCrypto'])->name('sell.crypto');

// ADMIN - view sell trades
Route::middleware(['auth', 'is_admin'])->get('/admin/sells', [SellController::class, 'viewSellTrades'])->name('admin.sells');

// ADMIN - update sell status
Route::middleware(['auth', 'is_admin'])->patch('/admin/sells/{id}/update-status', [SellController::class, 'updateSellStatus'])->name('admin.sells.updateStatus');

/**
 * Admin Authentication Routes
 */
Route::get('/admin', [AdminController::class, 'showLoginForm'])->name('admin.login'); // Admin login form
Route::post('/admin', [AdminController::class, 'login']); // Admin login post action

/**
 * Admin Protected Routes (auth + is_admin middleware)
 */
Route::middleware(['auth', 'is_admin'])->prefix('admin')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard'); // Admin dashboard
    Route::get('/users', [AdminController::class, 'users'])->name('admin.users'); // View all users
});

// Admin trades route (outside prefix, but still admin protected)
Route::middleware(['auth', 'is_admin'])->get('/trades', [AdminController::class, 'showTrades'])->name('trades');

Route::get('/dashboard/settings', [UserController::class, 'showSettings'])->name('settings');
Route::post('/dashboard/settings', [UserController::class, 'updateSettings']);

/**
 * Buy Trade Routes
 */

Route::middleware(['auth'])->group(function () {
    Route::post('/buy/payment/upload/{id}', [BuyController::class, 'uploadPayment'])->name('buy.uploadPayment');
    Route::get('/buy/payment/{id}', [BuyController::class, 'paymentPage'])->name('buy.payment'); // Buy payment page
    
    Route::get('/buy/success', [BuyController::class, 'success'])->name('buy.success'); // Buy success page
    Route::patch('/buy/update-status/{id}', [BuyController::class, 'updateStatus'])->name('buy.updateStatus'); // Update buy trade status
    Route::post('buy/upload/{id}', [BuyController::class, 'uploadPayment'])->name('buy.upload'); // Upload buy payment proof
});

Route::get('/test', function () {
    return 'Test Route';
});

/**
 * Crypto Sell Process Route
 */
Route::post('/sell', [SellController::class, 'sellCrypto'])->name('sell.crypto'); // Sell crypto (general post action)

/**
 * Dashboard and Buy/Sell Pages
 */
Route::get('/dashboard', [CryptoController::class, 'dashboard'])->name('dashboard'); // User dashboard

Route::middleware(['auth'])->group(function () {
    Route::get('/buy', function () {
        return view('buy');
    })->name('buy'); // Static buy page view (might conflict, see note below)

    Route::post('/buy/submit', [BuyController::class, 'submit'])->name('buy.submit'); // Submit buy form

    Route::get('/buy', [CryptoController::class, 'buy'])->name('buy'); // Buy page controlled via CryptoController (conflict with static buy above)
    Route::get('/sell', [CryptoController::class, 'sell'])->name('sell'); // Sell page controlled via CryptoController
});
/**
 * Rates Pages
 */
Route::get('/rates', [RatesController::class, 'index'])->name('rates'); // General rates page
Route::get('/crypto-rates', [RatesController::class, 'cryptoRates'])->name('crypto.rates'); // Crypto rates page
Route::get('/gift-card-rates', [RatesController::class, 'giftCardRates'])->name('giftcard.rates'); // Gift card rates page

/**
 * General Pages
 */
Route::get('/', function () {
    return view('index');
}); // Home page

Route::get('/home', function () {
    return view('home');
}); // Home view

Route::get('/profile', function () {
    return view('profile');
})->middleware(['auth'])->name('profile'); // Profile page (simple view)

Route::get('/settings', function () {
    return view('settings.index');
})->middleware(['auth'])->name('settings'); // Settings page (simple view)

/**
 * Authenticated Dashboard
 */
Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard'); // Dashboard (authenticated & verified)

/**
 * Profile Management Routes
 */
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit'); // Edit profile
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update'); // Update profile
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy'); // Delete profile
});

/**
 * Auth Routes (Login/Register/Forgot Password, etc.)
 */
require __DIR__.'/auth.php';
