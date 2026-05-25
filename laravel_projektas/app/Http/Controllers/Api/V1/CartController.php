<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\CartService;
use Illuminate\Http\Request;

class CartController extends Controller
{
    private function currentQtyInCart(CartService $cartService, int $productId): int
    {
        $cart = $cartService->get();
        $it = $cart[$productId] ?? null;

        return (int) ($it['qty'] ?? 0);
    }

    public function show(Request $request, CartService $cartService)
    {
        $cart = $cartService->get();

        $items = collect($cart)
            ->values()
            ->map(function ($it) {
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

    // cia priimama paprastos prekes idejimo i krepseli API uzklausa.
    public function addItem(Request $request, CartService $cartService)
    {
        $validated = $request->validate([
            'product_id' => ['required', 'integer', 'min:1'],
            'qty' => ['nullable', 'integer', 'min:1', 'max:999'],
        ]);

        $productId = (int) $validated['product_id'];
        $qty = (int) ($validated['qty'] ?? 1);

        // pagal product_id pasiimama aktyvi preke is products lenteles.
        $product = Product::query()
            ->where('id', $productId)
            ->where('is_active', 1)
            ->first();

        if (! $product) {
            return response()->json([
                'message' => 'Prekė nerasta.',
            ], 404);
        }

        if ((int) ($product->stock ?? 0) <= 0) {
            return response()->json([
                'message' => 'Prekės nėra sandėlyje.',
            ], 422);
        }

        // cia patikrinamas likutis, kad neleistu ideti daugiau negu yra sandelyje.
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

        // po patikrinimu preke perduodama i CartService ir issaugoma session krepselyje.
        $cartService->add($product, $qty);

        return response()->json([
            'message' => 'Prekė įdėta į krepšelį.',
        ], 201);
    }

    // cia priimamas individualaus kubilo idejimas i krepseli.
    public function addCustomTub(Request $request, CartService $cartService)
    {

        // serveris leidzia tik is anksto numatytus dydzius ir spalvas.
        $validated = $request->validate([
            'size_key' => ['required', 'in:180,200,220'],
            'inside_key' => ['required', 'in:balta,melyna,raudona,zalia'],
            'wood_key' => ['required', 'in:base-ruda,chestnut-ruda,darkred-ruda,deepcrimson-ruda'],
            'qty' => ['nullable', 'integer', 'min:1', 'max:99'],
        ]);
        $sizeMap = [
            '180' => ['label' => '180 cm', 'price' => 0],
            '200' => ['label' => '200 cm', 'price' => 300],
            '220' => ['label' => '220 cm', 'price' => 650],
        ];
        $insideMap = [
            'balta' => ['label' => 'Balta', 'price' => 0],
            'melyna' => ['label' => 'Mėlyna', 'price' => 90],
            'raudona' => ['label' => 'Raudona', 'price' => 90],
            'zalia' => ['label' => 'Žalia', 'price' => 90],
        ];
        $woodMap = [
            'base-ruda' => ['label' => 'Šviesi ruda', 'price' => 0],
            'chestnut-ruda' => ['label' => 'Kaštoninė', 'price' => 120],
            'darkred-ruda' => ['label' => 'Rudai raudona', 'price' => 160],
            'deepcrimson-ruda' => ['label' => 'Bordo', 'price' => 210],
        ];

        $sizeKey = (string) $validated['size_key'];
        $insideKey = (string) $validated['inside_key'];
        $woodKey = (string) $validated['wood_key'];
        $qty = (int) ($validated['qty'] ?? 1);

        // kaina perskaiciuojama backende, kad vartotojas negaletu jos pasikeisti narsykleje.
        $basePrice = 2200;
        $totalPrice = $basePrice
            + (int) $sizeMap[$sizeKey]['price']
            + (int) $insideMap[$insideKey]['price']
            + (int) $woodMap[$woodKey]['price'];

        // patikrinti pasirinkimai perduodami i CartService kaip custom_tub irasas.
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

    public function updateItem(Request $request, $id, CartService $cartService)
    {
        $validated = $request->validate([
            'qty' => ['required', 'integer', 'min:1', 'max:999'],
        ]);

        $qty = (int) $validated['qty'];
        $itemId = (string) $id;

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

    public function clear(Request $request, CartService $cartService)
    {
        $cartService->clear();

        return response()->json([
            'message' => 'Krepšelis išvalytas.',
        ], 200);
    }

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