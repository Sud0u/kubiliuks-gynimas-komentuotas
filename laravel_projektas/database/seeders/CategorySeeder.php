<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            'Kubilai',
            'Namelių priedai',
            'Pirties įranga',
            'Kiti gaminiai',
        ];

        foreach ($items as $name) {
            Category::updateOrCreate(
                ['name' => $name],
                ['slug' => str($name)->slug()]
            );
        }
    }
}