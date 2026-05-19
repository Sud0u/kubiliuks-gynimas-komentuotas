<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class RecaptchaService
{
    // reCAPTCHA patikra komentaro pradzia
    // Čia tikrinama, ar prisijungimo / registracijos veiksmas nėra botas.
    // reCAPTCHA tokeno tikrinimas komentaro pradzia
    // Cia serverio puseje tikrinamas reCAPTCHA tokenas.
    // Tokenas padeda suprasti ar veiksma atliko tikras vartotojas, o ne botas.
    // reCAPTCHA tokeno tikrinimas komentaro pabaiga
    public function verify(?string $token, string $expectedAction): array
    {
        // ar recaptcha ijungta komentaro pradzia
        // Cia paimama reiksme is config, kuri ateina is .env.
        // Local galima laikyti false, o live serveryje true.
        // ar recaptcha ijungta komentaro pabaiga
        $enabled = (bool) config('services.recaptcha.enabled', false);

        // Local aplinkoje galima išjungti, kad būtų lengviau testuoti. Live serveryje įjungiama per .env.
        // kai recaptcha isjungta komentaro pradzia
        // Jei recaptcha isjungta, metodas grazina ok true.
        // Taip local testavimas veikia net be Google patikros.
        // kai recaptcha isjungta komentaro pabaiga
        if (!$enabled) {
            return [
                'ok' => true,
                'score' => 1,
                'action' => $expectedAction,
            ];
        }

        // slaptas recaptcha raktas ir score komentaro pradzia
        // Cia paimamas slaptas Google raktas ir minimalus score.
        // Score parodo kiek veiksmas panasus i tikra zmogu.
        // slaptas recaptcha raktas ir score komentaro pabaiga
        $secret = (string) config('services.recaptcha.secret_key');
        $minScore = (float) config('services.recaptcha.min_score', 0.5);

        if (!$secret) {
            return [
                'ok' => false,
                'message' => 'Nepavyko patikrinti saugumo. Bandykite dar kartą.',
            ];
        }

        // tokeno patikrinimas komentaro pradzia
        // Jei frontend neatsiuncia tokeno, saugumo patikra laikoma nepavykusia.
        // Tokenas reikalingas, kad Google galetu patikrinti veiksma.
        // tokeno patikrinimas komentaro pabaiga
        if (!$token) {
            return [
                'ok' => false,
                'message' => 'Nepavyko patvirtinti saugumo. Bandykite dar kartą.',
            ];
        }

        // Tokenas siunčiamas Google patikrai.
        // uzklausa i Google recaptcha komentaro pradzia
        // Cia serveris issiuncia tokena i Google siteverify API.
        // Google atsako ar tokenas galiojantis, koks action ir koks score.
        // uzklausa i Google recaptcha komentaro pabaiga
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

        // Google atsakymo nuskaitymas komentaro pradzia
        // Cia is Google atsakymo paimamas success, score ir action.
        // Sitie duomenys zemiau naudojami sprendimui ar leisti veiksma.
        // Google atsakymo nuskaitymas komentaro pabaiga
        $success = (bool) ($data['success'] ?? false);
        $score = (float) ($data['score'] ?? 0);
        $action = (string) ($data['action'] ?? '');

        if (!$success) {
            return [
                'ok' => false,
                'message' => 'Nepavyko patvirtinti saugumo patikros.',
            ];
        }

        // action sutapimas komentaro pradzia
        // Cia tikrinama ar veiksmas yra tas pats, kurio tikejomes.
        // Pvz registracijai turi buti register, o checkoutui kitas action jei naudojamas.
        // action sutapimas komentaro pabaiga
        if ($action !== $expectedAction) {
            return [
                'ok' => false,
                'message' => 'Gautas neteisingas saugumo atsakymas.',
            ];
        }

        // Score parodo, kiek veiksmas panašus į tikrą žmogų. Per mažas score atmetamas.
        // score patikrinimas komentaro pradzia
        // Jei score per mazas, veiksmas atrodo itartinas.
        // Tada sistema grazina klaida ir neleidzia testi veiksmo.
        // score patikrinimas komentaro pabaiga
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
    // reCAPTCHA patikra komentaro pabaiga
}