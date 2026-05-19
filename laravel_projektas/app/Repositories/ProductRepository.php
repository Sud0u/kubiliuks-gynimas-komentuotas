<?php

namespace App\Repositories;

use App\Models\Product;
use App\Repositories\Contracts\ProductRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;

// GYNIMO PAAISKINIMAS PRADZIA: ProductRepository paskirtis
// Sitas failas atsakingas uz prekiu paemima is duomenu bazes.
// Repository naudojamas tam, kad filtravimo logika nebutu sumaisyta su controlleriu.
// GYNIMO PAAISKINIMAS PABAIGA: ProductRepository paskirtis
class ProductRepository extends BaseRepository implements ProductRepositoryInterface
{
    // GYNIMO PAAISKINIMAS PRADZIA: repository metodas
    // Cia prasideda metodas, kuris pagal filtrus formuoja DB uzklausa.
    // Jei vartotojas pasirenka kategorija ar paieska, sitas failas pritaiko salygas.
    // GYNIMO PAAISKINIMAS PABAIGA: repository metodas
    public function __construct(Product $model)
    {
        parent::__construct($model);
    }

    // GYNIMO PAAISKINIMAS PRADZIA: bazinė katalogo uzklausa
    // Cia sukuriama pradine prekiu uzklausa.
    // Jei onlyActive true, rodomos tik aktyvios prekes, kurios turi buti matomos klientui.
    // GYNIMO PAAISKINIMAS PABAIGA: bazinė katalogo uzklausa
    public function baseCatalogQuery(bool $onlyActive = true): Builder
    {
        $query = $this->query()->with('category');

        if ($onlyActive) {
            $query->where('is_active', 1);
        }

        return $query;
    }

    // KODO PRADŽIA: produktų filtro logika
    // Čia katalogas filtruojamas pagal paiešką, kategoriją, kainą ir rikiavimą.
    public function applyFilters(Builder $query, array $filters): Builder
    {
        // Paieška ieško pagal prekės pavadinimą ir aprašymą.
        // GYNIMO PAAISKINIMAS PRADZIA: paieska pagal teksta
        // Jei vartotojas iveda paieskos zodi, cia ieskoma pagal pavadinima ir aprasyma.
        // Taip kataloge galima rasti preke greiciau.
        // GYNIMO PAAISKINIMAS PABAIGA: paieska pagal teksta
        if (!empty($filters['q'])) {
            $term = trim((string) $filters['q']);

            $query->where(function (Builder $subQuery) use ($term) {
                $subQuery->where('name', 'like', "%{$term}%")
                    ->orWhere('description', 'like', "%{$term}%");
            });
        }

        // GYNIMO PAAISKINIMAS PRADZIA: filtravimas pagal kategorija
        // Jei pasirinkta kategorija, cia paliekamos tik tos kategorijos prekes.
        // Pvz kubilai, nameliai arba kiti gaminiai.
        // GYNIMO PAAISKINIMAS PABAIGA: filtravimas pagal kategorija
        if (!empty($filters['category_id'])) {
            $query->where('category_id', (int) $filters['category_id']);
        }

        // GYNIMO PAAISKINIMAS PRADZIA: filtravimas pagal minimalia kaina
        // Cia jeigu vartotojas nurodo minimalia kaina, rodomos tik brangesnes arba lygios prekes.
        // GYNIMO PAAISKINIMAS PABAIGA: filtravimas pagal minimalia kaina
        if (($filters['min_price'] ?? null) !== null && ($filters['min_price'] ?? '') !== '') {
            $query->where('price', '>=', (float) $filters['min_price']);
        }

        if (($filters['max_price'] ?? null) !== null && ($filters['max_price'] ?? '') !== '') {
            $query->where('price', '<=', (float) $filters['max_price']);
        }

        // GYNIMO PAAISKINIMAS PRADZIA: rikiavimo pasirinkimas
        // Cia paimama rikiavimo reiksme.
        // Jei nieko nepasirinkta, naudojamas newest, tai yra naujausios prekes pirmos.
        // GYNIMO PAAISKINIMAS PABAIGA: rikiavimo pasirinkimas
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

    // KODO PABAIGA: produktų filtro logika

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