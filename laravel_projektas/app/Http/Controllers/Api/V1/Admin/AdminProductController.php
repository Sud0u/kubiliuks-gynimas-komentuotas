<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ProductUpsertRequest;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AdminProductController extends Controller
{
    public function index(Request $request)
    {
        $status = trim((string) $request->query('status', 'all'));
        $q = trim((string) $request->query('q', ''));
        $perPage = (int) $request->query('per_page', 12);

        if ($perPage < 1) {
            $perPage = 12;
        }

        if ($perPage > 100) {
            $perPage = 100;
        }

        $query = Product::query()
            ->with('category:id,name')
            ->latest();

        if ($q !== '') {
            $query->where(function ($w) use ($q) {
                $w->where('name', 'like', '%' . $q . '%')
                    ->orWhere('slug', 'like', '%' . $q . '%');
            });
        }

        if ($status === 'active') {
            $query->where('is_active', true);
        } elseif ($status === 'inactive') {
            $query->where('is_active', false);
        }

        $products = $query->paginate($perPage)->withQueryString();

        $lowStock = Product::query()
            ->select(['id', 'name', 'slug', 'stock', 'is_active'])
            ->where('is_active', true)
            ->where('stock', '>', 0)
            ->where('stock', '<=', 5)
            ->orderBy('stock')
            ->limit(6)
            ->get();

        return response()->json([
            'data' => $products->items(),
            'meta' => [
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage(),
                'per_page' => $products->perPage(),
                'total' => $products->total(),
                'from' => $products->firstItem(),
                'to' => $products->lastItem(),
            ],
            'filters' => [
                'status' => $status,
                'q' => $q,
            ],
            'summary' => [
                'total' => Product::query()->count(),
                'active' => Product::query()->where('is_active', true)->count(),
                'inactive' => Product::query()->where('is_active', false)->count(),
            ],
            'low_stock' => $lowStock,
        ]);
    }

    public function store(ProductUpsertRequest $request)
    {
        $data = $request->validated();

        unset($data['gallery_images'], $data['remove_gallery_images']);

        $data['name'] = trim((string) $data['name']);
        $data['slug'] = $this->generateUniqueSlug($data['name']);
        $data['stock'] = $data['stock'] ?? 0;
        $data['is_active'] = $request->boolean('is_active', true);

        $data = $this->storeUploadedImages($request, $data);

        $product = Product::create($data);

        return response()->json([
            'message' => 'Prekė sukurta.',
            'data' => $product->fresh(),
        ], 201);
    }

    public function update(ProductUpsertRequest $request, $id)
    {
        $product = Product::findOrFail($id);

        $data = $request->validated();

        unset($data['gallery_images'], $data['remove_gallery_images']);

        $data['name'] = trim((string) $data['name']);
        $data['slug'] = $this->generateUniqueSlug($data['name'], $product->id);
        $data['stock'] = $data['stock'] ?? $product->stock;
        $data['is_active'] = $request->boolean('is_active', $product->is_active);

        $data = $this->storeUploadedImages($request, $data, $product);

        $product->update($data);

        return response()->json([
            'message' => 'Prekė atnaujinta.',
            'data' => $product->fresh(),
        ]);
    }

    public function destroy($id)
    {
        $product = Product::findOrFail($id);

        if ($product->orderItems()->exists()) {
            $product->is_active = false;
            $product->stock = 0;
            $product->save();

            return response()->json([
                'message' => 'Prekės ištrinti negalima, nes ji yra užsakymuose. Prekė paslėpta ir nustatytas likutis 0.',
            ], 409);
        }

        $this->deleteStoredImages($product);

        $product->delete();

        return response()->json([
            'message' => 'Prekė ištrinta.',
        ]);
    }

    private function storeUploadedImages(ProductUpsertRequest $request, array $data, ?Product $product = null): array
    {
        foreach (['image', 'image_2', 'image_3'] as $field) {
            if ($request->hasFile($field)) {
                if ($product) {
                    $this->deletePublicFile($product->{$field});
                }

                $data[$field] = $request->file($field)->store('products', 'public');
            }
        }

        $galleryImages = $product ? $this->currentGalleryImages($product) : [];

        $removeImages = collect($request->input('remove_gallery_images', []))
            ->filter(fn ($path) => is_string($path) && trim($path) !== '')
            ->map(fn ($path) => trim($path))
            ->values()
            ->all();

        if (!empty($removeImages)) {
            foreach ($removeImages as $path) {
                $this->deletePublicFile($path);
            }

            $galleryImages = collect($galleryImages)
                ->reject(fn ($path) => in_array($path, $removeImages, true))
                ->values()
                ->all();
        }

        if ($request->hasFile('gallery_images')) {
            foreach ($request->file('gallery_images') as $file) {
                if ($file) {
                    $galleryImages[] = $file->store('products', 'public');
                }
            }
        }

        $data['gallery_images'] = array_values(array_unique($galleryImages));

        return $data;
    }

    private function deleteStoredImages(Product $product): void
    {
        foreach (['image', 'image_2', 'image_3'] as $field) {
            $this->deletePublicFile($product->{$field});
        }

        foreach ($this->currentGalleryImages($product) as $path) {
            $this->deletePublicFile($path);
        }
    }

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