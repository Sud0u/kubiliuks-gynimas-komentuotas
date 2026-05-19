<?php

namespace App\Repositories;

use App\Models\Product;
use App\Repositories\Contracts\ProductRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;

// ProductRepository paskirtis komentaro pradzia
// Sitas failas atsakingas uz prekiu paemima is duomenu bazes.
// Repository naudojamas tam, kad filtravimo logika nebutu sumaisyta su controlleriu.
// ProductRepository paskirtis komentaro pabaiga
class ProductRepository extends BaseRepository implements ProductRepositoryInterface
{
    // repository metodas komentaro pradzia
    // Cia prasideda metodas, kuris pagal filtrus formuoja DB uzklausa.
    // Jei vartotojas pasirenka kategorija ar paieska, sitas failas pritaiko salygas.
    // repository metodas komentaro pabaiga
    public function __construct(Product $model)
    {
        parent::__construct($model);
    }

    // bazinė katalogo uzklausa komentaro pradzia
    // Cia sukuriama pradine prekiu uzklausa.
    // Jei onlyActive true, rodomos tik aktyvios prekes, kurios turi buti matomos klientui.
    // bazinė katalogo uzklausa komentaro pabaiga
    public function baseCatalogQuery(bool $onlyActive = true): Builder
    {
        $query = $this->query()->with('category');

        if ($onlyActive) {
            $query->where('is_active', 1);
        }

        return $query;
    }

    // produktų filtro logika komentaro pradzia
    // Čia katalogas filtruojamas pagal paiešką, kategoriją, kainą ir rikiavimą.
    public function applyFilters(Builder $query, array $filters): Builder
    {
        // Paieška ieško pagal prekės pavadinimą ir aprašymą.
        // paieska pagal teksta komentaro pradzia
        // Jei vartotojas iveda paieskos zodi, cia ieskoma pagal pavadinima ir aprasyma.
        // Taip kataloge galima rasti preke greiciau.
        // paieska pagal teksta komentaro pabaiga
        if (!empty($filters['q'])) {
            $term = trim((string) $filters['q']);

            $query->where(function (Builder $subQuery) use ($term) {
                $subQuery->where('name', 'like', "%{$term}%")
                    ->orWhere('description', 'like', "%{$term}%");
            });
        }

        // filtravimas pagal kategorija komentaro pradzia
        // Jei pasirinkta kategorija, cia paliekamos tik tos kategorijos prekes.
        // Pvz kubilai, nameliai arba kiti gaminiai.
        // filtravimas pagal kategorija komentaro pabaiga
        if (!empty($filters['category_id'])) {
            $query->where('category_id', (int) $filters['category_id']);
        }

        // filtravimas pagal minimalia kaina komentaro pradzia
        // Cia jeigu vartotojas nurodo minimalia kaina, rodomos tik brangesnes arba lygios prekes.
        // filtravimas pagal minimalia kaina komentaro pabaiga
        if (($filters['min_price'] ?? null) !== null && ($filters['min_price'] ?? '') !== '') {
            $query->where('price', '>=', (float) $filters['min_price']);
        }

        if (($filters['max_price'] ?? null) !== null && ($filters['max_price'] ?? '') !== '') {
            $query->where('price', '<=', (float) $filters['max_price']);
        }

        // rikiavimo pasirinkimas komentaro pradzia
        // Cia paimama rikiavimo reiksme.
        // Jei nieko nepasirinkta, naudojamas newest, tai yra naujausios prekes pirmos.
        // rikiavimo pasirinkimas komentaro pabaiga
        $sort = (string) ($filters['sort'] ?? 'newest');

        // Rikiavimas atliekamas serveryje pagal pasirinktą sort reikšmę.
        return match ($sort) {
            'price_asc' => $query->orderBy('price', 'asc'),
            'price_desc' => $query->orderBy('price', 'desc'),
            'name_asc' => $query->orderBy('name', 'asc'),
            'name_desc' => $query->orderBy('name', 'desc'),
            'oldest' => $query->orderBy('created_at', 'asc'),
            default => $query->orderBy('created_at', 'desc'),
        };
    }

    // produktų filtro logika komentaro pabaiga

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