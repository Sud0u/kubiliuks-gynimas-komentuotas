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
    // prekiu API sarasas komentaro pradzia
    // Sitas metodas grazina prekes katalogui.
    // Jis paima filtrus is query parametru ir perduoda juos ProductCatalogService.
    // prekiu API sarasas komentaro pabaiga
    public function index(Request $request)
    {
        // prekiu filtru paemimas komentaro pradzia
        // Cia is URL paimama paieska, kategorija, kainos ribos ir rikiavimas.
        // Pvz vartotojas pasirenka kategorija arba paieskos teksta.
        // prekiu filtru paemimas komentaro pabaiga
        $filters = [
            'q' => $request->query('q'),
            'category_id' => $request->query('category_id'),
            'min_price' => $request->query('min_price'),
            'max_price' => $request->query('max_price'),
            'sort' => $request->query('sort', 'newest'),
        ];

        // puslapiavimo kiekis komentaro pradzia
        // Cia nustatoma kiek prekiu rodyti viename puslapyje.
        // max/min apsaugo, kad vartotojas negaletu paprasyti per daug prekiu vienu kartu.
        // puslapiavimo kiekis komentaro pabaiga
        $perPage = (int) $request->query('per_page', 12);
        $perPage = max(1, min(48, $perPage));

        // prekiu gavimas per service komentaro pradzia
        // Cia filtrai perduodami i ProductCatalogService.
        // Service/repository puse tada parenka prekes is DB pagal vartotojo filtrus.
        // prekiu gavimas per service komentaro pabaiga
        $paginator = $this->catalog->paginateForApi($filters, $perPage);

        // Minimalus API formatas frontui (Blade + JS)
        // prekiu formato paruosimas frontendui komentaro pradzia
        // Cia kiekviena preke paverciama i paprasta masyva.
        // Frontendui graziname tik tai ko reikia: pavadinima, kaina, nuotraukas, kategorija ir likuti.
        // prekiu formato paruosimas frontendui komentaro pabaiga
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
    // vienos prekes API komentaro pradzia
    // Cia pagal slug surandama viena aktyvi preke.
    // Slug yra grazus URL pavadinimas, pvz medinis-kubilas.
    // vienos prekes API komentaro pabaiga
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


    // prekes nuotrauku galerija komentaro pradzia
    // Cia sujungiama pagrindine nuotrauka, image_2, image_3 ir gallery_images.
    // Taip prekes puslapyje galima rodyti kelias nuotraukas.
    // prekes nuotrauku galerija komentaro pabaiga
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

    // prekes nuotraukos URL komentaro pradzia
    // Cia is nuotraukos kelio padaromas normalus URL.
    // Jei kelias tuscias, grazinama null, kad frontend negautu blogo paveikslelio.
    // prekes nuotraukos URL komentaro pabaiga
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
