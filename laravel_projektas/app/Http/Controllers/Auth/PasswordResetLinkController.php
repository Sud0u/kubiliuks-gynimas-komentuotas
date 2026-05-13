<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PasswordResetLinkController extends Controller
{
    public function create(): View
    {
        return view('auth.forgot-password');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->merge([
            'email' => Str::lower(trim((string) $request->input('email'))),
        ]);

        $request->validate([
            'email' => ['required', 'email:rfc', 'max:255'],
        ], [
            'email.required' => 'Įveskite el. paštą.',
            'email.email' => 'Įveskite teisingą el. pašto adresą.',
            'email.max' => 'El. paštas per ilgas.',
        ]);

        Password::sendResetLink(
            $request->only('email')
        );

        return back()->with('status', 'Jeigu toks el. paštas yra sistemoje, išsiuntėme slaptažodžio atkūrimo nuorodą.');
    }
}