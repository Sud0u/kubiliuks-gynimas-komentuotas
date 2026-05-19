<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\PayseraService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PayseraController extends Controller
{
    public function __construct(
        private readonly PayseraService $paysera
    ) {}

    // GYNIMO PAAISKINIMAS PRADZIA: sekmingo Paysera grizimo puslapis
    // Cia vartotojas patenka kai Paysera nukreipia ji atgal po sekmingo mokejimo.
    // Tai daugiau vartotojo grizimo vieta, o realus statuso patvirtinimas vyksta callback metode.
    // GYNIMO PAAISKINIMAS PABAIGA: sekmingo Paysera grizimo puslapis
    public function accept(Order $order)
    {
        return redirect()
            ->route('orders.show', $order->id)
            ->with('success', 'Mokėjimas inicijuotas. Laukiame Paysera patvirtinimo.');
    }

    // GYNIMO PAAISKINIMAS PRADZIA: atsaukto Paysera mokejimo puslapis
    // Cia vartotojas patenka jei Paysera mokejima atsaukia arba neuzbaigia.
    // Uzsakymas del to iskart netampa apmoketas.
    // GYNIMO PAAISKINIMAS PABAIGA: atsaukto Paysera mokejimo puslapis
    public function cancel(Order $order)
    {
        if ($order->payment && $order->payment->status !== 'paid') {
            $meta = $order->payment->meta ?? [];
            $meta['cancelled_by_user_at'] = now()->toISOString();

            $order->payment->update([
                'status' => 'cancelled',
                'meta' => $meta,
            ]);
        }

        return redirect()
            ->route('orders.show', $order->id)
            ->with('error', 'Mokėjimas buvo nutrauktas. Galėsite bandyti dar kartą.');
    }

    // KODO PRADŽIA: Paysera callback
    // Šitą metodą kviečia ne klientas, o Paysera po mokėjimo.
    // GYNIMO PAAISKINIMAS PRADZIA: Paysera callback
    // Cia svarbiausia Paysera vieta.
    // Paysera serveris atsiuncia atsakyma, o sistema patikrina ar mokejimas tikras ir ar priklauso sitam uzsakymui.
    // GYNIMO PAAISKINIMAS PABAIGA: Paysera callback
    public function callback(Request $request, Order $order)
    {
        try {
            // Pirma patikrinamas Paysera parašas, kad callback būtų tikras.
            // GYNIMO PAAISKINIMAS PRADZIA: callback validavimas
            // Cia PayseraService patikrina Paysera duomenis ir parasa.
            // Jei duomenys neteisingi, zemiau bus catch ir callback nepatvirtins uzsakymo.
            // GYNIMO PAAISKINIMAS PABAIGA: callback validavimas
            $response = $this->paysera->validateCallback($request->all());

            if ((string) ($response['orderid'] ?? '') !== (string) $order->id) {
                throw new \RuntimeException('Nesutampa užsakymo numeris.');
            }

            if (!in_array((string) ($response['status'] ?? ''), ['1', '3'], true)) {
                throw new \RuntimeException('Mokėjimas nebuvo sėkmingas.');
            }

            // Papildomai patikrinama, ar apmokėta suma ir valiuta sutampa su užsakymu.
            // GYNIMO PAAISKINIMAS PRADZIA: ar mokejimas sutampa su orderiu
            // Cia tikrinama ar Paysera atsakymas priklauso butent sitam uzsakymui.
            // Taip apsaugoma, kad vieno uzsakymo mokejimas nebutu priskirtas kitam.
            // GYNIMO PAAISKINIMAS PABAIGA: ar mokejimas sutampa su orderiu
            $this->paysera->assertPaymentMatches($order, $response);

            // GYNIMO PAAISKINIMAS PRADZIA: statusu atnaujinimas transakcijoje
            // Cia po sekmingo Paysera patvirtinimo atnaujinamas payment ir order statusas.
            // Transakcija uztikrina, kad abu irasai butu pakeisti kartu.
            // GYNIMO PAAISKINIMAS PABAIGA: statusu atnaujinimas transakcijoje
            DB::transaction(function () use ($order, $response) {
                $payment = $order->payment;

                if (!$payment) {
                    throw new \RuntimeException('Nerastas su užsakymu susietas mokėjimas.');
                }

                $meta = $payment->meta ?? [];
                $meta['paysera_callback'] = $response;

                // Čia mokėjimo įrašas pažymimas kaip apmokėtas.
                $payment->update([
                    'provider' => 'paysera',
                    'status' => 'paid',
                    'transaction_id' => (string) ($response['payment'] ?? $response['tranid'] ?? $response['orderid'] ?? $order->id),
                    'amount' => $order->total_amount,
                    'paid_at' => now(),
                    'meta' => $meta,
                ]);

                // Čia kartu atnaujinamas ir pats užsakymas, kad admin matytų paid statusą.
                if (!$order->paid_at || $order->status === 'pending') {
                    $order->update([
                        'status' => 'paid',
                        'paid_at' => now(),
                    ]);
                }
            });

            // Paysera turi gauti paprastą OK atsakymą.
            return response('OK', 200)->header('Content-Type', 'text/plain');
        } catch (\Throwable $e) {
            Log::error('Paysera callback klaida', [
                'order_id' => $order->id,
                'message' => $e->getMessage(),
                'request' => $request->all(),
            ]);

            return response('ERROR', 400)->header('Content-Type', 'text/plain');
        }
    }
    // KODO PABAIGA: Paysera callback
}