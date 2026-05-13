<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            \App\Repositories\Contracts\ProductRepositoryInterface::class,
            \App\Repositories\ProductRepository::class
        );

        $this->app->bind(
            \App\Repositories\Contracts\OrderRepositoryInterface::class,
            \App\Repositories\OrderRepository::class
        );
    }

    public function boot(): void
    {
        Gate::define('isAdmin', function (User $user) {
            return (bool) $user->is_admin;
        });

        if (app()->environment('production')) {
            URL::forceScheme('https');
        }

        RateLimiter::for('login', function (Request $request) {
            $email = (string) $request->input('email');
            $ip = (string) $request->ip();

            return [
                Limit::perMinute(5)->by($email . '|' . $ip)->response(function () {
                    return response()->json([
                        'message' => 'Per daug prisijungimo bandymų. Pabandykite po minutės.',
                    ], 429);
                }),
            ];
        });

        RateLimiter::for('login-web', function (Request $request) {
            $email = (string) $request->input('email');
            $ip = (string) $request->ip();

            return [
                Limit::perMinute(5)->by('web-login|' . $email . '|' . $ip)->response(function () {
                    return back()
                        ->withErrors([
                            'email' => 'Per daug prisijungimo bandymų. Pabandykite po minutės.',
                        ])
                        ->withInput();
                }),
            ];
        });

        RateLimiter::for('register', function (Request $request) {
            $ip = (string) $request->ip();

            return [
                Limit::perMinute(3)->by('register|' . $ip)->response(function () {
                    return response()->json([
                        'message' => 'Per daug registracijos bandymų. Pabandykite po minutės.',
                    ], 429);
                }),
            ];
        });

        RateLimiter::for('register-web', function (Request $request) {
            $ip = (string) $request->ip();

            return [
                Limit::perMinute(3)->by('register-web|' . $ip)->response(function () {
                    return back()
                        ->withErrors([
                            'email' => 'Per daug registracijos bandymų. Pabandykite po minutės.',
                        ])
                        ->withInput();
                }),
            ];
        });

        RateLimiter::for('cart', function (Request $request) {
            $userKey = $request->user()?->id ? 'user:' . $request->user()->id : 'ip:' . $request->ip();

            return [
                Limit::perMinute(60)->by('cart|' . $userKey),
            ];
        });

        RateLimiter::for('orders', function (Request $request) {
            $userKey = $request->user()?->id ? 'user:' . $request->user()->id : 'ip:' . $request->ip();

            return [
                Limit::perMinute(20)->by('orders|' . $userKey)->response(function () {
                    return response()->json([
                        'message' => 'Per daug užsakymų veiksmų. Pabandykite dar kartą po minutės.',
                    ], 429);
                }),
            ];
        });

        RateLimiter::for('admin-api', function (Request $request) {
            $userKey = $request->user()?->id ? 'admin:' . $request->user()->id : 'ip:' . $request->ip();

            return [
                Limit::perMinute(120)->by('admin-api|' . $userKey),
            ];
        });
    }
}