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
    // registracijos formos atidarymas komentaro pradzia
    // Cia tiesiog grazinamas registracijos puslapis.
    // Pats vartotojo sukurimas vyksta store metode.
    // registracijos formos atidarymas komentaro pabaiga
    public function create(): View
    {
        return view('auth.register', [
            'recaptchaEnabled' => (bool) config('services.recaptcha.enabled', false),
            'recaptchaSiteKey' => config('services.recaptcha.site_key'),
        ]);
    }

    // vartotojo registravimas komentaro pradzia
    // Sitas metodas veikia kai vartotojas uzpildo registracijos forma ir spaudzia registruotis.
    // Cia tikrinami duomenys, recaptcha, taisykliu checkbox ir sukuriamas vartotojas.
    // vartotojo registravimas komentaro pabaiga
    public function store(Request $request, RecaptchaService $recaptcha): RedirectResponse
    {
        // vardo ir email sutvarkymas komentaro pradzia
        // Pries validacija vardas apkarpomas nuo nereikalingu tarpu, o email paverciamas mazosiomis raidemis.
        // Taip duomenys i DB patenka tvarkingesni.
        // vardo ir email sutvarkymas komentaro pabaiga
        $request->merge([
            'name' => trim((string) $request->input('name')),
            'email' => Str::lower(trim((string) $request->input('email'))),
        ]);

        // registracijos validacija komentaro pradzia
        // Cia tikrinama ar vardas, email ir slaptazodis atitinka taisykles.
        // Taip pat terms turi buti accepted, kitaip vartotojas negales registruotis.
        // registracijos validacija komentaro pabaiga
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
            // taisykliu checkbox backend puseje komentaro pradzia
            // Cia svarbi vieta: vartotojas privalo sutikti su taisyklemis.
            // Net jei kazkas bandytu apeiti frontend, backend vistiek neleis registruotis be accepted.
            // taisykliu checkbox backend puseje komentaro pabaiga
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
        // recaptcha registracijoje komentaro pradzia
        // Cia registracijos tokenas perduodamas i RecaptchaService.
        // Jei Google patikra nepraeina, vartotojas nebus sukurtas.
        // recaptcha registracijoje komentaro pabaiga
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

        // vartotojo sukurimas komentaro pradzia
        // Cia sukuriamas naujas vartotojas duomenu bazeje.
        // Slaptazodis neirasomas paprastu tekstu, jis pries tai uzhashinamas.
        // vartotojo sukurimas komentaro pabaiga
        $user = User::create([
            'name' => (string) $request->input('name'),
            'email' => (string) $request->input('email'),
            // Slaptažodis į DB niekada nesaugomas paprastu tekstu, čia jis užhashinamas.
            // slaptazodzio hash eilute komentaro pradzia
            // Cia tikras slaptazodis neirasomas i DB.
            // Hash::make ji uzkoduoja, todel duomenu bazeje saugomas tik hash.
            // slaptazodzio hash eilute komentaro pabaiga
            'password' => Hash::make((string) $request->input('password')),
        ]);

        event(new Registered($user));

        // automatinis prisijungimas po registracijos komentaro pradzia
        // Kai vartotojas sekmingai sukuriamas, jis iskart prijungiamas prie sistemos.
        // Tada nukreipiamas i norima puslapi.
        // automatinis prisijungimas po registracijos komentaro pabaiga
        Auth::login($user);
        $request->session()->regenerate();

        return redirect(route('dashboard', absolute: false));
    }

}