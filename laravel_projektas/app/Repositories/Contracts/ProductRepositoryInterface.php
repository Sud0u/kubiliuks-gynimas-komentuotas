<?php

namespace App\Repositories\Contracts;

use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;

interface ProductRepositoryInterface
{
    public function baseCatalogQuery(bool $onlyActive = true): Builder;

    /** $filters: q, category_id, min_price, max_price, sort */
    public function applyFilters(Builder $q, array $filters): Builder;

    public function findActiveBySlug(string $slug): Product;

    public function getById(int $id): ?Product;

    public function getByIdOrFail(int $id): Product;

    public function create(array $data): Product;
}
