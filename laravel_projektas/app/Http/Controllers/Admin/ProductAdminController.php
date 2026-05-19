<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ProductUpsertRequest;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductAdminController extends Controller
{
    // GYNIMO PAAISKINIMAS PRADZIA: admin produktu sarasas
    // Cia admin mato produktu sarasa.
    // Galima filtruoti pagal paieska, kategorija ir aktyvumo busena.
    // GYNIMO PAAISKINIMAS PABAIGA: admin produktu sarasas
    public function index(Request $request)
    {
        $status = $request->query('status', 'active');
        $q = trim((string) $request->query('q', ''));

        $base = Product::query()
            ->with('category')
            ->latest();

        if ($q !== '') {
            $base->where(function ($w) use ($q) {
                $w->where('name', 'like', '%' . $q . '%')
                    ->orWhere('slug', 'like', '%' . $q . '%');
            });
        }

        if ($status === 'active') {
            $base->where('is_active', true);
        } elseif ($status === 'inactive') {
            $base->where('is_active', false);
        }

        $products = $base->paginate(12)->withQueryString();

        $countAll = Product::query()->count();
        $countActive = Product::query()->where('is_active', true)->count();
        $countInactive = Product::query()->where('is_active', false)->count();

        return view('admin.products.index', compact(
            'products',
            'status',
            'q',
            'countAll',
            'countActive',
            'countInactive'
        ));
    }

    // GYNIMO PAAISKINIMAS PRADZIA: produkto kurimo forma
    // Cia atidaroma forma naujai prekei sukurti.
    // I forma perduodamos kategorijos, kad admin galetu pasirinkti prekes kategorija.
    // GYNIMO PAAISKINIMAS PABAIGA: produkto kurimo forma
    public function create()
    {
        $categories = Category::query()->orderBy('name')->get();

        return view('admin.products.create', compact('categories'));
    }

    // KODO PRADŽIA: admin prekės sukūrimas
    // Čia administratorius sukuria prekę, įkelia nuotraukas ir sistema sugeneruoja slug.
    // GYNIMO PAAISKINIMAS PRADZIA: naujos prekes issaugojimas
    // Cia issaugoma nauja preke.
    // Duomenys jau buna patikrinti ProductUpsertRequest faile.
    // GYNIMO PAAISKINIMAS PABAIGA: naujos prekes issaugojimas
    public function store(ProductUpsertRequest $request)
    {
        // GYNIMO PAAISKINIMAS PRADZIA: validuoti admin duomenys
        // Cia paimami tik validuoti formos duomenys.
        // Tai reiskia, kad i DB nepatenka laukai, kuriu neleidziame issaugoti.
        // GYNIMO PAAISKINIMAS PABAIGA: validuoti admin duomenys
        $data = $request->validated();

        unset($data['gallery_images'], $data['remove_gallery_images']);

        $data['name'] = trim((string) $data['name']);
        // Slug generuojamas automatiškai iš pavadinimo, kad URL būtų tvarkingas.
        $data['slug'] = $this->generateUniqueSlug($data['name']);
        $data['stock'] = $data['stock'] ?? 0;
        $data['is_active'] = $request->boolean('is_active', true);

        // Čia saugomos trys pagrindinės prekės nuotraukos.
        foreach ($this->mainImageFields() as $field) {
            if ($request->hasFile($field)) {
                $data[$field] = $request->file($field)->store('products', 'public');
            }
        }

        // Papildomos galerijos nuotraukos saugomos atskirai, kad nebūtų 3 nuotraukų ribojimo.
        $data['gallery_images'] = $this->storeGalleryImages($request);

        Product::create($data);

        return redirect()
            ->route('admin.products.index')
            ->with('success', 'Prekė sukurta.');
    }

    // GYNIMO PAAISKINIMAS PRADZIA: produkto redagavimo forma
    // Cia atidaromas esamos prekes redagavimas.
    // Laravel pagal route automatiskai paduoda Product modeli.
    // GYNIMO PAAISKINIMAS PABAIGA: produkto redagavimo forma
    public function edit(Product $product)
    {
        $categories = Category::query()->orderBy('name')->get();

        return view('admin.products.edit', compact('product', 'categories'));
    }

    // KODO PABAIGA: admin prekės sukūrimas

    // GYNIMO PAAISKINIMAS PRADZIA: prekes atnaujinimas
    // Cia admin pakeicia prekes informacija.
    // Vel naudojamas ProductUpsertRequest, todel ir kurimas ir redagavimas turi ta pacia validacija.
    // GYNIMO PAAISKINIMAS PABAIGA: prekes atnaujinimas
    public function update(ProductUpsertRequest $request, Product $product)
    {
        $data = $request->validated();

        unset($data['gallery_images'], $data['remove_gallery_images']);

        $data['name'] = trim((string) $data['name']);
        $data['slug'] = $this->generateUniqueSlug($data['name'], $product->id);
        $data['stock'] = $data['stock'] ?? $product->stock;
        $data['is_active'] = $request->boolean('is_active', $product->is_active);

        foreach ($this->mainImageFields() as $field) {
            if ($request->hasFile($field)) {
                $this->deletePublicFile($product->{$field});
                $data[$field] = $request->file($field)->store('products', 'public');
            }
        }

        // Redaguojant prekę senos galerijos nuotraukos išlaikomos, nebent admin jas pažymi pašalinimui.
        $galleryImages = $this->currentGalleryImages($product);
        $galleryImages = $this->removeGalleryImages($request, $galleryImages);
        $galleryImages = array_merge($galleryImages, $this->storeGalleryImages($request));

        $data['gallery_images'] = array_values(array_unique($galleryImages));

        $product->update($data);

        return redirect()
            ->route('admin.products.index')
            ->with('success', 'Prekė atnaujinta.');
    }

    // GYNIMO PAAISKINIMAS PRADZIA: prekes trynimas
    // Cia admin gali istrinti preke.
    // Pries trinant pasalinamos ir su preke susijusios nuotraukos.
    // GYNIMO PAAISKINIMAS PABAIGA: prekes trynimas
    public function destroy(Product $product)
    {
        if ($product->orderItems()->exists()) {
            $product->is_active = false;
            $product->stock = 0;
            $product->save();

            return redirect()
                ->route('admin.products.index', ['status' => 'active'])
                ->with('success', 'Prekės ištrinti negalima, nes ji yra užsakymuose. Prekė paslėpta ir nustatytas likutis 0.');
        }

        foreach ($this->mainImageFields() as $field) {
            $this->deletePublicFile($product->{$field});
        }

        foreach ($this->currentGalleryImages($product) as $path) {
            $this->deletePublicFile($path);
        }

        $product->delete();

        return redirect()
            ->route('admin.products.index', ['status' => 'active'])
            ->with('success', 'Prekė ištrinta.');
    }

    private function mainImageFields(): array
    {
        return ['image', 'image_2', 'image_3'];
    }

    // KODO PRADŽIA: papildomų produkto nuotraukų saugojimas
    // GYNIMO PAAISKINIMAS PRADZIA: galerijos nuotrauku issaugojimas
    // Cia issaugomos papildomos prekes nuotraukos.
    // Jos naudojamos prekes puslapyje kaip galerija.
    // GYNIMO PAAISKINIMAS PABAIGA: galerijos nuotrauku issaugojimas
    private function storeGalleryImages(ProductUpsertRequest $request): array
    {
        if (!$request->hasFile('gallery_images')) {
            return [];
        }

        return collect($request->file('gallery_images'))
            ->filter()
            ->map(fn ($file) => $file->store('products', 'public'))
            ->values()
            ->all();
    }

    // KODO PABAIGA: papildomų produkto nuotraukų saugojimas

    private function currentGalleryImages(Product $product): array
    {
        $images = $product->gallery_images;

        if (is_string($images)) {
            $decoded = json_decode($images, true);
            $images = is_array($decoded) ? $decoded : [];
        }

        return collect($images ?: [])
            ->filter(fn ($path) => is_string($path) && trim($path) !== '')
            ->map(fn ($path) => trim($path))
            ->values()
            ->all();
    }

    // GYNIMO PAAISKINIMAS PRADZIA: galerijos nuotrauku salinimas
    // Cia admin gali pasalinti pasirinktas galerijos nuotraukas.
    // Is masyvo paliekamos tik tos, kuriu nereikia istrinti.
    // GYNIMO PAAISKINIMAS PABAIGA: galerijos nuotrauku salinimas
    private function removeGalleryImages(ProductUpsertRequest $request, array $galleryImages): array
    {
        $removeImages = collect($request->input('remove_gallery_images', []))
            ->filter(fn ($path) => is_string($path) && trim($path) !== '')
            ->map(fn ($path) => trim($path))
            ->values()
            ->all();

        if (empty($removeImages)) {
            return $galleryImages;
        }

        foreach ($removeImages as $path) {
            $this->deletePublicFile($path);
        }

        return collect($galleryImages)
            ->reject(fn ($path) => in_array($path, $removeImages, true))
            ->values()
            ->all();
    }

    private function deletePublicFile(?string $path): void
    {
        if (!$path) {
            return;
        }

        $path = ltrim(trim($path), '/');

        if (str_starts_with($path, 'storage/')) {
            $path = substr($path, strlen('storage/'));
        }

        if (str_starts_with($path, 'public/')) {
            $path = substr($path, strlen('public/'));
        }

        if ($path !== '' && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }

    // GYNIMO PAAISKINIMAS PRADZIA: unikalaus slug kurimas
    // Slug yra nuorodos dalis, pvz medinis-kubilas.
    // Cia uzdedama apsauga, kad keli produktai neturetu tokio pacio slug.
    // GYNIMO PAAISKINIMAS PABAIGA: unikalaus slug kurimas
    private function generateUniqueSlug(?string $nameInput, ?int $ignoreProductId = null): string
    {
        $baseSlug = Str::slug(trim((string) $nameInput));

        if ($baseSlug === '') {
            $baseSlug = 'preke-' . now()->timestamp;
        }

        $slug = $baseSlug;
        $counter = 2;

        while ($this->slugExists($slug, $ignoreProductId)) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    private function slugExists(string $slug, ?int $ignoreProductId = null): bool
    {
        return Product::query()
            ->when($ignoreProductId, fn ($q) => $q->where('id', '!=', $ignoreProductId))
            ->where('slug', $slug)
            ->exists();
    }
}
