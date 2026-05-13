<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\RecaptchaService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    public function create(): View
    {
        return view('auth.register', [
            'recaptchaEnabled' => (bool) config('services.recaptcha.enabled', false),
            'recaptchaSiteKey' => config('services.recaptcha.site_key'),
        ]);
    }

    public function store(Request $request, RecaptchaService $recaptcha): RedirectResponse
    {
        $request->merge([
            'name' => trim((string) $request->input('name')),
            'email' => Str::lower(trim((string) $request->input('email'))),
        ]);

        $request->validate([
            'name' => ['required', 'string', 'min:2', 'max:255'],
            'email' => ['required', 'string', 'email:rfc', 'max:255', 'unique:users,email'],
            'password' => [
                'required',
                'confirmed',
                'min:8',
                'regex:/[A-Z]/',
                'regex:/\d/',
                'regex:/[^A-Za-z0-9]/',
            ],
            // Jeigu vartotojas nepažymi šio laukelio, registracija nebus leidžiama.
            'terms' => ['accepted'],
        ], [
            'name.required' => 'Įveskite vardą.',
            'name.min' => 'Vardas per trumpas.',
            'name.max' => 'Vardas per ilgas.',
            'email.required' => 'Įveskite el. paštą.',
            'email.email' => 'Neteisingas el. pašto formatas.',
            'email.max' => 'El. paštas per ilgas.',
            'email.unique' => 'Toks el. paštas jau naudojamas.',
            'password.required' => 'Įveskite slaptažodį.',
            'password.min' => 'Slaptažodis turi būti bent iš 8 simbolių.',
            'password.confirmed' => 'Slaptažodžiai nesutampa.',
            'password.regex' => 'Slaptažodis turi turėti bent 1 didžiąją raidę, bent 1 skaičių ir bent 1 specialų simbolį.',
            'terms.accepted' => 'Norėdami užsiregistruoti, turite sutikti su taisyklėmis ir privatumo politika.',
        ]);

        // Prieš sukuriant vartotoją patikrinama reCAPTCHA.
        $result = $recaptcha->verify(
            $request->input('recaptcha_token'),
            'register'
        );

        if (! $result['ok']) {
            return back()
                ->withErrors([
                    'email' => $result['message'],
                ])
                ->withInput();
        }

        $user = User::create([
            'name' => (string) $request->input('name'),
            'email' => (string) $request->input('email'),
            // Slaptažodis į DB niekada nesaugomas paprastu tekstu, čia jis užhashinamas.
            'password' => Hash::make((string) $request->input('password')),
        ]);

        event(new Registered($user));

        Auth::login($user);
        $request->session()->regenerate();

        return redirect(route('dashboard', absolute: false));
    }

}