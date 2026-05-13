<?php

namespace App\Services;

use App\Repositories\Contracts\ProductRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;

class ProductCatalogService
{
    public function __construct(
        private readonly ProductRepositoryInterface $products
    ) {}

    public function paginateForWeb(array $filters, int $perPage = 12): LengthAwarePaginator
    {
        $query = $this->products->baseCatalogQuery(onlyActive: true);
        $query = $this->products->applyFilters($query, $filters);

        return $query
            ->paginate($perPage)
            ->appends(array_filter($filters, fn ($value) => $value !== null && $value !== ''));
    }

    public function paginateForApi(array $filters, int $perPage = 10): LengthAwarePaginator
    {
        $query = $this->products->baseCatalogQuery(onlyActive: true);
        $query = $this->products->applyFilters($query, $filters);

        return $query
            ->paginate($perPage)
            ->appends(array_filter($filters, fn ($value) => $value !== null && $value !== ''));
    }
}