<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\CartService;
use Illuminate\Http\Request;

class CartController extends Controller
{
    // kiekio patikrinimas krepselyje komentaro pradzia
    // Cia pagal produkto id patikrinama kiek tos prekes jau yra krepselyje.
    // To reikia, kad vartotojas negaletu isideti daugiau negu yra sandelyje.
    // kiekio patikrinimas krepselyje komentaro pabaiga
    private function currentQtyInCart(CartService $cartService, int $productId): int
    {
        $cart = $cartService->get();
        $it = $cart[$productId] ?? null;

        return (int) ($it['qty'] ?? 0);
    }

    // krepselio parodymas komentaro pradzia
    // Sitas metodas grazina visa krepseli JavaScript pusei.
    // Is sesijos paimamos prekes, tada jos paruosiamos grazinimui JSON formatu.
    // krepselio parodymas komentaro pabaiga
    public function show(Request $request, CartService $cartService)
    {
        $cart = $cartService->get();

        $items = collect($cart)
            ->values()
            ->map(function ($it) {
                // krepselio elemento duomenys komentaro pradzia
                // Cia is vienos krepselio prekes paimamas kiekis, kaina ir tipas.
                // Jeigu kiekio nera, sistema naudoja 1. Jeigu kainos nera, naudoja 0.
                // Tipas pagal nutylejima yra product, bet individualiam kubilui gali buti custom_tub.
                // Taip sistema supranta ar cia paprasta preke ar sukonfiguruotas kubilas.
                // krepselio elemento duomenys komentaro pabaiga
                $qty = (int) ($it['qty'] ?? 1);
                $price = (float) ($it['price'] ?? 0);
                $type = (string) ($it['type'] ?? 'product');

                return [
                    'id' => $it['cart_key'] ?? ($it['id'] ?? null),
                    'cart_key' => $it['cart_key'] ?? ($it['id'] ?? null),
                    'type' => $type,
                    'product_id' => (int) ($it['id'] ?? 0),
                    'slug' => (string) ($it['slug'] ?? ''),
                    'name' => (string) ($it['name'] ?? ''),
                    'subtitle' => (string) ($it['subtitle'] ?? ''),
                    'price' => $price,
                    'qty' => $qty,
                    'image' => $it['image'] ?? null,
                    'image_url' => $this->publicImageUrl($it['image'] ?? null),
                    'subtotal' => (float) ($price * $qty),
                    'meta' => $it['meta'] ?? null,
                ];
            })
            ->all();

        $count = collect($items)->sum('qty');
        $total = collect($items)->sum('subtotal');

        return response()->json([
            'data' => [
                'items' => $items,
                'count' => (int) $count,
                'total' => (float) $total,
            ],
        ]);
    }

    // paprastos prekes idejimas komentaro pradzia
    // Sitas metodas veikia kai vartotojas spaudzia ideti paprasta preke i krepseli.
    // Pirmiausia patikrinami duomenys, tada tikrinama ar preke aktyvi ir ar yra likutis.
    // paprastos prekes idejimas komentaro pabaiga
    public function addItem(Request $request, CartService $cartService)
    {
        // addItem validacija komentaro pradzia
        // Cia Laravel patikrina ar atejo produkto id ir kiekis.
        // Tai apsauga, kad i krepseli nepatektu blogi arba tusci duomenys.
        // addItem validacija komentaro pabaiga
        $validated = $request->validate([
            'product_id' => ['required', 'integer', 'min:1'],
            'qty' => ['nullable', 'integer', 'min:1', 'max:999'],
        ]);

        $productId = (int) $validated['product_id'];
        $qty = (int) ($validated['qty'] ?? 1);

        // produkto paieska komentaro pradzia
        // Cia is duomenu bazes ieskoma aktyvi preke pagal id.
        // Jei preke neaktyvi arba nerasta, sistema neleis jos ideti i krepseli.
        // produkto paieska komentaro pabaiga
        $product = Product::query()
            ->where('id', $productId)
            ->where('is_active', 1)
            ->first();

        if (! $product) {
            return response()->json([
                'message' => 'Prekė nerasta.',
            ], 404);
        }

        // likucio patikrinimas komentaro pradzia
        // Cia tikrinama ar prekes kiekis sandelyje didesnis uz 0.
        // Jei likucio nera, vartotojui grazinama lietuviska klaida.
        // likucio patikrinimas komentaro pabaiga
        if ((int) ($product->stock ?? 0) <= 0) {
            return response()->json([
                'message' => 'Prekės nėra sandėlyje.',
            ], 422);
        }

        $stock = (int) ($product->stock ?? 0);
        $currentQty = $this->currentQtyInCart($cartService, $productId);

        if ($currentQty >= $stock) {
            return response()->json([
                'message' => 'Pasiektas maksimalus kiekis krepšelyje (' . $stock . ' vnt.).',
            ], 422);
        }

        $availableToAdd = $stock - $currentQty;

        if ($qty > $availableToAdd) {
            return response()->json([
                'message' => 'Sandėlyje liko ' . $availableToAdd . ' vnt.',
            ], 422);
        }

        // perdavimas i CartService komentaro pradzia
        // Kai visi patikrinimai praeina, preke perduodama i CartService.
        // Controlleris tik priima uzklausa, o Service realiai atnaujina krepseli.
        // perdavimas i CartService komentaro pabaiga
        $cartService->add($product, $qty);

        return response()->json([
            'message' => 'Prekė įdėta į krepšelį.',
        ], 201);
    }

