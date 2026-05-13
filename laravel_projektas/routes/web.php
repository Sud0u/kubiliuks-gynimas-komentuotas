<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\OrderPageController as AdminOrderPageController;
use App\Http\Controllers\Admin\ProductAdminController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Store\OrderPageController;
use App\Http\Controllers\Store\PageController;
use App\Http\Controllers\Store\PayseraController;
use App\Http\Controllers\Store\ProductPageController;
use Illuminate\Support\Facades\Route;

// KODO PRADŽIA: web.php maršrutai
// Šitas failas skirtas puslapiams, kuriuos žmogus atsidaro naršyklėje.
// Čia grąžinami Blade puslapiai: pradžia, prekės, checkout, admin ir kt.
Route::get('/', [PageController::class, 'home'])->name('home');

Route::get('/dashboard', [PageController::class, 'dashboard'])
    ->middleware('auth')
    ->name('dashboard');

Route::get('/prekes', [PageController::class, 'products'])->name('prekes');

Route::get('/prekes/{product:slug}', [ProductPageController::class, 'show'])
    ->name('store.products.show');

Route::get('/kontaktai', [PageController::class, 'contact'])->name('kontaktai');

Route::get('/susikurk-savo-kubila', [PageController::class, 'buildTub'])
    ->name('build.tub');

Route::get('/cart', [PageController::class, 'cart'])->name('cart');

Route::get('/checkout', [PageController::class, 'checkout'])
    ->middleware('auth')
    ->name('checkout');

Route::get('/privatumo-politika', [PageController::class, 'privacy'])
    ->name('privacy');

Route::get('/slapuku-politika', [PageController::class, 'cookies'])
    ->name('cookies');

Route::get('/taisykles', [PageController::class, 'terms'])
    ->name('terms');

// Paysera grąžinimo maršrutai.
// accept/cancel mato klientas, o callback naudoja pati Paysera sistema.
Route::get('/paysera/accept/{order}', [PayseraController::class, 'accept'])
    ->middleware('auth')
    ->name('paysera.accept');

Route::get('/paysera/cancel/{order}', [PayseraController::class, 'cancel'])
    ->middleware('auth')
    ->name('paysera.cancel');

Route::match(['GET', 'POST'], '/paysera/callback/{order}', [PayseraController::class, 'callback'])
    ->name('paysera.callback');

Route::middleware('auth')->group(function () {
    Route::get('/orders', [OrderPageController::class, 'index'])->name('orders.index');
    Route::get('/orders/{id}', [OrderPageController::class, 'show'])->name('orders.show');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Admin dalis apsaugota: reikia būti prisijungus ir turėti administratoriaus teises.
Route::prefix('admin')
    ->name('admin.')
    ->middleware(['auth', 'can:isAdmin'])
    ->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        Route::resource('products', ProductAdminController::class)
            ->except(['show']);

        Route::get('/orders', [AdminOrderPageController::class, 'index'])->name('orders.index');
        Route::get('/orders/{id}', [AdminOrderPageController::class, 'show'])->name('orders.show');
    });

// KODO PABAIGA: web.php maršrutai

require __DIR__ . '/auth.php';