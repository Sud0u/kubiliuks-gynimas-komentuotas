<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class RecaptchaService
{
    // KODO PRADŽIA: reCAPTCHA patikra
    // Čia tikrinama, ar prisijungimo / registracijos veiksmas nėra botas.
    // GYNIMO PAAISKINIMAS PRADZIA: reCAPTCHA tokeno tikrinimas
    // Cia serverio puseje tikrinamas reCAPTCHA tokenas.
    // Tokenas padeda suprasti ar veiksma atliko tikras vartotojas, o ne botas.
    // GYNIMO PAAISKINIMAS PABAIGA: reCAPTCHA tokeno tikrinimas
    public function verify(?string $token, string $expectedAction): array
    {
        // GYNIMO PAAISKINIMAS PRADZIA: ar recaptcha ijungta
        // Cia paimama reiksme is config, kuri ateina is .env.
        // Local galima laikyti false, o live serveryje true.
        // GYNIMO PAAISKINIMAS PABAIGA: ar recaptcha ijungta
        $enabled = (bool) config('services.recaptcha.enabled', false);

        // Local aplinkoje galima išjungti, kad būtų lengviau testuoti. Live serveryje įjungiama per .env.
        // GYNIMO PAAISKINIMAS PRADZIA: kai recaptcha isjungta
        // Jei recaptcha isjungta, metodas grazina ok true.
        // Taip local testavimas veikia net be Google patikros.
        // GYNIMO PAAISKINIMAS PABAIGA: kai recaptcha isjungta
        if (!$enabled) {
            return [
                'ok' => true,
                'score' => 1,
                'action' => $expectedAction,
            ];
        }

        // GYNIMO PAAISKINIMAS PRADZIA: slaptas recaptcha raktas ir score
        // Cia paimamas slaptas Google raktas ir minimalus score.
        // Score parodo kiek veiksmas panasus i tikra zmogu.
        // GYNIMO PAAISKINIMAS PABAIGA: slaptas recaptcha raktas ir score
        $secret = (string) config('services.recaptcha.secret_key');
        $minScore = (float) config('services.recaptcha.min_score', 0.5);

        if (!$secret) {
            return [
                'ok' => false,
                'message' => 'Nepavyko patikrinti saugumo. Bandykite dar kartą.',
            ];
        }

        // GYNIMO PAAISKINIMAS PRADZIA: tokeno patikrinimas
        // Jei frontend neatsiuncia tokeno, saugumo patikra laikoma nepavykusia.
        // Tokenas reikalingas, kad Google galetu patikrinti veiksma.
        // GYNIMO PAAISKINIMAS PABAIGA: tokeno patikrinimas
        if (!$token) {
            return [
                'ok' => false,
                'message' => 'Nepavyko patvirtinti saugumo. Bandykite dar kartą.',
            ];
        }

        // Tokenas siunčiamas Google patikrai.
        // GYNIMO PAAISKINIMAS PRADZIA: uzklausa i Google recaptcha
        // Cia serveris issiuncia tokena i Google siteverify API.
        // Google atsako ar tokenas galiojantis, koks action ir koks score.
        // GYNIMO PAAISKINIMAS PABAIGA: uzklausa i Google recaptcha
        $response = Http::asForm()
            ->timeout(10)
            ->post('https://www.google.com/recaptcha/api/siteverify', [
                'secret' => $secret,
                'response' => $token,
            ]);

        if (!$response->ok()) {
            return [
                'ok' => false,
                'message' => 'Google saugumo patikra laikinai nepasiekiama. Bandykite dar kartą.',
            ];
        }

        $data = $response->json();

        // GYNIMO PAAISKINIMAS PRADZIA: Google atsakymo nuskaitymas
        // Cia is Google atsakymo paimamas success, score ir action.
        // Sitie duomenys zemiau naudojami sprendimui ar leisti veiksma.
        // GYNIMO PAAISKINIMAS PABAIGA: Google atsakymo nuskaitymas
        $success = (bool) ($data['success'] ?? false);
        $score = (float) ($data['score'] ?? 0);
        $action = (string) ($data['action'] ?? '');

        if (!$success) {
            return [
                'ok' => false,
                'message' => 'Nepavyko patvirtinti saugumo patikros.',
            ];
        }

        // GYNIMO PAAISKINIMAS PRADZIA: action sutapimas
        // Cia tikrinama ar veiksmas yra tas pats, kurio tikejomes.
        // Pvz registracijai turi buti register, o checkoutui kitas action jei naudojamas.
        // GYNIMO PAAISKINIMAS PABAIGA: action sutapimas
        if ($action !== $expectedAction) {
            return [
                'ok' => false,
                'message' => 'Gautas neteisingas saugumo atsakymas.',
            ];
        }

        // Score parodo, kiek veiksmas panašus į tikrą žmogų. Per mažas score atmetamas.
        // GYNIMO PAAISKINIMAS PRADZIA: score patikrinimas
        // Jei score per mazas, veiksmas atrodo itartinas.
        // Tada sistema grazina klaida ir neleidzia testi veiksmo.
        // GYNIMO PAAISKINIMAS PABAIGA: score patikrinimas
        if ($score < $minScore) {
            return [
                'ok' => false,
                'message' => 'Google saugumo patikra nepavyko. Bandykite dar kartą.',
            ];
        }

        return [
            'ok' => true,
            'score' => $score,
            'action' => $action,
        ];
    }
    // KODO PABAIGA: reCAPTCHA patikra
}