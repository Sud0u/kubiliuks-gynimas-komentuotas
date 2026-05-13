<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use WebToPay;

class PayseraService
{
    public function isEnabled(): bool
    {
        return (bool) config('services.paysera.enabled', false);
    }

    public function isConfigured(): bool
    {
        return $this->isEnabled()
            && !empty(config('services.paysera.project_id'))
            && !empty(config('services.paysera.sign_password'));
    }

    public function getProjectId(): int
    {
        return (int) config('services.paysera.project_id');
    }

    public function getSignPassword(): string
    {
        return (string) config('services.paysera.sign_password');
    }

    // KODO PRADŽIA: Paysera redirect URL sukūrimas
    // Čia iš užsakymo duomenų suformuojama nuoroda į Paysera mokėjimo langą.
    public function buildCheckoutUrl(Order $order): string
    {
        if (!$this->isConfigured()) {
            throw new RuntimeException('Paysera dar nesukonfigūruota.');
        }

        $request = WebToPay::buildRequest([
            'projectid' => $this->getProjectId(),
            'sign_password' => $this->getSignPassword(),
            'orderid' => (string) $order->id,
            'amount' => $this->toCents((float) $order->total_amount),
            'currency' => (string) config('services.paysera.currency', 'EUR'),
            'country' => (string) config('services.paysera.country', 'LT'),
            // Šios trys nuorodos pasako Paysera, kur grąžinti klientą ir kur siųsti callback.
            'accepturl' => route('paysera.accept', ['order' => $order->id]),
            'cancelurl' => route('paysera.cancel', ['order' => $order->id]),
            'callbackurl' => route('paysera.callback', ['order' => $order->id]),
            // Kai PAYSERA_TEST=0, mokėjimas vyksta realiu režimu.
            'test' => (int) config('services.paysera.test', 1),
            'lang' => 'LIT',
            'paytext' => 'Užsakymas #' . $order->id . ' | Kubiliuks',
            'p_firstname' => $this->extractFirstName($order->customer_name),
            'p_email' => $order->customer_email,
            'p_phone' => $order->customer_phone,
        ]);

        return 'https://bank.paysera.com/pay/?' . http_build_query($request);
    }

    // KODO PABAIGA: Paysera redirect URL sukūrimas

    // KODO PRADŽIA: Paysera callback patikra
    public function validateCallback(array $request): array
    {
        if (!$this->isConfigured()) {
            throw new RuntimeException('Paysera dar nesukonfigūruota.');
        }

        // WebToPay patikrina Paysera parašą pagal projekto ID ir slaptažodį.
        return WebToPay::validateAndParseData(
            $request,
            $this->getProjectId(),
            $this->getSignPassword()
        );
    }

    // Čia saugumo patikra: ar klientas apmokėjo būtent tą sumą, kuri yra užsakyme.
    public function assertPaymentMatches(Order $order, array $response): void
    {
        $expectedAmount = $this->toCents((float) $order->total_amount);
        $expectedCurrency = (string) config('services.paysera.currency', 'EUR');

        $paidAmount = array_key_exists('payamount', $response)
            ? (int) $response['payamount']
            : (int) ($response['amount'] ?? 0);

        $paidCurrency = array_key_exists('paycurrency', $response)
            ? (string) $response['paycurrency']
            : (string) ($response['currency'] ?? '');

        if ($expectedAmount !== $paidAmount || $expectedCurrency !== $paidCurrency) {
            throw new RuntimeException('Gauta neteisinga mokėjimo suma arba valiuta.');
        }
    }

    // KODO PABAIGA: Paysera callback patikra

    private function toCents(float $amount): int
    {
        return (int) round($amount * 100);
    }

    private function extractFirstName(?string $fullName): string
    {
        $fullName = trim((string) $fullName);

        if ($fullName === '') {
            return 'Klientas';
        }

        $parts = preg_split('/\s+/', $fullName);

        return (string) ($parts[0] ?? 'Klientas');
    }
}