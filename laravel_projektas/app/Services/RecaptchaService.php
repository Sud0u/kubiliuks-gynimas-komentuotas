<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class RecaptchaService
{
    public function verify(?string $token, string $expectedAction): array
    {
        $enabled = (bool) config('services.recaptcha.enabled', false);

        if (!$enabled) {
            return [
                'ok' => true,
                'score' => 1,
                'action' => $expectedAction,
            ];
        }

        $secret = (string) config('services.recaptcha.secret_key');
        $minScore = (float) config('services.recaptcha.min_score', 0.5);

        if (!$secret) {
            return [
                'ok' => false,
                'message' => 'Nepavyko patikrinti saugumo. Bandykite dar kartą.',
            ];
        }

        if (!$token) {
            return [
                'ok' => false,
                'message' => 'Nepavyko patvirtinti saugumo. Bandykite dar kartą.',
            ];
        }

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

        $success = (bool) ($data['success'] ?? false);
        $score = (float) ($data['score'] ?? 0);
        $action = (string) ($data['action'] ?? '');

        if (!$success) {
            return [
                'ok' => false,
                'message' => 'Nepavyko patvirtinti saugumo patikros.',
            ];
        }

        if ($action !== $expectedAction) {
            return [
                'ok' => false,
                'message' => 'Gautas neteisingas saugumo atsakymas.',
            ];
        }

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
}