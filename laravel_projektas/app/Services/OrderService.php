<?php

namespace App\Services;

use App\Mail\NewOrderAdminMail;
use App\Mail\NewOrderCustomerMail;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

class OrderService
{
    public function __construct(
        private readonly CartService $cart
    ) {}

    // KODO PRADŽIA: užsakymo kūrimo verslo logika
    // Čia yra pagrindinis orderio sukūrimas iš krepšelio.
    public function createFromCart(array $customer, ?string $paymentMethod = null): Order
    {
        if (!Auth::check()) {
            throw ValidationException::withMessages([
                'auth' => ['Reikia prisijungti.'],
            ]);
        }

        $cart = $this->cart->get();

        if (empty($cart)) {
            throw ValidationException::withMessages([
                'cart' => ['Krepšelis tuščias.'],
            ]);
        }

        $paymentMethod = $paymentMethod ?: 'cash_on_delivery';

        // Transakcija naudojama todėl, kad užsakymas, prekės ir mokėjimas turi būti sukurti kartu.
        $order = DB::transaction(function () use ($cart, $customer, $paymentMethod) {
            $standardItems = collect($cart)
                ->filter(fn ($item) => ($item['type'] ?? 'product') !== 'custom_tub')
                ->values();

            $customItems = collect($cart)
                ->filter(fn ($item) => ($item['type'] ?? 'product') === 'custom_tub')
                ->values();

            $products = collect();

            if ($standardItems->isNotEmpty()) {
                $ids = $standardItems
                    ->pluck('id')
                    ->map(fn ($value) => (int) $value)
                    ->values()
                    ->all();

                // lockForUpdate apsaugo, kad du žmonės tuo pačiu metu nenupirktų paskutinio likučio.
                $products = Product::query()
                    ->whereIn('id', $ids)
                    ->lockForUpdate()
                    ->get()
                    ->keyBy('id');

                foreach ($standardItems as $item) {
                    $productId = (int) ($item['id'] ?? 0);
                    $quantity = (int) ($item['qty'] ?? 1);

                    if (!$productId || $quantity < 1) {
                        throw ValidationException::withMessages([
                            'cart' => ['Blogas krepšelio formatas.'],
                        ]);
                    }

                    $product = $products->get($productId);

                    if (!$product) {
                        throw ValidationException::withMessages([
                            'cart' => ["Prekė (ID: {$productId}) neberasta."],
                        ]);
                    }

                    if ((bool) $product->is_active !== true) {
                        throw ValidationException::withMessages([
                            'cart' => ["Prekė „{$product->name}“ šiuo metu neaktyvi."],
                        ]);
                    }

                    if ((int) $product->stock < $quantity) {
                        throw ValidationException::withMessages([
                            'cart' => ["Prekės „{$product->name}“ sandėlyje nepakanka. Liko: {$product->stock}."],
                        ]);
                    }
                }
            }

            $customBaseProduct = null;

            // Individualus kubilas neturi atskiro produkto kiekvienai kombinacijai, todėl pririšamas prie bazinės kubilo prekės.
            if ($customItems->isNotEmpty()) {
                $customBaseProduct = $this->resolveCustomTubBaseProduct();

                if (! $customBaseProduct) {
                    throw ValidationException::withMessages([
                        'cart' => ['Custom kubilo užsakymui nerasta bazinė aktyvi kubilo prekė.'],
                    ]);
                }
            }

            // Pirmiausia sukuriamas pats užsakymas su kliento ir pristatymo duomenimis.
            $order = Order::create([
                'user_id' => Auth::id(),
                'customer_name' => $customer['customer_name'],
                'customer_email' => $customer['customer_email'],
                'customer_phone' => $customer['customer_phone'] ?? null,
                'shipping_address' => $customer['shipping_address'],
                'shipping_city' => $customer['shipping_city'] ?? null,
                'shipping_postcode' => $customer['shipping_postcode'] ?? null,
                'shipping_country' => $customer['shipping_country'] ?? 'Lietuva',
                'total_amount' => 0,
                'status' => 'pending',
            ]);

            $total = 0;

            foreach ($cart as $item) {
                $type = (string) ($item['type'] ?? 'product');
                $quantity = (int) ($item['qty'] ?? 1);

                if ($quantity < 1) {
                    $quantity = 1;
                }

                // Jei krepšelyje yra individualus kubilas, jo kaina jau ateina iš CartController apskaičiuotos logikos.
                if ($type === 'custom_tub') {
                    $unitPrice = (float) ($item['price'] ?? 0);
                    $lineTotal = $unitPrice * $quantity;
                    $total += $lineTotal;

                    OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $customBaseProduct->id,
                        'quantity' => $quantity,
                        'unit_price' => $unitPrice,
                        'line_total' => $lineTotal,
                    ]);

                    continue;
                }

                $productId = (int) ($item['id'] ?? 0);
                $product = $products->get($productId);

                if (! $product) {
                    throw ValidationException::withMessages([
                        'cart' => ['Nepavyko apdoroti vienos iš prekių.'],
                    ]);
                }

                $unitPrice = (float) $product->price;
                $lineTotal = $unitPrice * $quantity;
                $total += $lineTotal;

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'line_total' => $lineTotal,
                ]);

