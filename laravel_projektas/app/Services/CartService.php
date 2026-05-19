<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Facades\Session;

class CartService
{
    // krepselio sesijos raktas komentaro pradzia
    // Krepselis saugomas sesijoje su raktu cart.
    // Tai reiskia, kad vartotojo krepselis laikomas narsymo sesijoje, kol jis perka.
    // krepselio sesijos raktas komentaro pabaiga
    private string $sessionKey = 'cart';

    // krepselio gavimas komentaro pradzia
    // Sita funkcija paima krepseli is sesijos.
    // Jei krepselio dar nera, grazinamas tuscias masyvas, kad kodas nesugriutu.
    // krepselio gavimas komentaro pabaiga
    public function get(): array
    {
        return Session::get($this->sessionKey, []);
    }

    // paprastos prekes pridejimas komentaro pradzia
    // Cia i krepseli idedama paprasta preke is katalogo.
    // Jei tokia preke jau yra krepselyje, padidinamas jos kiekis.
    // Jei jos dar nera, sukuriamas naujas krepselio irasas.
    // paprastos prekes pridejimas komentaro pabaiga
    public function add(Product $product, int $qty = 1): void
    {
        // kiekio saugiklis komentaro pradzia
        // Cia tikrinama ar kiekis nera mazesnis uz 1.
        // Jei ateitu 0 arba minusas, sistema vistiek padaro 1.
        // Taip apsaugoma, kad krepselyje nebutu blogo kiekio.
        // kiekio saugiklis komentaro pabaiga
        if ($qty < 1) {
            $qty = 1;
        }

        $cart = $this->get();
        $id = (int) $product->id;

        // ar preke jau yra krepselyje komentaro pradzia
        // Cia tikrinama ar tokia preke jau egzistuoja krepselyje.
        // Jei egzistuoja, jos kiekis padidinamas. Jei ne, sukuriamas naujas irasas.
        // ar preke jau yra krepselyje komentaro pabaiga
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

        // krepselio issaugojimas komentaro pradzia
        // Cia atnaujintas krepselis irasomas atgal i sesija.
        // Be sitos eilutes pakeitimai neissisaugotu.
        // krepselio issaugojimas komentaro pabaiga
        Session::put($this->sessionKey, $cart);
    }

