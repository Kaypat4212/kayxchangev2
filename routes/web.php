<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{
    ProfileController,
    RatesController,
    CryptoController,
    AdminController,
    BuyController,
    UserController,
    SellController
};

// Home
Route::get('/', fn() => view('index'));
Route::get('/home', fn() => view('home'));

// Authenticated User Dashboard
Route::get('/dashboard', [CryptoController::class, 'dashboard'])->middleware(['auth', 'verified'])->name('dashboard');

// General Rates Pages
Route::get('/rates', [RatesController::class, 'index'])->name('rates');
Route::get('/crypto-rates', [RatesController::class, 'cryptoRates'])->name('crypto.rates');
Route::get('/gift-card-rates', [RatesController::class, 'giftCardRates'])->name('giftcard.rates');

// Settings
Route::middleware(['auth'])->group(function () {
    Route::get('/settings', [UserController::class, 'settings'])->name('settings');
    Route::post('/settings', [UserController::class, 'updateSettings'])->name('update.settings');

    Route::get('/settings/edit-bank', [UserController::class, 'editBank'])->name('edit.bank');
    Route::post('/settings/update-bank', [UserController::class, 'updateBank'])->name('update.bank');

    Route::get('/settings/change-password', [UserController::class, 'changePasswordForm'])->name('change.password.form');
    Route::post('/settings/change-password', [UserController::class, 'changePassword'])->name('change.password');
});

// Buy Routes
Route::middleware(['auth'])->group(function () {
    Route::get('/buy', [CryptoController::class, 'buy'])->name('buy');
    Route::post('/buy/submit', [BuyController::class, 'submit'])->name('buy.submit');

    Route::get('/buy/payment/{id}', [BuyController::class, 'paymentPage'])->name('buy.payment');
    Route::post('/buy/payment/upload/{id}', [BuyController::class, 'uploadPayment'])->name('buy.uploadPayment');
    Route::patch('/buy/update-status/{id}', [BuyController::class, 'updateStatus'])->name('buy.updateStatus');

    Route::get('/buy/success', [BuyController::class, 'success'])->name('buy.success');
});

// Sell Routes (3-step form flow)
Route::middleware(['auth'])->prefix('sell')->group(function () {
    Route::get('/start', [SellController::class, 'step1'])->name('sell.step1');
    Route::post('/step1', [SellController::class, 'postStep1'])->name('sell.postStep1');

    Route::get('/step2', [SellController::class, 'step2'])->name('sell.step2');
    Route::post('/step2', [SellController::class, 'postStep2'])->name('sell.postStep2');

    Route::get('/step3', [SellController::class, 'step3'])->name('sell.step3');
    Route::post('/finalize', [SellController::class, 'finalize'])->name('sell.finalize');

    Route::get('/summary/{trade_id}', [SellController::class, 'tradeSummary'])->name('trade.summary');
});

// Sell Trade Actions
Route::get('/sell', [SellController::class, 'showSellForm'])->name('sell.form'); // For displaying form
Route::post('/sell', [SellController::class, 'submitSellForm'])->name('sell');    // For handling submission

Route::post('/sell', [SellController::class, 'sellCrypto'])->name('sell.crypto');
Route::get('/sell/payment/{id}', [SellController::class, 'paymentPage'])->name('sell.payment');
Route::post('/sell/upload/{id}', [SellController::class, 'uploadPayment'])->name('sell.upload');
Route::patch('/sell/update-status/{id}', [SellController::class, 'updateSellStatus'])->name('sell.updateStatus');
Route::get('/sell/success/{trade_id}', [SellController::class, 'sellSuccess'])->name('sell.success');

Route::get('/transactions', [SellController::class, 'transactionHistory'])->name('transaction.history');
Route::get('/waiting', [SellController::class, 'waitingPage'])->name('waiting.page');

// Admin Auth
Route::get('/admin', [AdminController::class, 'showLoginForm'])->name('admin.login');
Route::post('/admin', [AdminController::class, 'login']);

// Admin Protected Routes
Route::middleware(['auth', 'is_admin'])->prefix('admin')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');
    Route::get('/users', [AdminController::class, 'users'])->name('admin.users');
    Route::get('/sells', [SellController::class, 'viewSellTrades'])->name('admin.sells');
    Route::patch('/sells/{id}/update-status', [SellController::class, 'updateSellStatus'])->name('admin.sells.updateStatus');
});

Route::middleware(['auth', 'is_admin'])->get('/trades', [AdminController::class, 'showTrades'])->name('trades');

// Profile Management
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Misc
Route::get('/test', fn() => 'Test Route');

require __DIR__.'/auth.php';
