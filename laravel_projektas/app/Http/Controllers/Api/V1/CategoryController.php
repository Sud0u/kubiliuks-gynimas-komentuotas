<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Services\ProductCatalogService;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function __construct(
        private readonly ProductCatalogService $catalog
    ) {}

    public function index()
    {
        $categories = Category::query()
            ->select(['id', 'name', 'slug'])
            ->withCount([
                'products as active_products_count' => function ($query) {
                    $query->where('is_active', 1);
                },
            ])
            ->having('active_products_count', '>', 0)
            ->orderBy('name')
            ->get()
            ->map(function (Category $category) {
                return [
                    'id' => (int) $category->id,
                    'name' => $category->name,
                    'slug' => $category->slug,
                    'products_count' => (int) $category->active_products_count,
                ];
            })
            ->values();

        return response()->json([
            'data' => $categories,
        ]);
    }

    public function productsByCategory(Request $request, int $id)
    {
        $category = Category::query()
            ->select(['id', 'name', 'slug'])
            ->whereKey($id)
            ->firstOrFail();

        $filters = [
            'q' => $request->query('q'),
            'category_id' => $category->id,
            'min_price' => $request->query('min_price'),
            'max_price' => $request->query('max_price'),
            'sort' => $request->query('sort', 'newest'),
        ];

        $perPage = (int) $request->query('per_page', 12);
        $perPage = max(1, min(48, $perPage));

        $paginator = $this->catalog->paginateForApi($filters, $perPage);

        $products = $paginator->getCollection()->map(function (Product $product) {
            return [
                'id' => (int) $product->id,
                'name' => $product->name,
                'slug' => $product->slug,
                'description' => $product->description,
                'price' => (float) $product->price,
                'stock' => (int) ($product->stock ?? 0),
                'image' => $product->image,
                'image_url' => $this->publicImageUrl($product->image),
                'category' => $product->relationLoaded('category') && $product->category
                    ? [
                        'id' => (int) $product->category->id,
                        'name' => $product->category->name,
                        'slug' => $product->category->slug,
                    ]
                    : null,
            ];
        })->values();

        return response()->json([
            'data' => [
                'category' => [
                    'id' => (int) $category->id,
                    'name' => $category->name,
                    'slug' => $category->slug,
                ],
                'products' => $products,
            ],
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
        ]);
    }

    private function publicImageUrl(?string $path): ?string
    {
        if (!$path) {
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