    // individualaus kubilo saugojimas sesijos krepšelyje komentaro pradzia
    // Čia suformuojamas specialus krepšelio įrašas, kuris nėra paprasta prekė iš katalogo.
    public function addCustomTub(array $config, int $qty = 1): string
    {
        if ($qty < 1) {
            $qty = 1;
        }

        // custom kubilo pasirinkimu paemimas komentaro pradzia
        // Cia is config masyvo paimami individualaus kubilo pasirinkimai.
        // Jei kazkokios reiksmes nera, naudojamas saugus numatytas variantas.
        // custom kubilo pasirinkimu paemimas komentaro pabaiga
        $sizeKey = (string) ($config['size_key'] ?? '180');
        $insideKey = (string) ($config['inside_key'] ?? 'balta');
        $woodKey = (string) ($config['wood_key'] ?? 'base-ruda');

        $sizeLabel = (string) ($config['size_label'] ?? '180 cm');
        $insideLabel = (string) ($config['inside_label'] ?? 'Balta');
        $woodLabel = (string) ($config['wood_label'] ?? 'Šviesi ruda');

        // custom kubilo kaina ir nuotrauka komentaro pradzia
        // Cia paimama custom kubilo galutine kaina ir nuotraukos kelias.
        // Kaina jau buna paskaiciuota CartController faile backend puseje.
        // custom kubilo kaina ir nuotrauka komentaro pabaiga
        $unitPrice = (float) ($config['price'] ?? 0);
        $image = (string) ($config['image'] ?? ('images/kubilai/' . $insideKey . '-' . $woodKey . '.png'));

        // Raktas sudaromas iš pasirinkimų, todėl ta pati komplektacija krepšelyje susijungia į vieną eilutę.
        // custom kubilo unikalus raktas komentaro pradzia
        // Cia sukuriamas specialus raktas is dydzio, vidaus spalvos ir medienos.
        // Del to tokia pati komplektacija krepselyje susijungia i viena eilute.
        // custom kubilo unikalus raktas komentaro pabaiga
        $key = $this->customTubKey($sizeKey, $insideKey, $woodKey);

        $cart = $this->get();

        // ar toks custom kubilas jau yra komentaro pradzia
        // Cia tikrinama ar toks pats individualus kubilas jau yra krepselyje.
        // Jei yra, padidinamas kiekis. Jei nera, sukuriamas naujas krepselio irasas.
        // ar toks custom kubilas jau yra komentaro pabaiga
        if (isset($cart[$key])) {
            $cart[$key]['qty'] = (int) ($cart[$key]['qty'] ?? 0) + $qty;
            $cart[$key]['price'] = $unitPrice;
            $cart[$key]['image'] = $image;
            $cart[$key]['name'] = 'Individualus kubilas';
            $cart[$key]['subtitle'] = $sizeLabel . ' · ' . $insideLabel . ' · ' . $woodLabel;
            // Meta dalyje saugomi pasirinkimai, kad vėliau juos matytume krepšelyje ir užsakyme.
            // custom kubilo meta informacija komentaro pradzia
            // Meta dalyje saugomi visi pasirinkimai: dydis, vidus, mediena ir gamybos laikas.
            // Veliau sita informacija galima rodyti krepselyje arba uzsakymo santraukoje.
            // custom kubilo meta informacija komentaro pabaiga
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
            // naujas custom kubilo irasas komentaro pradzia
            // Cia sukuriamas naujas individualaus kubilo irasas krepselyje.
            // Type yra custom_tub, todel veliau sistema zino kad tai ne paprasta preke.
            // naujas custom kubilo irasas komentaro pabaiga
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
    // individualaus kubilo saugojimas sesijos krepšelyje komentaro pabaiga

    // paprastos prekes kiekio atnaujinimas komentaro pradzia
    // Cia atnaujinamas paprastos katalogo prekes kiekis krepselyje.
    // paprastos prekes kiekio atnaujinimas komentaro pabaiga
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

    // custom iraso kiekio atnaujinimas komentaro pradzia
    // Cia atnaujinamas irasas pagal specialu cart key.
    // Tai naudojama custom_tub, nes jis neturi paprasto produkto id kaip katalogo preke.
    // custom iraso kiekio atnaujinimas komentaro pabaiga
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

    // salinimas pagal produkto id komentaro pradzia
    // Cia pasalinama paprasta preke is krepselio pagal jos id.
    // Jei po salinimo krepselis tuscias, visa sesija isvaloma.
    // salinimas pagal produkto id komentaro pabaiga
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

    // salinimas pagal custom key komentaro pradzia
    // Cia salinamas irasas pagal specialu rakta.
    // Tai reikalinga individualiam kubilui, nes jo raktas sudarytas is pasirinkimu.
    // salinimas pagal custom key komentaro pabaiga
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

    // custom_tub atpazinimas komentaro pradzia
    // Cia patikrinama ar krepselio raktas prasideda custom_tub--.
    // Jei taip, sistema zino kad tai individualus kubilas.
    // custom_tub atpazinimas komentaro pabaiga
    public function isCustomKey(string $key): bool
    {
        return str_starts_with($key, 'custom_tub--');
    }

    public function clear(): void
    {
        Session::forget($this->sessionKey);
    }

    // krepselio sumos skaiciavimas komentaro pradzia
    // Cia sudedamos visu krepselio prekiu sumos.
    // Kiekvienai eilutei kaina dauginama is kiekio ir tada viskas susumuojama.
    // krepselio sumos skaiciavimas komentaro pabaiga
    public function total(array $cart = null): float
    {
        $cart = $cart ?? $this->get();

        return (float) collect($cart)
            ->map(fn ($item) => ((float) ($item['price'] ?? 0)) * ((int) ($item['qty'] ?? 1)))
            ->sum();
    }

    // krepselio paruosimas order items komentaro pradzia
    // Cia krepselio elementai paverciami i paprasta struktura uzsakymo prekems.
    // Sita vieta naudinga kai uzsakymo kurimo metu reikia zinoti produkto id ir kieki.
    // krepselio paruosimas order items komentaro pabaiga
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

    // custom kubilo rakto sudarymas komentaro pradzia
    // Cia is pasirinkto dydzio, vidaus spalvos ir medienos sudaromas vienas raktas.
    // Pvz custom_tub--200--melyna--chestnut-ruda.
    // custom kubilo rakto sudarymas komentaro pabaiga
    private function customTubKey(string $sizeKey, string $insideKey, string $woodKey): string
    {
        return 'custom_tub--' . $sizeKey . '--' . $insideKey . '--' . $woodKey;
    }
}