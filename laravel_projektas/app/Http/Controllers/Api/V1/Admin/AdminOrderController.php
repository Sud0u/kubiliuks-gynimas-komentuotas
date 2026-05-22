<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminOrderController extends Controller
{
    // admin uzsakymu sarasas komentaro pradzia
    // Cia admin gauna visus uzsakymus.
    // Galima ieskoti pagal kliento duomenis ir filtruoti pagal statusus.
    // admin uzsakymu sarasas komentaro pabaiga
    public function index(Request $request)
    {
        $status = trim((string) $request->query('status', 'all'));
        $q = trim((string) $request->query('q', ''));
        $perPage = (int) $request->query('per_page', 12);

        if ($perPage < 1) {
            $perPage = 12;
        }

        if ($perPage > 100) {
            $perPage = 100;
        }

        $query = Order::query()
            ->with(['payment:id,order_id,provider,status,transaction_id,amount,paid_at,meta'])
            ->select([
                'id',
                'customer_name',
                'customer_email',
                'customer_phone',
                'shipping_city',
                'status',
                'total_amount',
                'created_at',
            ])
            ->orderByDesc('created_at');

        if ($status !== '' && $status !== 'all') {
            $query->where('status', $status);
        }

        if ($q !== '') {
            $query->where(function ($w) use ($q) {
                if (is_numeric($q)) {
                    $w->orWhere('id', (int) $q);
                }

                $w->orWhere('customer_name', 'like', '%' . $q . '%')
                    ->orWhere('customer_email', 'like', '%' . $q . '%')
                    ->orWhere('customer_phone', 'like', '%' . $q . '%')
                    ->orWhere('shipping_city', 'like', '%' . $q . '%');
            });
        }

        $orders = $query->paginate($perPage)->withQueryString();

        $recent = Order::query()
            ->select([
                'id',
                'status',
                'total_amount',
                'created_at',
            ])
            ->orderByDesc('created_at')
            ->limit(6)
            ->get();

        return response()->json([
            'data' => $orders->items(),
            'meta' => [
                'current_page' => $orders->currentPage(),
                'last_page' => $orders->lastPage(),
                'per_page' => $orders->perPage(),
                'total' => $orders->total(),
                'from' => $orders->firstItem(),
                'to' => $orders->lastItem(),
            ],
            'filters' => [
                'status' => $status,
                'q' => $q,
            ],
            'counts' => [
                'all' => Order::query()->count(),
                'pending' => Order::query()->where('status', 'pending')->count(),
                'paid' => Order::query()->where('status', 'paid')->count(),
                'shipped' => Order::query()->where('status', 'shipped')->count(),
                'cancelled' => Order::query()->where('status', 'cancelled')->count(),
            ],
            'recent' => $recent,
            'paid_revenue' => (float) Order::query()
                ->whereIn('status', ['paid', 'shipped'])
                ->sum('total_amount'),
        ]);
    }

    // vieno uzsakymo perziura adminui komentaro pradzia
    // Cia admin mato viena uzsakyma su prekemis ir mokejimu.
    // Tai reikalinga uzsakymo detaliam tikrinimui.
    // vieno uzsakymo perziura adminui komentaro pabaiga
    public function show($id)
    {
        $order = Order::query()
            ->with([
                'items.product:id,name,slug,price,image',
                'payment:id,order_id,provider,status,transaction_id,amount,paid_at,meta',
            ])
            ->findOrFail($id);

        $items = $order->items->map(function ($item) {
            return [
                'id' => (int) $item->id,
                'product_id' => (int) $item->product_id,
                'name' => $item->product?->name,
                'slug' => $item->product?->slug,
                'image' => $item->product?->image,
                'unit_price' => (float) $item->unit_price,
                'quantity' => (int) $item->quantity,
                'line_total' => (float) $item->line_total,
            ];
        })->values();

        return response()->json([
            'data' => [
                'order' => [
                    'id' => (int) $order->id,
                    'status' => $order->status,
                    'total_amount' => (float) $order->total_amount,
                    'created_at' => optional($order->created_at)->toISOString(),
                    'paid_at' => optional($order->paid_at)->toISOString(),
                    'customer' => [
                        'name' => $order->customer_name,
                        'email' => $order->customer_email,
                        'phone' => $order->customer_phone,
                    ],
                    'shipping' => [
                        'address' => $order->shipping_address,
                        'city' => $order->shipping_city,
                        'postcode' => $order->shipping_postcode,
                        'country' => $order->shipping_country,
                    ],
                    'payment' => [
                        'provider' => $order->payment?->provider,
                        'status' => $order->payment?->status,
                        'transaction_id' => $order->payment?->transaction_id,
                        'paid_at' => optional($order->payment?->paid_at)->toISOString(),
                        'meta' => $order->payment?->meta,
                    ],
                ],
                'items' => $items,
            ],
        ]);
    }

    // admin užsakymo statuso keitimas komentaro pradzia
    // Čia administratorius gali pakeisti statusą, bet tik pagal leidžiamą logiką.
    // uzsakymo statuso keitimas komentaro pradzia
    // Cia admin pakeicia uzsakymo arba mokejimo busena.
    // Pvz pending gali tapti confirmed arba cancelled.
    // uzsakymo statuso keitimas komentaro pabaiga
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => ['required', 'in:pending,paid,shipped,cancelled'],
        ], [
            'status.required' => 'Pasirinkite statusą.',
            'status.in' => 'Neteisingas statusas.',
        ]);

        $order = Order::query()
            ->with(['items', 'payment'])
            ->findOrFail($id);

        $oldStatus = (string) $order->status;
        $newStatus = (string) $request->string('status');

        if ($newStatus === $oldStatus) {
            return response()->json([
                'message' => 'Statusas nepakeistas.',
                'data' => [
                    'id' => (int) $order->id,
                    'status' => $order->status,
                ],
            ]);
        }

        // Šita vieta neleidžia blogų perėjimų, pvz. iš pending tiesiai į shipped.
        if (! $this->isAllowedTransition($oldStatus, $newStatus)) {
            return response()->json([
                'message' => $this->transitionErrorMessage($oldStatus, $newStatus),
            ], 422);
        }

        // statuso keitimas transakcijoje komentaro pradzia
        // Cia statusas keiciamas transakcijoje.
        // Jei uzsakymas atsaukiamas, tuo paciu gali buti grazinami prekiu likuciai.
        // statuso keitimas transakcijoje komentaro pabaiga
        DB::transaction(function () use ($order, $oldStatus, $newStatus) {
            $order->load(['items', 'payment']);

            // Kai admin patvirtina apmokėjimą rankiniu būdu, atnaujinamas ir payment įrašas.
            if ($oldStatus === 'pending' && $newStatus === 'paid') {
                if ($order->payment) {
                    $order->payment->status = 'paid';
                    $order->payment->provider = $order->payment->provider ?: 'manual';
                    $order->payment->transaction_id = $order->payment->transaction_id
                        ?: ('MANUAL-' . $order->id . '-' . time());
                    $order->payment->paid_at = $order->payment->paid_at ?: now();
                    $order->payment->meta = array_merge($order->payment->meta ?? [], [
                        'note' => 'Apmokėjimas patvirtintas administratoriaus.',
                    ]);
                    $order->payment->save();
                }

                $order->paid_at = $order->paid_at ?: now();
            }

            if ($oldStatus === 'pending' && $newStatus === 'cancelled') {
                // Atšaukus užsakymą, rezervuotos prekės grąžinamos į sandėlį.
                $this->restoreStockForOrder($order);

                if ($order->payment && $order->payment->status === 'unpaid') {
                    $order->payment->meta = array_merge($order->payment->meta ?? [], [
                        'note' => 'Užsakymas atšauktas administratoriaus. Sandėlio rezervacija panaikinta.',
                    ]);
                    $order->payment->save();
                }
            }

            if (in_array($oldStatus, ['paid', 'shipped'], true) && $newStatus === 'cancelled') {
                $this->restoreStockForOrder($order);

                if ($order->payment && $order->payment->status === 'paid') {
                    $order->payment->status = 'refunded';
                    $order->payment->meta = array_merge($order->payment->meta ?? [], [
                        'note' => 'Užsakymas atšauktas administratoriaus. Grąžinimas tvarkomas rankiniu būdu.',
                    ]);
                    $order->payment->save();
                }
            }

            $order->status = $newStatus;
            $order->save();
        });

        $order->refresh();

        return response()->json([
            'message' => 'Statusas atnaujintas.',
            'data' => [
                'id' => (int) $order->id,
                'status' => $order->status,
            ],
        ]);
    }

    // uzsakymo trynimas adminui komentaro pradzia
    // Cia admin gali istrinti uzsakyma.
    // Jei reikia, trynimo metu sutvarkomi ir susije irasai.
    // uzsakymo trynimas adminui komentaro pabaiga
    public function destroy($id)
    {
        $order = Order::query()->with('items')->findOrFail($id);

        if (! in_array($order->status, ['pending', 'cancelled'], true)) {
            return response()->json([
                'message' => 'Šio užsakymo ištrinti negalima. Palik istorijai.',
            ], 409);
        }

        if ($order->status === 'pending') {
            DB::transaction(function () use ($order) {
                $this->restoreStockForOrder($order);

                foreach ($order->items as $item) {
                    $item->delete();
                }

                $order->delete();
            });
        } else {
            foreach ($order->items as $item) {
                $item->delete();
            }

            $order->delete();
        }

        return response()->json([
            'message' => 'Užsakymas ištrintas.',
        ]);
    }

    // admin užsakymo statuso keitimas komentaro pabaiga

    // ar galima pereiti i nauja statusa komentaro pradzia
    // Cia tikrinama statusu logika.
    // Ne visi statusu pakeitimai leidziami, kad uzsakymo eiga butu tvarkinga.
    // ar galima pereiti i nauja statusa komentaro pabaiga
    private function isAllowedTransition(string $oldStatus, string $newStatus): bool
    {
        // Čia aiškiai aprašyta statusų schema.
        $allowed = [
            'pending' => ['paid', 'cancelled'],
            'paid' => ['shipped', 'cancelled'],
            'shipped' => ['cancelled'],
            'cancelled' => [],
        ];

        return in_array($newStatus, $allowed[$oldStatus] ?? [], true);
    }

    private function transitionErrorMessage(string $oldStatus, string $newStatus): string
    {
        if ($oldStatus === 'pending' && $newStatus === 'shipped') {
            return 'Į „shipped“ galima keisti tik iš „paid“.';
        }

        if ($oldStatus === 'paid' && $newStatus === 'pending') {
            return 'Atgal į „pending“ grąžinti negalima.';
        }

        if ($oldStatus === 'shipped' && in_array($newStatus, ['pending', 'paid'], true)) {
            return 'Iš „shipped“ galima tik į „cancelled“.';
        }

        if ($oldStatus === 'cancelled') {
            return 'Atšauktas užsakymas nebegali būti grąžintas į aktyvią būseną.';
        }

        return 'Toks statuso perėjimas neleidžiamas.';
    }

    // sandėlio likučio grąžinimas komentaro pradzia
    // prekiu likuciu grazinimas komentaro pradzia
    // Cia grazinami prekiu likuciai, jei uzsakymas atsaukiamas.
    // Tai svarbu, nes prekes buvo nuskaiciuotos kuriant uzsakyma.
    // prekiu likuciu grazinimas komentaro pabaiga
    private function restoreStockForOrder(Order $order): void
    {
        $ids = $order->items
            ->pluck('product_id')
            ->map(fn ($value) => (int) $value)
            ->values()
            ->all();

        $products = Product::query()
            ->whereIn('id', $ids)
            ->lockForUpdate()
            ->get()
            ->keyBy('id');

        foreach ($order->items as $item) {
            $product = $products->get((int) $item->product_id);

            if (! $product) {
                continue;
            }

            // Prie produkto grąžinamas toks kiekis, koks buvo užsakyme.
            $product->stock = (int) $product->stock + (int) $item->quantity;
            $product->save();
        }
    }
    // sandėlio likučio grąžinimas komentaro pabaiga
}