<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Facades\Session;

class CartService
{
    private string $sessionKey = 'cart';

    public function get(): array
    {
        return Session::get($this->sessionKey, []);
    }

    public function add(Product $product, int $qty = 1): void
    {
        if ($qty < 1) {
            $qty = 1;
        }

        $cart = $this->get();
        $id = (int) $product->id;

        if (isset($cart[$id])) {
            $cart[$id]['qty'] = (int) ($cart[$id]['qty'] ?? 0) + $qty;
            $cart[$id]['slug'] = $cart[$id]['slug'] ?? $product->slug;
            $cart[$id]['name'] = (string) $product->name;
            $cart[$id]['price'] = (float) $product->price;
            $cart[$id]['image'] = $product->image;
            $cart[$id]['type'] = 'product';
        } else {
            $cart[$id] = [
                'id' => $id,
                'cart_key' => (string) $id,
                'type' => 'product',
                'slug' => (string) $product->slug,
                'name' => (string) $product->name,
                'price' => (float) $product->price,
                'image' => $product->image,
                'qty' => (int) $qty,
            ];
        }

        Session::put($this->sessionKey, $cart);
    }

    // KODO PRADŽIA: individualaus kubilo saugojimas sesijos krepšelyje
    // Čia suformuojamas specialus krepšelio įrašas, kuris nėra paprasta prekė iš katalogo.
    public function addCustomTub(array $config, int $qty = 1): string
    {
        if ($qty < 1) {
            $qty = 1;
        }

        $sizeKey = (string) ($config['size_key'] ?? '180');
        $insideKey = (string) ($config['inside_key'] ?? 'balta');
        $woodKey = (string) ($config['wood_key'] ?? 'base-ruda');

        $sizeLabel = (string) ($config['size_label'] ?? '180 cm');
        $insideLabel = (string) ($config['inside_label'] ?? 'Balta');
        $woodLabel = (string) ($config['wood_label'] ?? 'Šviesi ruda');

        $unitPrice = (float) ($config['price'] ?? 0);
        $image = (string) ($config['image'] ?? ('images/kubilai/' . $insideKey . '-' . $woodKey . '.png'));

        // Raktas sudaromas iš pasirinkimų, todėl ta pati komplektacija krepšelyje susijungia į vieną eilutę.
        $key = $this->customTubKey($sizeKey, $insideKey, $woodKey);

        $cart = $this->get();

        if (isset($cart[$key])) {
            $cart[$key]['qty'] = (int) ($cart[$key]['qty'] ?? 0) + $qty;
            $cart[$key]['price'] = $unitPrice;
            $cart[$key]['image'] = $image;
            $cart[$key]['name'] = 'Individualus kubilas';
            $cart[$key]['subtitle'] = $sizeLabel . ' · ' . $insideLabel . ' · ' . $woodLabel;
            // Meta dalyje saugomi pasirinkimai, kad vėliau juos matytume krepšelyje ir užsakyme.
            $cart[$key]['meta'] = [
                'builder_type' => 'custom_tub',
                'size_key' => $sizeKey,
                'size_label' => $sizeLabel,
                'inside_key' => $insideKey,
                'inside_label' => $insideLabel,
                'wood_key' => $woodKey,
                'wood_label' => $woodLabel,
                'production_time' => '6–8 savaitės',
            ];
        } else {
            $cart[$key] = [
                'id' => null,
                'cart_key' => $key,
                'type' => 'custom_tub',
                'slug' => 'susikurk-savo-kubila',
                'name' => 'Individualus kubilas',
                'subtitle' => $sizeLabel . ' · ' . $insideLabel . ' · ' . $woodLabel,
                'price' => $unitPrice,
                'image' => $image,
                'qty' => (int) $qty,
                'meta' => [
                    'builder_type' => 'custom_tub',
                    'size_key' => $sizeKey,
                    'size_label' => $sizeLabel,
                    'inside_key' => $insideKey,
                    'inside_label' => $insideLabel,
                    'wood_key' => $woodKey,
                    'wood_label' => $woodLabel,
                    'production_time' => '6–8 savaitės',
                ],
            ];
        }

        Session::put($this->sessionKey, $cart);

        return $key;
    }
    // KODO PABAIGA: individualaus kubilo saugojimas sesijos krepšelyje

    public function update(Product $product, int $qty): void
    {
        if ($qty < 1) {
            $qty = 1;
        }

        $cart = $this->get();
        $id = (int) $product->id;

        if (! isset($cart[$id])) {
            return;
        }

        $cart[$id]['qty'] = (int) $qty;
        $cart[$id]['slug'] = (string) $product->slug;
        $cart[$id]['name'] = (string) $product->name;
        $cart[$id]['price'] = (float) $product->price;
        $cart[$id]['image'] = $product->image;
        $cart[$id]['type'] = 'product';

        Session::put($this->sessionKey, $cart);
    }

    public function updateByKey(string $key, int $qty): void
    {
        if ($qty < 1) {
            $qty = 1;
        }

        $cart = $this->get();

        if (! isset($cart[$key])) {
            return;
        }

        $cart[$key]['qty'] = (int) $qty;

        Session::put($this->sessionKey, $cart);
    }

    public function remove(Product $product): void
    {
        $this->removeById((int) $product->id);
    }

    public function removeById(int $productId): void
    {
        $cart = $this->get();

        unset($cart[$productId]);

        if (empty($cart)) {
            Session::forget($this->sessionKey);
            return;
        }

        Session::put($this->sessionKey, $cart);
    }

    public function removeByKey(string $key): void
    {
        $cart = $this->get();

        unset($cart[$key]);

        if (empty($cart)) {
            Session::forget($this->sessionKey);
            return;
        }

        Session::put($this->sessionKey, $cart);
    }

    public function has(int $productId): bool
    {
        $cart = $this->get();

        return isset($cart[$productId]);
    }

    public function hasKey(string $key): bool
    {
        $cart = $this->get();

        return isset($cart[$key]);
    }

    public function isCustomKey(string $key): bool
    {
        return str_starts_with($key, 'custom_tub--');
    }

    public function clear(): void
    {
        Session::forget($this->sessionKey);
    }

    public function total(array $cart = null): float
    {
        $cart = $cart ?? $this->get();

        return (float) collect($cart)
            ->map(fn ($item) => ((float) ($item['price'] ?? 0)) * ((int) ($item['qty'] ?? 1)))
            ->sum();
    }

    public function toOrderItems(array $cart = null): array
    {
        $cart = $cart ?? $this->get();

        return collect($cart)
            ->map(fn ($item) => [
                'product_id' => (int) ($item['id'] ?? 0),
                'quantity' => (int) ($item['qty'] ?? 1),
            ])
            ->values()
            ->all();
    }

    private function customTubKey(string $sizeKey, string $insideKey, string $woodKey): string
    {
        return 'custom_tub--' . $sizeKey . '--' . $insideKey . '--' . $woodKey;
    }
}