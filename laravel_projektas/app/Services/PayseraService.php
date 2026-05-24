<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use WebToPay;

class PayseraService
{
    // ar Paysera ijungta komentaro pradzia
    // Cia tikrinama konfiguracija ar Paysera funkcija ijungta.
    // Local arba testavimo metu galima isjungti, o live serveryje ijungti per .env.
    // ar Paysera ijungta komentaro pabaiga
    public function isEnabled(): bool
    {
        return (bool) config('services.paysera.enabled', false);
    }

    // ar Paysera sukonfiguruota komentaro pradzia
    // Cia patikrinama ar yra projekto id ir slaptazodis.
    // Be situ duomenu Paysera mokejimo nuoroda negali buti saugiai sukurta.
    // ar Paysera sukonfiguruota komentaro pabaiga
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

   
    // Cia is uzsakymo duomenu paruosiama Paysera mokejimo nuoroda.
    // I ja vartotojas nukreipiamas kai pasirenka apmoketi per Paysera.
    // sukuriama paysera nuoroda uzsakymo
    public function buildCheckoutUrl(Order $order): string
    {
        if (!$this->isConfigured()) {
            throw new RuntimeException('Paysera dar nesukonfigūruota.');
        }

        // duomenys siunciami Paysera komentaro pradzia
        // Cia sudedami Paysera reikalingi laukai: projectid, orderid, amount, currency ir callback adresai.
        // Amount siunciamas centais, todel eurai pries tai paverciami i centus.
        // duomenys siunciami Paysera komentaro pabaiga
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
 //////// sukuriama nuoroda payseros
        return 'https://bank.paysera.com/pay/?' . http_build_query($request);
    }

    // Paysera redirect URL sukūrimas komentaro pabaiga

    // Paysera callback patikra komentaro pradzia
    // Paysera callback tikrinimas service faile komentaro pradzia
    // Cia Paysera biblioteka patikrina callback duomenis.
    // Jei parasas blogas arba duomenys neteisingi, bus klaida ir statusai nebus pakeisti.
    // Paysera callback tikrinimas service faile komentaro pabaiga
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
    // Paysera sumos ir orderio patikrinimas komentaro pradzia
    // Cia tikrinama ar Paysera orderid sutampa su musu uzsakymo id.
    // Taip pat tikrinama suma, kad apmoketa suma atitiktu uzsakymo suma.
    // Paysera sumos ir orderio patikrinimas komentaro pabaiga
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

    // Paysera callback patikra komentaro pabaiga

    // eurai i centus komentaro pradzia
    // Paysera sumas priima centais, todel cia eurai paverciami i centus.
    // Pvz 25.50 euro tampa 2550 centu.
    // eurai i centus komentaro pabaiga
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