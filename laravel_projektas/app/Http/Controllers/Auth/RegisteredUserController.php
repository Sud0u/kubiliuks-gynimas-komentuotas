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
    // GYNIMO PAAISKINIMAS PRADZIA: registracijos formos atidarymas
    // Cia tiesiog grazinamas registracijos puslapis.
    // Pats vartotojo sukurimas vyksta store metode.
    // GYNIMO PAAISKINIMAS PABAIGA: registracijos formos atidarymas
    public function create(): View
    {
        return view('auth.register', [
            'recaptchaEnabled' => (bool) config('services.recaptcha.enabled', false),
            'recaptchaSiteKey' => config('services.recaptcha.site_key'),
        ]);
    }

    // GYNIMO PAAISKINIMAS PRADZIA: vartotojo registravimas
    // Sitas metodas veikia kai vartotojas uzpildo registracijos forma ir spaudzia registruotis.
    // Cia tikrinami duomenys, recaptcha, taisykliu checkbox ir sukuriamas vartotojas.
    // GYNIMO PAAISKINIMAS PABAIGA: vartotojo registravimas
    public function store(Request $request, RecaptchaService $recaptcha): RedirectResponse
    {
        // GYNIMO PAAISKINIMAS PRADZIA: vardo ir email sutvarkymas
        // Pries validacija vardas apkarpomas nuo nereikalingu tarpu, o email paverciamas mazosiomis raidemis.
        // Taip duomenys i DB patenka tvarkingesni.
        // GYNIMO PAAISKINIMAS PABAIGA: vardo ir email sutvarkymas
        $request->merge([
            'name' => trim((string) $request->input('name')),
            'email' => Str::lower(trim((string) $request->input('email'))),
        ]);

        // GYNIMO PAAISKINIMAS PRADZIA: registracijos validacija
        // Cia tikrinama ar vardas, email ir slaptazodis atitinka taisykles.
        // Taip pat terms turi buti accepted, kitaip vartotojas negales registruotis.
        // GYNIMO PAAISKINIMAS PABAIGA: registracijos validacija
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
            // GYNIMO PAAISKINIMAS PRADZIA: taisykliu checkbox backend puseje
            // Cia svarbi vieta: vartotojas privalo sutikti su taisyklemis.
            // Net jei kazkas bandytu apeiti frontend, backend vistiek neleis registruotis be accepted.
            // GYNIMO PAAISKINIMAS PABAIGA: taisykliu checkbox backend puseje
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
        // GYNIMO PAAISKINIMAS PRADZIA: recaptcha registracijoje
        // Cia registracijos tokenas perduodamas i RecaptchaService.
        // Jei Google patikra nepraeina, vartotojas nebus sukurtas.
        // GYNIMO PAAISKINIMAS PABAIGA: recaptcha registracijoje
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

        // GYNIMO PAAISKINIMAS PRADZIA: vartotojo sukurimas
        // Cia sukuriamas naujas vartotojas duomenu bazeje.
        // Slaptazodis neirasomas paprastu tekstu, jis pries tai uzhashinamas.
        // GYNIMO PAAISKINIMAS PABAIGA: vartotojo sukurimas
        $user = User::create([
            'name' => (string) $request->input('name'),
            'email' => (string) $request->input('email'),
            // Slaptažodis į DB niekada nesaugomas paprastu tekstu, čia jis užhashinamas.
            // GYNIMO PAAISKINIMAS PRADZIA: slaptazodzio hash eilute
            // Cia tikras slaptazodis neirasomas i DB.
            // Hash::make ji uzkoduoja, todel duomenu bazeje saugomas tik hash.
            // GYNIMO PAAISKINIMAS PABAIGA: slaptazodzio hash eilute
            'password' => Hash::make((string) $request->input('password')),
        ]);

        event(new Registered($user));

        // GYNIMO PAAISKINIMAS PRADZIA: automatinis prisijungimas po registracijos
        // Kai vartotojas sekmingai sukuriamas, jis iskart prijungiamas prie sistemos.
        // Tada nukreipiamas i norima puslapi.
        // GYNIMO PAAISKINIMAS PABAIGA: automatinis prisijungimas po registracijos
        Auth::login($user);
        $request->session()->regenerate();

        return redirect(route('dashboard', absolute: false));
    }

}