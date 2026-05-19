<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\ProductController;
use App\Http\Controllers\Api\V1\CategoryController;
use App\Http\Controllers\Api\V1\CartController;
use App\Http\Controllers\Api\V1\OrderController;

use App\Http\Controllers\Api\V1\Admin\AdminProductController;
use App\Http\Controllers\Api\V1\Admin\AdminOrderController;

// api.php maršrutai komentaro pradzia
// Šitas failas skirtas veiksmams be pilno puslapio perkrovimo.
// Per šiuos maršrutus veikia krepšelis, checkout, admin užsakymai ir produktų API.
// API routes komentaro pradzia
// Sitas failas skirtas veiksmams kurie vyksta per JavaScript.
// Pvz krepselio atnaujinimas, prekes idejimas, custom kubilas arba uzsakymo pateikimas.
// API routes komentaro pabaiga
Route::prefix('v1')->group(function () {
    Route::get('/products', [ProductController::class, 'index']);
    Route::get('/products/{slug}', [ProductController::class, 'show']);

    Route::get('/categories', [CategoryController::class, 'index']);
    Route::get('/categories/{id}/products', [CategoryController::class, 'productsByCategory']);

    // Naudoju web middleware, nes krepšelis ir užsakymai remiasi sesija bei prisijungusiu vartotoju.
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

        // Krepšelio veiksmai ribojami throttle, kad nebūtų galima be galo siųsti užklausų.
        Route::middleware('throttle:cart')->group(function () {
            Route::get('/cart', [CartController::class, 'show']);
            Route::post('/cart/items', [CartController::class, 'addItem']);
            // custom kubilo API kelias komentaro pradzia
            // Sitas route priima JavaScript uzklausa is build_tub.blade.php.
            // Kai vartotojas spaudzia ideti individualu kubila i krepseli, uzklausa ateina cia.
            // custom kubilo API kelias komentaro pabaiga
            Route::post('/cart/custom-tub', [CartController::class, 'addCustomTub']);
            Route::patch('/cart/items/{id}', [CartController::class, 'updateItem']);
            Route::delete('/cart/items/{id}', [CartController::class, 'removeItem']);
            Route::delete('/cart/clear', [CartController::class, 'clear']);
        });

        // Užsakymą gali pateikti tik prisijungęs vartotojas.
        Route::middleware(['auth', 'throttle:orders'])->group(function () {
            Route::post('/orders', [OrderController::class, 'store']);
            Route::get('/orders', [OrderController::class, 'index']);
            Route::get('/orders/{id}', [OrderController::class, 'show']);
            Route::post('/orders/{id}/cancel', [OrderController::class, 'cancel']);
        });

        // Admin API papildomai tikrina, ar vartotojas turi administratoriaus teisę.
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

// api.php maršrutai komentaro pabaiga
