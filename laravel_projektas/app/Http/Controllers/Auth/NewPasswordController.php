<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\View\View;

class NewPasswordController extends Controller
{
    public function create(Request $request): View
    {
        return view('auth.reset-password', [
            'request' => $request,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->merge([
            'email' => Str::lower(trim((string) $request->input('email'))),
        ]);

        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email:rfc', 'max:255'],
            'password' => [
                'required',
                'confirmed',
                'min:8',
                'regex:/[A-Z]/',
                'regex:/\d/',
                'regex:/[^A-Za-z0-9]/',
            ],
        ], [
            'email.required' => 'Įveskite el. paštą.',
            'email.email' => 'Įveskite teisingą el. pašto adresą.',
            'password.required' => 'Įveskite naują slaptažodį.',
            'password.confirmed' => 'Slaptažodžiai nesutampa.',
            'password.min' => 'Slaptažodis turi būti bent iš 8 simbolių.',
            'password.regex' => 'Slaptažodis turi turėti bent 1 didžiąją raidę, bent 1 skaičių ir bent 1 specialų simbolį.',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return redirect()
                ->route('login')
                ->with('status', 'Slaptažodis pakeistas. Dabar galite prisijungti.');
        }

        return back()
            ->withInput($request->only('email'))
            ->withErrors([
                'email' => 'Nuoroda neteisinga arba nebegalioja.',
            ]);
    }
}