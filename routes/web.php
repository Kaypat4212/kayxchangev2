<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\RatesController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/
use App\Http\Controllers\CryptoController;

Route::post('/sell', [CryptoController::class, 'sellCrypto'])->name('sell.crypto');

Route::get('/dashboard', [CryptoController::class, 'dashboard'])->name('dashboard');
Route::get('/buy', [CryptoController::class, 'buy'])->name('buy');
Route::get('/sell', [CryptoController::class, 'sell'])->name('sell');


Route::get('/rates', [RatesController::class, 'index'])->name('rates');
Route::get('/crypto-rates', [RatesController::class, 'cryptoRates'])->name('crypto.rates');
Route::get('/gift-card-rates', [RatesController::class, 'giftCardRates'])->name('giftcard.rates');


Route::get('/', function () {
    return view('index');
});

Route::get('/home', function () {
    return view('home');
});

Route::get('/profile', function () {
    return view('profile');
})->middleware(['auth'])->name('profile');

Route::get('/settings', function () {
    return view('settings');
})->middleware(['auth'])->name('settings');


Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
