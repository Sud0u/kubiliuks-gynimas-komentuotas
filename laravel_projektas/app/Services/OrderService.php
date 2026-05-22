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

   
    // Čia yra pagrindinis orderio sukūrimas iš krepšelio.
    // Čia prasideda pagrindinė užsakymo kūrimo logika. Į šitą funkciją ateina patikrinti checkout duomenys iš OrderController, 
    // ir tada iš session krepšelio sukuriamas realus užsakymas duomenų bazėje.
    public function createFromCart(array $customer, ?string $paymentMethod = null): Order
    {
        // prisijungimo patikrinimas komentaro pradzia
        // Cia patikrinama ar vartotojas prisijunges.
        // Uzsakymo kurti neleidziama neprisijungus, nes uzsakymas turi priklausyti vartotojui.
        // prisijungimo patikrinimas komentaro pabaiga
        if (!Auth::check()) {
            throw ValidationException::withMessages([
                'auth' => ['Reikia prisijungti.'],
            ]);
        }

        // krepselio paemimas uzsakymui komentaro pradzia
        // Cia paimamas dabartinis vartotojo krepselis.
        // Is jo veliau bus sukurtas uzsakymas ir uzsakymo prekes.
        // krepselio paemimas uzsakymui komentaro pabaiga
        $cart = $this->cart->get();

        // tuscio krepselio saugiklis komentaro pradzia
        // Jei krepselis tuscias, uzsakymas nesukuriamas.
        // Taip apsaugoma, kad duomenu bazeje neatsirastu tusciu uzsakymu.
        // tuscio krepselio saugiklis komentaro pabaiga
        if (empty($cart)) {
            throw ValidationException::withMessages([
                'cart' => ['Krepšelis tuščias.'],
            ]);
        }

        $paymentMethod = $paymentMethod ?: 'cash_on_delivery';

        // Transakcija naudojama todėl, kad užsakymas, prekės ir mokėjimas turi būti sukurti kartu.
        $order = DB::transaction(function () use ($cart, $customer, $paymentMethod) {
            // paprastu ir custom prekiu atskyrimas komentaro pradzia
            // Cia krepselis padalinamas i dvi dalis.
            // standardItems yra paprastos katalogo prekes, o customItems yra individualus kubilas.
            // paprastu ir custom prekiu atskyrimas komentaro pabaiga
            $standardItems = collect($cart)
                ->filter(fn ($item) => ($item['type'] ?? 'product') !== 'custom_tub')
                ->values();

            $customItems = collect($cart)
                ->filter(fn ($item) => ($item['type'] ?? 'product') === 'custom_tub')
                ->values();

            $products = collect();

            if ($standardItems->isNotEmpty()) {
                // produktu id surinkimas komentaro pradzia
                // Cia surenkami visu paprastu prekiu id.
                // Pagal juos veliau is duomenu bazes paimami produktai ir tikrinami likuciai.
                // produktu id surinkimas komentaro pabaiga
                $ids = $standardItems
                    ->pluck('id')
                    ->map(fn ($value) => (int) $value)
                    ->values()
                    ->all();

                // lockForUpdate apsaugo, kad du žmonės tuo pačiu metu nenupirktų paskutinio likučio.
                // produktu uzrakinimas komentaro pradzia
                // Cia produktai paimami is DB ir naudojamas lockForUpdate.
                // Tai apsaugo nuo situacijos kai du zmones tuo paciu metu bando nupirkti paskutini likuti.
                // produktu uzrakinimas komentaro pabaiga
                $products = Product::query()
                    ->whereIn('id', $ids)
                    ->lockForUpdate()
                    ->get()
                    ->keyBy('id');

                // kiekvienos prekes tikrinimas komentaro pradzia
                // Cia pereinama per kiekviena paprasta preke is krepselio.
                // Tikrinama ar preke egzistuoja, ar aktyvi ir ar pakanka likucio sandelyje.
                // kiekvienos prekes tikrinimas komentaro pabaiga
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
            // custom kubilo bazines prekes patikrinimas komentaro pradzia
            // Individualus kubilas nera atskira DB preke kiekvienai spalvai.
            // Todel uzsakymo prekei jis pririsamas prie bazines aktyvios kubilo prekes.
            // custom kubilo bazines prekes patikrinimas komentaro pabaiga
            if ($customItems->isNotEmpty()) {
                $customBaseProduct = $this->resolveCustomTubBaseProduct();

                if (! $customBaseProduct) {
                    throw ValidationException::withMessages([
                        'cart' => ['Custom kubilo užsakymui nerasta bazinė aktyvi kubilo prekė.'],
                    ]);
                }
            }

            // Pirmiausia sukuriamas pats užsakymas su kliento ir pristatymo duomenimis.
            // order iraso sukurimas komentaro pradzia
            // Cia sukuriamas pats uzsakymas duomenu bazeje.
            // Irasomi kliento duomenys, adresas, user_id ir pradinis statusas pending.
            // Total amount pradzioje yra 0, nes suma bus suskaiciuota zemiau pagal prekes.
            // order iraso sukurimas komentaro pabaiga
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

            // krepselio perkelimas i uzsakymo prekes komentaro pradzia
            // Cia pereinama per visas krepselio prekes.
            // Kiekvienai prekei apskaiciuojama eilutes suma ir sukuriamas OrderItem irasas.
            // krepselio perkelimas i uzsakymo prekes komentaro pabaiga
            foreach ($cart as $item) {
                // prekes tipas ir kiekis uzsakyme komentaro pradzia
                // Cia is krepselio elemento paimamas tipas ir kiekis.
                // Tipas gali buti product arba custom_tub, todel zemiau logika issiskiria.
                // Jei kiekis blogas, jis pataisomas i 1.
                // prekes tipas ir kiekis uzsakyme komentaro pabaiga
                $type = (string) ($item['type'] ?? 'product');
                $quantity = (int) ($item['qty'] ?? 1);

                if ($quantity < 1) {
                    $quantity = 1;
                }

                // Jei krepšelyje yra individualus kubilas, jo kaina jau ateina iš CartController apskaičiuotos logikos.
                // individualaus kubilo uzsakymo eilute komentaro pradzia
                // Jei tipas yra custom_tub, sistema naudoja kaina is krepselio.
                // Ji jau buvo apskaiciuota backend puseje, kai kubilas buvo dedamas i krepseli.
                // Tada sukuriamas OrderItem ir pereinama prie kitos prekes.
                // individualaus kubilo uzsakymo eilute komentaro pabaiga
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

                // paprastos prekes kaina ir suma komentaro pradzia
                // Cia paprastos prekes kaina imama is duomenu bazes, ne is narsykles.
                // Tada kaina dauginama is kiekio ir pridedama prie bendros uzsakymo sumos.
                // paprastos prekes kaina ir suma komentaro pabaiga
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
                // likucio sumazinimas komentaro pradzia
                // Kai uzsakymas sukuriamas, paprastos prekes likutis sumazinamas pagal kieki.
                // Taip sandelyje lieka teisingas prekiu skaicius.
                // likucio sumazinimas komentaro pabaiga
                $product->stock = (int) $product->stock - $quantity;
                $product->save();
            }

            // galutines sumos irasymas komentaro pradzia
            // Po visu prekiu apdorojimo i order irasoma galutine suma.
            // Iki tol ji buvo 0, nes suma buvo skaiciuojama foreach cikle.
            // galutines sumos irasymas komentaro pabaiga
            $order->total_amount = $total;
            $order->save();

            // Kiekvienam užsakymui iš karto sukuriamas mokėjimo įrašas.
            // payment iraso sukurimas komentaro pradzia
            // Kiekvienam uzsakymui sukuriamas mokejimo irasas.
            // Provider gali buti paysera arba manual, o statusas pradzioje unpaid.
            // Meta dalyje issaugomas payment_method ir krepselio snapshot.
            // payment iraso sukurimas komentaro pabaiga
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

    // užsakymo kūrimo verslo logika komentaro pabaiga

    // bazines kubilo prekes paieska komentaro pradzia
    // Cia ieskoma aktyvi kubilo preke, prie kurios galima priristi individualu kubila uzsakyme.
    // Pirma bandoma ieskoti pagal kategorija kubilai, o jei nepavyksta, pagal pavadinima arba slug.
    // bazines kubilo prekes paieska komentaro pabaiga
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

    // krepselio snapshot komentaro pradzia
    // Cia sukuriama krepselio kopija, kuri issaugoma payment meta dalyje.
    // Tai naudinga, nes veliau galima matyti, kas buvo krepselyje uzsakymo metu.
    // krepselio snapshot komentaro pabaiga
    private function buildCartSnapshot(array $cart): array
    {
        return collect($cart)
            ->values()
            ->map(function ($item) {
                // snapshot kiekis ir kaina komentaro pradzia
                // Cia is krepselio elemento paimamas kiekis ir kaina.
                // Jei kiekio nera, naudojamas 1, jei kainos nera, naudojamas 0.
                // Veliau is siu duomenu skaiciuojamas subtotal.
                // snapshot kiekis ir kaina komentaro pabaiga
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

    // uzsakymo laisku siuntimas komentaro pradzia
    // Po sekmingo uzsakymo sistema bando issiusti laiska klientui ir administratoriui.
    // Jei laiskas nepavyksta, uzsakymas vis tiek lieka sukurtas, o klaida irasoma i logus.
    // uzsakymo laisku siuntimas komentaro pabaiga
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