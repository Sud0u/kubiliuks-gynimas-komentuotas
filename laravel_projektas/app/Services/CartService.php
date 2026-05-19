<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Facades\Session;

class CartService
{
    // GYNIMO PAAISKINIMAS PRADZIA: krepselio sesijos raktas
    // Krepselis saugomas sesijoje su raktu cart.
    // Tai reiskia, kad vartotojo krepselis laikomas narsymo sesijoje, kol jis perka.
    // GYNIMO PAAISKINIMAS PABAIGA: krepselio sesijos raktas
    private string $sessionKey = 'cart';

    // GYNIMO PAAISKINIMAS PRADZIA: krepselio gavimas
    // Sita funkcija paima krepseli is sesijos.
    // Jei krepselio dar nera, grazinamas tuscias masyvas, kad kodas nesugriutu.
    // GYNIMO PAAISKINIMAS PABAIGA: krepselio gavimas
    public function get(): array
    {
        return Session::get($this->sessionKey, []);
    }

    // GYNIMO PAAISKINIMAS PRADZIA: paprastos prekes pridejimas
    // Cia i krepseli idedama paprasta preke is katalogo.
    // Jei tokia preke jau yra krepselyje, padidinamas jos kiekis.
    // Jei jos dar nera, sukuriamas naujas krepselio irasas.
    // GYNIMO PAAISKINIMAS PABAIGA: paprastos prekes pridejimas
    public function add(Product $product, int $qty = 1): void
    {
        // GYNIMO PAAISKINIMAS PRADZIA: kiekio saugiklis
        // Cia tikrinama ar kiekis nera mazesnis uz 1.
        // Jei ateitu 0 arba minusas, sistema vistiek padaro 1.
        // Taip apsaugoma, kad krepselyje nebutu blogo kiekio.
        // GYNIMO PAAISKINIMAS PABAIGA: kiekio saugiklis
        if ($qty < 1) {
            $qty = 1;
        }

        $cart = $this->get();
        $id = (int) $product->id;

        // GYNIMO PAAISKINIMAS PRADZIA: ar preke jau yra krepselyje
        // Cia tikrinama ar tokia preke jau egzistuoja krepselyje.
        // Jei egzistuoja, jos kiekis padidinamas. Jei ne, sukuriamas naujas irasas.
        // GYNIMO PAAISKINIMAS PABAIGA: ar preke jau yra krepselyje
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

        // GYNIMO PAAISKINIMAS PRADZIA: krepselio issaugojimas
        // Cia atnaujintas krepselis irasomas atgal i sesija.
        // Be sitos eilutes pakeitimai neissisaugotu.
        // GYNIMO PAAISKINIMAS PABAIGA: krepselio issaugojimas
        Session::put($this->sessionKey, $cart);
    }