    // individualaus kubilo įdėjimas į krepšelį komentaro pradzia
    // Čia backend pusėje patikrinama, ar iš frontend atėjo tik leistini pasirinkimai.
    public function addCustomTub(Request $request, CartService $cartService)
    {
        // custom kubilo validacija komentaro pradzia
        // Cia tikrinama ar is frontend atejo tik leistini pasirinkimai.
        // Dydis gali buti tik 180, 200 arba 220, o spalvos tik is mano sarasu.
        // Tai svarbu, nes vien JavaScript puse pasitiketi negalima.
        // custom kubilo validacija komentaro pabaiga
        $validated = $request->validate([
            'size_key' => ['required', 'in:180,200,220'],
            'inside_key' => ['required', 'in:balta,melyna,raudona,zalia'],
            'wood_key' => ['required', 'in:base-ruda,chestnut-ruda,darkred-ruda,deepcrimson-ruda'],
            'qty' => ['nullable', 'integer', 'min:1', 'max:99'],
        ]);

        // dydziu zemelapis backend puseje komentaro pradzia
        // Cia backend puseje dar karta aprasomi dydziai ir ju kainos.
        // Taip kaina nera imama tik is narsykles, o saugiai perskaiciuojama serveryje.
        // dydziu zemelapis backend puseje komentaro pabaiga
        $sizeMap = [
            '180' => ['label' => '180 cm', 'price' => 0],
            '200' => ['label' => '200 cm', 'price' => 300],
            '220' => ['label' => '220 cm', 'price' => 650],
        ];

        // vidaus spalvu zemelapis backend puseje komentaro pradzia
        // Cia serveris zino kiek kainuoja kiekviena vidaus spalva.
        // Jei vartotojas bandytu pakeisti kaina per inspect, cia ji vistiek bus perskaiciuota teisingai.
        // vidaus spalvu zemelapis backend puseje komentaro pabaiga
        $insideMap = [
            'balta' => ['label' => 'Balta', 'price' => 0],
            'melyna' => ['label' => 'Mėlyna', 'price' => 90],
            'raudona' => ['label' => 'Raudona', 'price' => 90],
            'zalia' => ['label' => 'Žalia', 'price' => 90],
        ];

        // medienos zemelapis backend puseje komentaro pradzia
        // Cia aprasomos medienos spalvos ir ju kainos priedai.
        // medienos zemelapis backend puseje komentaro pabaiga
        $woodMap = [
            'base-ruda' => ['label' => 'Šviesi ruda', 'price' => 0],
            'chestnut-ruda' => ['label' => 'Kaštoninė', 'price' => 120],
            'darkred-ruda' => ['label' => 'Rudai raudona', 'price' => 160],
            'deepcrimson-ruda' => ['label' => 'Bordo', 'price' => 210],
        ];

        // validuoti pasirinkimai komentaro pradzia
        // Cia is validacijos paimami konkretus pasirinkimu raktai.
        // Jie veliau naudojami kainai, pavadinimams ir nuotraukos keliui sudaryti.
        // validuoti pasirinkimai komentaro pabaiga
        $sizeKey = (string) $validated['size_key'];
        $insideKey = (string) $validated['inside_key'];
        $woodKey = (string) $validated['wood_key'];
        $qty = (int) ($validated['qty'] ?? 1);

        // Kaina skaičiuojama serveryje, kad žmogus negalėtų pakeisti kainos per naršyklę.
        // serverio kainos skaiciavimas komentaro pradzia
        // Cia kaina skaiciuojama backend puseje, tai yra svarbi saugumo vieta.
        // Vartotojas gali keisti frontend koda, bet galutine kaina vistiek sudaroma serveryje.
        // serverio kainos skaiciavimas komentaro pabaiga
        $basePrice = 2200;
        $totalPrice = $basePrice
            + (int) $sizeMap[$sizeKey]['price']
            + (int) $insideMap[$insideKey]['price']
            + (int) $woodMap[$woodKey]['price'];

        // Į krepšelį saugomi ne tik pavadinimai, bet ir pasirinkimų meta informacija.
        // custom kubilo perdavimas i krepseli komentaro pradzia
        // Cia i CartService perduodama visa custom kubilo informacija.
        // Saugojami raktai, vartotojui rodomi pavadinimai, kaina ir nuotraukos kelias.
        // custom kubilo perdavimas i krepseli komentaro pabaiga
        $cartService->addCustomTub([
            'size_key' => $sizeKey,
            'size_label' => $sizeMap[$sizeKey]['label'],
            'inside_key' => $insideKey,
            'inside_label' => $insideMap[$insideKey]['label'],
            'wood_key' => $woodKey,
            'wood_label' => $woodMap[$woodKey]['label'],
            'price' => $totalPrice,
            'image' => 'images/kubilai/' . $insideKey . '-' . $woodKey . '.png',
        ], $qty);

        return response()->json([
            'message' => 'Individualus kubilas įdėtas į krepšelį.',
        ], 201);
    }
    // individualaus kubilo įdėjimas į krepšelį komentaro pabaiga

