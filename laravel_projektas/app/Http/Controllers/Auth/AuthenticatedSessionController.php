<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Services\RecaptchaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    public function create(): View
    {
        return view('auth.login', [
            'recaptchaEnabled' => (bool) config('services.recaptcha.enabled', false),
            'recaptchaSiteKey' => config('services.recaptcha.site_key'),
        ]);
    }

    public function store(LoginRequest $request, RecaptchaService $recaptcha): RedirectResponse
    {
        // Prisijungime taip pat tikrinama reCAPTCHA.
        $result = $recaptcha->verify(
            $request->input('recaptcha_token'),
            'login'
        );

        if (!$result['ok']) {
            return back()
                ->withErrors([
                    'email' => $result['message'],
                ])
                ->onlyInput('email');
        }

        $request->authenticate();

        $request->session()->regenerate();

        return redirect()->intended(route('dashboard', absolute: false));
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}