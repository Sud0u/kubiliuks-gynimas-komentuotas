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

    // sekmingo Paysera grizimo puslapis komentaro pradzia
    // Cia vartotojas patenka kai Paysera nukreipia ji atgal po sekmingo mokejimo.
    // Tai daugiau vartotojo grizimo vieta, o realus statuso patvirtinimas vyksta callback metode.
    // sekmingo Paysera grizimo puslapis komentaro pabaiga
    public function accept(Order $order)
    {
        $order->load('payment');

        if ($order->payment?->status === 'paid') {
            return redirect()
                ->route('orders.show', $order->id)
                ->with('success', 'Mokėjimas gautas. Užsakymas pažymėtas kaip apmokėtas.');
        }

        return redirect()
            ->route('orders.show', $order->id)
            ->with('success', 'Grįžote iš Paysera. Jei mokėjimo neužbaigėte, užsakymas lieka neapmokėtas.');
    }

    // atsaukto Paysera mokejimo puslapis komentaro pradzia
    // Cia vartotojas patenka jei Paysera mokejima atsaukia arba neuzbaigia.
    // Uzsakymas del to iskart netampa apmoketas.
    // atsaukto Paysera mokejimo puslapis komentaro pabaiga
    public function cancel(Order $order)
    {
        if ($order->payment && $order->payment->status !== 'paid') {
            $meta = $order->payment->meta ?? [];
            $meta['cancelled_by_user_at'] = now()->toISOString();

            $meta['note'] = 'Paysera mokėjimas nutrauktas vartotojo pusėje. Užsakymas nėra apmokėtas.';

            $order->payment->update([
                'status' => 'cancelled',
                'meta' => $meta,
            ]);
        }

        return redirect()
            ->route('orders.show', $order->id)
            ->with('error', 'Mokėjimas buvo nutrauktas. Užsakymas nėra apmokėtas.');
    }

    // Paysera callback komentaro pradzia
    // Šitą metodą kviečia ne klientas, o Paysera po mokėjimo.
    // Paysera callback komentaro pradzia
    // Cia svarbiausia Paysera vieta.
    // Čia prasideda Paysera callback metodas. Šitą metodą kviečia ne vartotojas, o Paysera serveris po mokėjimo.
    //  Čia sistema gauna Paysera atsakymą ir tikrina, ar mokėjimas tikras.
    public function callback(Request $request, Order $order)
    {
        try {
            //PayseraService patikrina Paysera atsiųstus duomenis ir parašą. Jeigu callback duomenys netikri arba pakeisti, sistema jo nepriima.
            $response = $this->paysera->validateCallback($request->all());

            if ((string) ($response['orderid'] ?? '') !== (string) $order->id) {
                throw new \RuntimeException('Nesutampa užsakymo numeris.');
            }
        // sita eilute tikrina duomenis Jeigu statusas netinkamas, užsakymas nebus pažymėtas kaip apmokėtas.
            if (!in_array((string) ($response['status'] ?? ''), ['1', '3'], true)) {
                throw new \RuntimeException('Mokėjimas nebuvo sėkmingas.');
            }

            // Papildomai patikrinama, ar apmokėta suma ir valiuta sutampa su užsakymu.
            // ar mokejimas sutampa su orderiu komentaro pradzia
            // Cia tikrinama ar Paysera atsakymas priklauso butent sitam uzsakymui.
            // Taip apsaugoma, kad vieno uzsakymo mokejimas nebutu priskirtas kitam.
            // ar mokejimas sutampa su orderiu komentaro pabaiga
            $this->paysera->assertPaymentMatches($order, $response);

            // statusu atnaujinimas transakcijoje komentaro pradzia
            // Cia po sekmingo Paysera patvirtinimo atnaujinamas payment ir order statusas.
            // Transakcija uztikrina, kad abu irasai butu pakeisti kartu.
            // statusu atnaujinimas transakcijoje komentaro pabaiga
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
    // Paysera callback komentaro pabaiga
}