    // krepselio kiekio keitimas komentaro pradzia
    // Sitas metodas veikia kai vartotojas keicia prekes kieki krepselyje.
    // Jis atskiria paprasta preke nuo custom_tub ir tada atnaujina tinkamu budu.
    // krepselio kiekio keitimas komentaro pabaiga
    public function updateItem(Request $request, $id, CartService $cartService)
    {
        $validated = $request->validate([
            'qty' => ['required', 'integer', 'min:1', 'max:999'],
        ]);

        $qty = (int) $validated['qty'];
        $itemId = (string) $id;

        // custom kubilo atpazinimas komentaro pradzia
        // Cia tikrinama ar krepselio elementas yra individualus kubilas.
        // Custom kubilo raktas prasideda custom_tub--, todel jis tvarkomas pagal key, ne pagal produkto id.
        // custom kubilo atpazinimas komentaro pabaiga
        if ($cartService->isCustomKey($itemId)) {
            if (! $cartService->hasKey($itemId)) {
                return response()->json([
                    'message' => 'Individualus kubilas krepšelyje nerastas.',
                ], 404);
            }

            $cartService->updateByKey($itemId, $qty);

            return response()->json([
                'message' => 'Krepšelis atnaujintas.',
            ], 200);
        }

        $productId = (int) $id;

        $product = Product::query()
            ->where('id', $productId)
            ->where('is_active', 1)
            ->first();

        if (! $product) {
            return response()->json([
                'message' => 'Prekė nerasta arba nebėra aktyvi.',
            ], 404);
        }

        if ((int) ($product->stock ?? 0) <= 0) {
            return response()->json([
                'message' => 'Prekės nėra sandėlyje.',
            ], 422);
        }

        $stock = (int) ($product->stock ?? 0);

        // Paprastas likučio saugiklis: krepšelyje neleidžiama turėti daugiau, negu yra sandėlyje.
        if ($qty > $stock) {
            return response()->json([
                'message' => 'Sandėlyje yra ' . $stock . ' vnt.',
            ], 422);
        }

        $cartService->update($product, $qty);

        return response()->json([
            'message' => 'Krepšelis atnaujintas.',
        ], 200);
    }

    // prekes salinimas is krepselio komentaro pradzia
    // Sitas metodas pasalina preke is krepselio.
    // Jis irgi moka salinti tiek paprasta preke, tiek individualu kubila.
    // prekes salinimas is krepselio komentaro pabaiga
    public function removeItem(Request $request, $id, CartService $cartService)
    {
        $itemId = (string) $id;

        if ($cartService->isCustomKey($itemId)) {
            if (! $cartService->hasKey($itemId)) {
                return response()->json([
                    'message' => 'Individualus kubilas krepšelyje nerastas.',
                ], 404);
            }

            $cartService->removeByKey($itemId);

            return response()->json([
                'message' => 'Prekė pašalinta iš krepšelio.',
            ], 200);
        }

        $productId = (int) $id;

        if (! $cartService->has($productId)) {
            return response()->json([
                'message' => 'Prekė krepšelyje nerasta.',
            ], 404);
        }

        $cartService->removeById($productId);

        return response()->json([
            'message' => 'Prekė pašalinta iš krepšelio.',
        ], 200);
    }

    // viso krepselio isvalymas komentaro pradzia
    // Cia isvalomas visas krepselis is sesijos.
    // Tai naudojama kai vartotojas pasirenka isvalyti krepseli arba po sekmingo uzsakymo.
    // viso krepselio isvalymas komentaro pabaiga
    public function clear(Request $request, CartService $cartService)
    {
        $cartService->clear();

        return response()->json([
            'message' => 'Krepšelis išvalytas.',
        ], 200);
    }

    // paveikslelio URL paruosimas komentaro pradzia
    // Cia is issaugoto paveikslelio kelio padaromas normalus URL narsyklei.
    // Jei kelias jau yra pilnas su http, jis paliekamas toks pats.
    // paveikslelio URL paruosimas komentaro pabaiga
    private function publicImageUrl(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        $path = trim($path);

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        if (str_starts_with($path, '/')) {
            $path = ltrim($path, '/');
        }

        if (str_starts_with($path, 'images/')) {
            return asset($path);
        }

        if (str_starts_with($path, 'storage/')) {
            return asset($path);
        }

        return asset('storage/' . $path);
    }
}