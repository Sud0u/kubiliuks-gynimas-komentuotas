<?php

namespace App\Repositories;

use App\Models\Product;
use App\Repositories\Contracts\ProductRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;

class ProductRepository extends BaseRepository implements ProductRepositoryInterface
{
    public function __construct(Product $model)
    {
        parent::__construct($model);
    }

    public function baseCatalogQuery(bool $onlyActive = true): Builder
    {
        $query = $this->query()->with('category');

        if ($onlyActive) {
            $query->where('is_active', 1);
        }

        return $query;
    }

    public function applyFilters(Builder $query, array $filters): Builder
    {
        if (!empty($filters['q'])) {
            $term = trim((string) $filters['q']);

            $query->where(function (Builder $subQuery) use ($term) {
                $subQuery->where('name', 'like', "%{$term}%")
                    ->orWhere('description', 'like', "%{$term}%");
            });
        }

        if (!empty($filters['category_id'])) {
            $query->where('category_id', (int) $filters['category_id']);
        }

        if (($filters['min_price'] ?? null) !== null && ($filters['min_price'] ?? '') !== '') {
            $query->where('price', '>=', (float) $filters['min_price']);
        }

        if (($filters['max_price'] ?? null) !== null && ($filters['max_price'] ?? '') !== '') {
            $query->where('price', '<=', (float) $filters['max_price']);
        }

        $sort = (string) ($filters['sort'] ?? 'newest');

        return match ($sort) {
            'price_asc' => $query->orderBy('price', 'asc'),
            'price_desc' => $query->orderBy('price', 'desc'),
            'name_asc' => $query->orderBy('name', 'asc'),
            'name_desc' => $query->orderBy('name', 'desc'),
            'oldest' => $query->orderBy('created_at', 'asc'),
            default => $query->orderBy('created_at', 'desc'),
        };
    }


    public function findActiveBySlug(string $slug): Product
    {
        return $this->baseCatalogQuery(true)
            ->where('slug', $slug)
            ->firstOrFail();
    }

    public function getById(int $id): ?Product
    {
        return $this->query()->find($id);
    }

    public function getByIdOrFail(int $id): Product
    {
        return $this->query()->findOrFail($id);
    }

    public function create(array $data): Product
    {
        return Product::query()->create($data);
    }
}