                // Po užsakymo prekės likutis sumažinamas.
                $product->stock = (int) $product->stock - $quantity;
                $product->save();
            }

            $order->total_amount = $total;
            $order->save();

            // Kiekvienam užsakymui iš karto sukuriamas mokėjimo įrašas.
            Payment::create([
                'order_id' => $order->id,
                'provider' => $paymentMethod === 'paysera' ? 'paysera' : 'manual',
                'status' => 'unpaid',
                'amount' => $total,
                'meta' => [
                    'payment_method' => $paymentMethod,
                    'requested_method' => $paymentMethod,
                    'created_from_checkout' => true,
                    'cart_snapshot' => $this->buildCartSnapshot($cart),
                ],
            ]);

            return $order->fresh(['items.product', 'payment']);
        });

        // Po sėkmingo užsakymo siunčiami laiškai klientui ir administratoriui.
        $this->sendEmails($order);

        return $order;
    }

    // KODO PABAIGA: užsakymo kūrimo verslo logika

    private function resolveCustomTubBaseProduct(): ?Product
    {
        $product = Product::query()
            ->where('is_active', true)
            ->whereHas('category', function ($query) {
                $query->where('slug', 'kubilai');
            })
            ->orderBy('id')
            ->first();

        if ($product) {
            return $product;
        }

        return Product::query()
            ->where('is_active', true)
            ->where(function ($query) {
                $query->where('name', 'like', '%kubil%')
                    ->orWhere('slug', 'like', '%kubil%');
            })
            ->orderBy('id')
            ->first();
    }

    private function buildCartSnapshot(array $cart): array
    {
        return collect($cart)
            ->values()
            ->map(function ($item) {
                $qty = (int) ($item['qty'] ?? 1);
                $price = (float) ($item['price'] ?? 0);

                return [
                    'type' => (string) ($item['type'] ?? 'product'),
                    'name' => (string) ($item['name'] ?? 'Prekė'),
                    'subtitle' => (string) ($item['subtitle'] ?? ''),
                    'slug' => (string) ($item['slug'] ?? ''),
                    'image' => $item['image'] ?? null,
                    'qty' => $qty,
                    'price' => $price,
                    'subtotal' => (float) ($price * $qty),
                    'meta' => $item['meta'] ?? null,
                ];
            })
            ->all();
    }

    private function sendEmails(Order $order): void
    {
        try {
            if (!empty($order->customer_email)) {
                Mail::to($order->customer_email)->send(new NewOrderCustomerMail($order));
            }
        } catch (\Throwable $e) {
            Log::error('Nepavyko išsiųsti kliento užsakymo laiško', [
                'order_id' => $order->id,
                'message' => $e->getMessage(),
            ]);
        }

        try {
            $adminEmail = config('mail.from.address');

            if (!empty($adminEmail)) {
                Mail::to($adminEmail)->send(new NewOrderAdminMail($order));
            }
        } catch (\Throwable $e) {
            Log::error('Nepavyko išsiųsti admin užsakymo laiško', [
                'order_id' => $order->id,
                'message' => $e->getMessage(),
            ]);
        }
    }
}