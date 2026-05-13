<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        // jei produktų jau yra – nelendam
        if (Product::count() > 0) {
            return;
        }

        $kubilai = Category::where('slug', 'kubilai')->first();
        $priedai = Category::where('slug', 'nameliu-priedai')->first();
        $pirtis  = Category::where('slug', 'pirties-iranga')->first();
        $kiti    = Category::where('slug', 'kiti-gaminiai')->first();

        $fallbackCategoryId = Category::first()?->id;

        Product::create([
            'category_id' => $kubilai?->id ?? $fallbackCategoryId,
            'name' => 'Ąžuolinis kubilas 4–6 asmenims',
            'slug' => 'azuolinis-kubilas-4-6-asmenims',
            'description' => 'Rankų darbo ąžuolinis kubilas su krosnele. Tinka sodybai ar namams.',
            'price' => 1299.00,
            'stock' => 3,
            'is_active' => 1,
            'image' => 'https://picsum.photos/seed/kubilas/900/600',
        ]);

        Product::create([
            'category_id' => $kubilai?->id ?? $fallbackCategoryId,
            'name' => 'Kubilas su LED apšvietimu',
            'slug' => 'kubilas-su-led-apsvietimu',
            'description' => 'Kubilas su LED apšvietimu ir patogia apdaila.',
            'price' => 1599.00,
            'stock' => 2,
            'is_active' => 1,
            'image' => 'https://picsum.photos/seed/ledkubilas/900/600',
        ]);

        Product::create([
            'category_id' => $priedai?->id ?? $fallbackCategoryId,
            'name' => 'Termo dangtis kubilui',
            'slug' => 'termo-dangtis-kubilui',
            'description' => 'Padeda išlaikyti šilumą ir apsaugo kubilą nuo nešvarumų.',
            'price' => 149.00,
            'stock' => 12,
            'is_active' => 1,
            'image' => 'https://picsum.photos/seed/dangtis/900/600',
        ]);

        Product::create([
            'category_id' => $pirtis?->id ?? $fallbackCategoryId,
            'name' => 'Pirties krosnelė (demo)',
            'slug' => 'pirties-krosnele-demo',
            'description' => 'Demo prekė pirties įrangos kategorijai. Vėliau pakeisi realia.',
            'price' => 499.00,
            'stock' => 5,
            'is_active' => 1,
            'image' => 'https://picsum.photos/seed/krosnele/900/600',
        ]);
    }
}