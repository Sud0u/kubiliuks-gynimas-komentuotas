<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        if (Auth::check()) {
            return response()->json([
                'message' => 'Jūs jau prisijungęs.',
                'data' => $this->userData($request->user()),
            ], 200);
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => [
                'required',
                'confirmed',
                'min:8',
                'regex:/[A-Z]/',
                'regex:/(?:.*\d){2,}/',
            ],
        ], [
            'name.required' => 'Įveskite vardą.',
            'email.required' => 'Įveskite el. paštą.',
            'email.email' => 'Neteisingas el. pašto formatas.',
            'email.unique' => 'Toks el. paštas jau naudojamas.',
            'password.required' => 'Įveskite slaptažodį.',
            'password.min' => 'Slaptažodis turi būti bent iš 8 simbolių.',
            'password.confirmed' => 'Slaptažodžiai nesutampa.',
            'password.regex' => 'Slaptažodis turi turėti bent 1 didžiąją raidę ir bent 2 skaičius.',
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'is_admin' => 0,
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        return response()->json([
            'message' => 'Registracija sėkminga.',
            'data' => $this->userData($user),
        ], 201);
    }

    public function login(Request $request)
    {
        if (Auth::check()) {
            return response()->json([
                'message' => 'Jūs jau prisijungęs.',
                'data' => $this->userData($request->user()),
            ], 200);
        }

        $credentials = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ], [
            'email.required' => 'Įveskite el. paštą.',
            'email.email' => 'Neteisingas el. pašto formatas.',
            'password.required' => 'Įveskite slaptažodį.',
        ]);

        if (!Auth::attempt($credentials)) {
            throw ValidationException::withMessages([
                'email' => ['Neteisingi prisijungimo duomenys.'],
            ]);
        }

        $request->session()->regenerate();

        return response()->json([
            'message' => 'Prisijungimas sėkmingas.',
            'data' => $this->userData($request->user()),
        ]);
    }

    public function me(Request $request)
    {
        if (!$request->user()) {
            return response()->json([
                'message' => 'Neprisijungta.',
                'data' => null,
            ], 401);
        }

        return response()->json([
            'data' => $this->userData($request->user()),
        ]);
    }

    public function logout(Request $request)
    {
        if (!Auth::check()) {
            return response()->json([
                'message' => 'Naudotojas jau atsijungęs.',
            ]);
        }

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json([
            'message' => 'Atsijungimas sėkmingas.',
        ]);
    }

    private function userData(User $user): array
    {
        return [
            'id' => (int) $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'is_admin' => (bool) $user->is_admin,
        ];
    }
}