<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\ProductCatalogService;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function __construct(
        private readonly ProductCatalogService $catalog
    ) {}

    /**
     * GET /api/v1/products
     * Public katalogas (tik aktyvios prekės).
     */
    // GYNIMO PAAISKINIMAS PRADZIA: prekiu API sarasas
    // Sitas metodas grazina prekes katalogui.
    // Jis paima filtrus is query parametru ir perduoda juos ProductCatalogService.
    // GYNIMO PAAISKINIMAS PABAIGA: prekiu API sarasas
    public function index(Request $request)
    {
        // GYNIMO PAAISKINIMAS PRADZIA: prekiu filtru paemimas
        // Cia is URL paimama paieska, kategorija, kainos ribos ir rikiavimas.
        // Pvz vartotojas pasirenka kategorija arba paieskos teksta.
        // GYNIMO PAAISKINIMAS PABAIGA: prekiu filtru paemimas
        $filters = [
            'q' => $request->query('q'),
            'category_id' => $request->query('category_id'),
            'min_price' => $request->query('min_price'),
            'max_price' => $request->query('max_price'),
            'sort' => $request->query('sort', 'newest'),
        ];

        // GYNIMO PAAISKINIMAS PRADZIA: puslapiavimo kiekis
        // Cia nustatoma kiek prekiu rodyti viename puslapyje.
        // max/min apsaugo, kad vartotojas negaletu paprasyti per daug prekiu vienu kartu.
        // GYNIMO PAAISKINIMAS PABAIGA: puslapiavimo kiekis
        $perPage = (int) $request->query('per_page', 12);
        $perPage = max(1, min(48, $perPage));

        // GYNIMO PAAISKINIMAS PRADZIA: prekiu gavimas per service
        // Cia filtrai perduodami i ProductCatalogService.
        // Service/repository puse tada parenka prekes is DB pagal vartotojo filtrus.
        // GYNIMO PAAISKINIMAS PABAIGA: prekiu gavimas per service
        $paginator = $this->catalog->paginateForApi($filters, $perPage);

        // Minimalus API formatas frontui (Blade + JS)
        // GYNIMO PAAISKINIMAS PRADZIA: prekiu formato paruosimas frontendui
        // Cia kiekviena preke paverciama i paprasta masyva.
        // Frontendui graziname tik tai ko reikia: pavadinima, kaina, nuotraukas, kategorija ir likuti.
        // GYNIMO PAAISKINIMAS PABAIGA: prekiu formato paruosimas frontendui
        $data = $paginator->getCollection()->map(function (Product $p) {
            return [
                'id' => $p->id,
                'name' => $p->name,
                'slug' => $p->slug,
                'description' => $p->description,
                'production_time' => $p->production_time,
                'price' => (float) $p->price,
                'stock' => (int) ($p->stock ?? 0),
                'image' => $p->image,
                'image_url' => $this->publicImageUrl($p->image),
                'images' => $this->productImageUrls($p),
                'category' => $p->relationLoaded('category') && $p->category
                    ? ['id' => $p->category->id, 'name' => $p->category->name, 'slug' => $p->category->slug]
                    : null,
            ];
        });

        // paliekam meta/links jei vėliau darysi filtrus/puslapiavimą
        return response()->json([
            'data' => $data,
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
        ]);
    }

    /**
     * GET /api/v1/products/{slug}
     */
    // GYNIMO PAAISKINIMAS PRADZIA: vienos prekes API
    // Cia pagal slug surandama viena aktyvi preke.
    // Slug yra grazus URL pavadinimas, pvz medinis-kubilas.
    // GYNIMO PAAISKINIMAS PABAIGA: vienos prekes API
    public function show(string $slug)
    {
        $product = Product::with('category')
            ->where('is_active', 1)
            ->where('slug', $slug)
            ->firstOrFail();

        return response()->json([
            'data' => [
                'id' => $product->id,
                'name' => $product->name,
                'slug' => $product->slug,
                'description' => $product->description,
                'production_time' => $product->production_time,
                'price' => (float) $product->price,
                'stock' => (int) ($product->stock ?? 0),
                'image' => $product->image,
                'image_url' => $this->publicImageUrl($product->image),
                'images' => $this->productImageUrls($product),
                'category' => $product->category
                    ? ['id' => $product->category->id, 'name' => $product->category->name, 'slug' => $product->category->slug]
                    : null,
            ],
        ]);
    }


    // GYNIMO PAAISKINIMAS PRADZIA: prekes nuotrauku galerija
    // Cia sujungiama pagrindine nuotrauka, image_2, image_3 ir gallery_images.
    // Taip prekes puslapyje galima rodyti kelias nuotraukas.
    // GYNIMO PAAISKINIMAS PABAIGA: prekes nuotrauku galerija
    private function productImageUrls(Product $product): array
    {
        $galleryImages = $product->gallery_images ?? [];

        if (is_string($galleryImages)) {
            $galleryImages = json_decode($galleryImages, true) ?: [];
        }

        return collect(array_merge([
            $product->image,
            $product->image_2 ?? null,
            $product->image_3 ?? null,
        ], $galleryImages ?: []))
            ->map(fn ($path) => $this->publicImageUrl($path))
            ->filter()
            ->values()
            ->all();
    }

    // GYNIMO PAAISKINIMAS PRADZIA: prekes nuotraukos URL
    // Cia is nuotraukos kelio padaromas normalus URL.
    // Jei kelias tuscias, grazinama null, kad frontend negautu blogo paveikslelio.
    // GYNIMO PAAISKINIMAS PABAIGA: prekes nuotraukos URL
    private function publicImageUrl(?string $path): ?string
    {
        if (!$path) return null;

        $path = trim($path);

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        if (str_starts_with($path, '/')) {
            $path = ltrim($path, '/');
        }

        // public/images/...
        if (str_starts_with($path, 'images/')) {
            return asset($path);
        }

        // jei kažkas išsaugojo "storage/..."
        if (str_starts_with($path, 'storage/')) {
            return asset($path);
        }

        // default: storage/app/public (per storage:link)
        return asset('storage/' . $path);
    }
}
