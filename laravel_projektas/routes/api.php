<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\ProductController;
use App\Http\Controllers\Api\V1\CategoryController;
use App\Http\Controllers\Api\V1\CartController;
use App\Http\Controllers\Api\V1\OrderController;

use App\Http\Controllers\Api\V1\Admin\AdminProductController;
use App\Http\Controllers\Api\V1\Admin\AdminOrderController;


Route::prefix('v1')->group(function () {
    Route::get('/products', [ProductController::class, 'index']);
    Route::get('/products/{slug}', [ProductController::class, 'show']);

    Route::get('/categories', [CategoryController::class, 'index']);
    Route::get('/categories/{id}/products', [CategoryController::class, 'productsByCategory']);

    // web middleware reikalingas, nes krepselis remiasi session.
    Route::middleware('web')->group(function () {
        Route::middleware('throttle:login')->group(function () {
            Route::post('/auth/login', [AuthController::class, 'login']);
        });

        Route::middleware('throttle:register')->group(function () {
            Route::post('/auth/register', [AuthController::class, 'register']);
        });

        Route::middleware('auth')->group(function () {
            Route::get('/auth/me', [AuthController::class, 'me']);
            Route::post('/auth/logout', [AuthController::class, 'logout']);
        });

        // cia pagrindiniai krepselio API keliai.
        Route::middleware('throttle:cart')->group(function () {
            Route::get('/cart', [CartController::class, 'show']);
            Route::post('/cart/items', [CartController::class, 'addItem']);

            // sita uzklausa ateina is puslapio "Susikurk kubila".
            Route::post('/cart/custom-tub', [CartController::class, 'addCustomTub']);
            Route::patch('/cart/items/{id}', [CartController::class, 'updateItem']);
            Route::delete('/cart/items/{id}', [CartController::class, 'removeItem']);
            Route::delete('/cart/clear', [CartController::class, 'clear']);
        });

        Route::middleware(['auth', 'throttle:orders'])->group(function () {
            // checkout POST uzklausa ateina cia ir keliauja i OrderController store metoda.
            Route::post('/orders', [OrderController::class, 'store']);
            Route::get('/orders', [OrderController::class, 'index']);
            Route::get('/orders/{id}', [OrderController::class, 'show']);
            Route::post('/orders/{id}/cancel', [OrderController::class, 'cancel']);
        });

        Route::prefix('admin')
            ->middleware(['auth', 'can:isAdmin', 'throttle:admin-api'])
            ->group(function () {
                Route::get('/products', [AdminProductController::class, 'index']);
                Route::post('/products', [AdminProductController::class, 'store']);
                Route::patch('/products/{id}', [AdminProductController::class, 'update']);
                Route::delete('/products/{id}', [AdminProductController::class, 'destroy']);

                Route::get('/orders', [AdminOrderController::class, 'index']);
                Route::get('/orders/{id}', [AdminOrderController::class, 'show']);
                Route::patch('/orders/{id}/status', [AdminOrderController::class, 'updateStatus']);
                Route::delete('/orders/{id}', [AdminOrderController::class, 'destroy']);
            });
    });
});

