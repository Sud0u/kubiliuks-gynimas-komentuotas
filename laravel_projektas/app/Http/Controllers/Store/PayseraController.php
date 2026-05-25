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

    // cia vartotojas grizta is Paysera po bandymo apmoketi.
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

    // cia Paysera serveris atsiuncia tikra mokejimo patvirtinima.
    public function callback(Request $request, Order $order)
    {
        try {
            // pirmiausia tikrinamas Paysera parasas ir gauti duomenys.
            $response = $this->paysera->validateCallback($request->all());

            if ((string) ($response['orderid'] ?? '') !== (string) $order->id) {
                throw new \RuntimeException('Nesutampa užsakymo numeris.');
            }
            if (!in_array((string) ($response['status'] ?? ''), ['1', '3'], true)) {
                throw new \RuntimeException('Mokėjimas nebuvo sėkmingas.');
            }

            // cia saugoma, kad mokėjimas atitiktu būtent sita orderi.
            $this->paysera->assertPaymentMatches($order, $response);

            DB::transaction(function () use ($order, $response) {
                $payment = $order->payment;

                if (!$payment) {
                    throw new \RuntimeException('Nerastas su užsakymu susietas mokėjimas.');
                }

                $meta = $payment->meta ?? [];
                $meta['paysera_callback'] = $response;

                // tik po sekmingo callback mokejimas pazymimas kaip paid.
                $payment->update([
                    'provider' => 'paysera',
                    'status' => 'paid',
                    'transaction_id' => (string) ($response['payment'] ?? $response['tranid'] ?? $response['orderid'] ?? $order->id),
                    'amount' => $order->total_amount,
                    'paid_at' => now(),
                    'meta' => $meta,
                ]);

                if (!$order->paid_at || $order->status === 'pending') {
                    $order->update([
                        'status' => 'paid',
                        'paid_at' => now(),
                    ]);
                }
            });

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
}