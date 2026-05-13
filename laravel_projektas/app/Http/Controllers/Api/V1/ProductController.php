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
    public function index(Request $request)
    {
        $filters = [
            'q' => $request->query('q'),
            'category_id' => $request->query('category_id'),
            'min_price' => $request->query('min_price'),
            'max_price' => $request->query('max_price'),
            'sort' => $request->query('sort', 'newest'),
        ];

        $perPage = (int) $request->query('per_page', 12);
        $perPage = max(1, min(48, $perPage));

        $paginator = $this->catalog->paginateForApi($filters, $perPage);

        // Minimalus API formatas frontui (Blade + JS)
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
