<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => env('ADMIN_EMAIL', 'admin@example.com')],
            [
                'name' => env('ADMIN_NAME', 'Administratorius'),
                'password' => Hash::make(env('ADMIN_PASSWORD', 'ChangeMe123!')),
                'is_admin' => 1,
            ]
        );

        $this->call([
            CategorySeeder::class,
            ProductSeeder::class,
        ]);
    }
}