    // KODO PRADŽIA: individualaus kubilo saugojimas sesijos krepšelyje
    // Čia suformuojamas specialus krepšelio įrašas, kuris nėra paprasta prekė iš katalogo.
    public function addCustomTub(array $config, int $qty = 1): string
    {
        if ($qty < 1) {
            $qty = 1;
        }

        // GYNIMO PAAISKINIMAS PRADZIA: custom kubilo pasirinkimu paemimas
        // Cia is config masyvo paimami individualaus kubilo pasirinkimai.
        // Jei kazkokios reiksmes nera, naudojamas saugus numatytas variantas.
        // GYNIMO PAAISKINIMAS PABAIGA: custom kubilo pasirinkimu paemimas
        $sizeKey = (string) ($config['size_key'] ?? '180');
        $insideKey = (string) ($config['inside_key'] ?? 'balta');
        $woodKey = (string) ($config['wood_key'] ?? 'base-ruda');

        $sizeLabel = (string) ($config['size_label'] ?? '180 cm');
        $insideLabel = (string) ($config['inside_label'] ?? 'Balta');
        $woodLabel = (string) ($config['wood_label'] ?? 'Šviesi ruda');

        // GYNIMO PAAISKINIMAS PRADZIA: custom kubilo kaina ir nuotrauka
        // Cia paimama custom kubilo galutine kaina ir nuotraukos kelias.
        // Kaina jau buna paskaiciuota CartController faile backend puseje.
        // GYNIMO PAAISKINIMAS PABAIGA: custom kubilo kaina ir nuotrauka
        $unitPrice = (float) ($config['price'] ?? 0);
        $image = (string) ($config['image'] ?? ('images/kubilai/' . $insideKey . '-' . $woodKey . '.png'));

        // Raktas sudaromas iš pasirinkimų, todėl ta pati komplektacija krepšelyje susijungia į vieną eilutę.
        // GYNIMO PAAISKINIMAS PRADZIA: custom kubilo unikalus raktas
        // Cia sukuriamas specialus raktas is dydzio, vidaus spalvos ir medienos.
        // Del to tokia pati komplektacija krepselyje susijungia i viena eilute.
        // GYNIMO PAAISKINIMAS PABAIGA: custom kubilo unikalus raktas
        $key = $this->customTubKey($sizeKey, $insideKey, $woodKey);

        $cart = $this->get();

        // GYNIMO PAAISKINIMAS PRADZIA: ar toks custom kubilas jau yra
        // Cia tikrinama ar toks pats individualus kubilas jau yra krepselyje.
        // Jei yra, padidinamas kiekis. Jei nera, sukuriamas naujas krepselio irasas.
        // GYNIMO PAAISKINIMAS PABAIGA: ar toks custom kubilas jau yra
        if (isset($cart[$key])) {
            $cart[$key]['qty'] = (int) ($cart[$key]['qty'] ?? 0) + $qty;
            $cart[$key]['price'] = $unitPrice;
            $cart[$key]['image'] = $image;
            $cart[$key]['name'] = 'Individualus kubilas';
            $cart[$key]['subtitle'] = $sizeLabel . ' · ' . $insideLabel . ' · ' . $woodLabel;
            // Meta dalyje saugomi pasirinkimai, kad vėliau juos matytume krepšelyje ir užsakyme.
            // GYNIMO PAAISKINIMAS PRADZIA: custom kubilo meta informacija
            // Meta dalyje saugomi visi pasirinkimai: dydis, vidus, mediena ir gamybos laikas.
            // Veliau sita informacija galima rodyti krepselyje arba uzsakymo santraukoje.
            // GYNIMO PAAISKINIMAS PABAIGA: custom kubilo meta informacija
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
            // GYNIMO PAAISKINIMAS PRADZIA: naujas custom kubilo irasas
            // Cia sukuriamas naujas individualaus kubilo irasas krepselyje.
            // Type yra custom_tub, todel veliau sistema zino kad tai ne paprasta preke.
            // GYNIMO PAAISKINIMAS PABAIGA: naujas custom kubilo irasas
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

    // GYNIMO PAAISKINIMAS PRADZIA: paprastos prekes kiekio atnaujinimas
    // Cia atnaujinamas paprastos katalogo prekes kiekis krepselyje.
    // GYNIMO PAAISKINIMAS PABAIGA: paprastos prekes kiekio atnaujinimas
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

    // GYNIMO PAAISKINIMAS PRADZIA: custom iraso kiekio atnaujinimas
    // Cia atnaujinamas irasas pagal specialu cart key.
    // Tai naudojama custom_tub, nes jis neturi paprasto produkto id kaip katalogo preke.
    // GYNIMO PAAISKINIMAS PABAIGA: custom iraso kiekio atnaujinimas
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

    // GYNIMO PAAISKINIMAS PRADZIA: salinimas pagal produkto id
    // Cia pasalinama paprasta preke is krepselio pagal jos id.
    // Jei po salinimo krepselis tuscias, visa sesija isvaloma.
    // GYNIMO PAAISKINIMAS PABAIGA: salinimas pagal produkto id
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

    // GYNIMO PAAISKINIMAS PRADZIA: salinimas pagal custom key
    // Cia salinamas irasas pagal specialu rakta.
    // Tai reikalinga individualiam kubilui, nes jo raktas sudarytas is pasirinkimu.
    // GYNIMO PAAISKINIMAS PABAIGA: salinimas pagal custom key
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

    // GYNIMO PAAISKINIMAS PRADZIA: custom_tub atpazinimas
    // Cia patikrinama ar krepselio raktas prasideda custom_tub--.
    // Jei taip, sistema zino kad tai individualus kubilas.
    // GYNIMO PAAISKINIMAS PABAIGA: custom_tub atpazinimas
    public function isCustomKey(string $key): bool
    {
        return str_starts_with($key, 'custom_tub--');
    }

    public function clear(): void
    {
        Session::forget($this->sessionKey);
    }

    // GYNIMO PAAISKINIMAS PRADZIA: krepselio sumos skaiciavimas
    // Cia sudedamos visu krepselio prekiu sumos.
    // Kiekvienai eilutei kaina dauginama is kiekio ir tada viskas susumuojama.
    // GYNIMO PAAISKINIMAS PABAIGA: krepselio sumos skaiciavimas
    public function total(array $cart = null): float
    {
        $cart = $cart ?? $this->get();

        return (float) collect($cart)
            ->map(fn ($item) => ((float) ($item['price'] ?? 0)) * ((int) ($item['qty'] ?? 1)))
            ->sum();
    }

    // GYNIMO PAAISKINIMAS PRADZIA: krepselio paruosimas order items
    // Cia krepselio elementai paverciami i paprasta struktura uzsakymo prekems.
    // Sita vieta naudinga kai uzsakymo kurimo metu reikia zinoti produkto id ir kieki.
    // GYNIMO PAAISKINIMAS PABAIGA: krepselio paruosimas order items
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

    // GYNIMO PAAISKINIMAS PRADZIA: custom kubilo rakto sudarymas
    // Cia is pasirinkto dydzio, vidaus spalvos ir medienos sudaromas vienas raktas.
    // Pvz custom_tub--200--melyna--chestnut-ruda.
    // GYNIMO PAAISKINIMAS PABAIGA: custom kubilo rakto sudarymas
    private function customTubKey(string $sizeKey, string $insideKey, string $woodKey): string
    {
        return 'custom_tub--' . $sizeKey . '--' . $insideKey . '--' . $woodKey;
    }
}