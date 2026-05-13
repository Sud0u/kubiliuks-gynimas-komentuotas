<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Jei anksčiau buvo tik title – pridedam name (kad veiktų katalogas + admin panelė).
            if (!Schema::hasColumn('products', 'name')) {
                $table->string('name')->nullable()->after('id');
            }

            if (!Schema::hasColumn('products', 'slug')) {
                $table->string('slug')->nullable()->unique()->after('name');
            }

            if (!Schema::hasColumn('products', 'category_id')) {
                $table->foreignId('category_id')
                    ->nullable()
                    ->constrained()
                    ->nullOnDelete()
                    ->after('slug');
            }

            if (!Schema::hasColumn('products', 'stock')) {
                $table->unsignedInteger('stock')->default(0)->after('price');
            }

            if (!Schema::hasColumn('products', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('stock');
            }
        });

        // Jei turėjai senus įrašus su "title", perkeliam į "name" (tik jei name dar tuščias).
        if (Schema::hasColumn('products', 'title') && Schema::hasColumn('products', 'name')) {
            DB::table('products')
                ->whereNull('name')
                ->update(['name' => DB::raw('title')]);
        }

        // Slug generavimo čia nedarom masiškai – adminas įves/sugeneruos kuriant.
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {

            // drop FK pirmiau
            if (Schema::hasColumn('products', 'category_id')) {
                try {
                    $table->dropForeign(['category_id']);
                } catch (\Throwable $e) {
                    // jei FK jau kitoks / nėra – ignoruojam
                }
                $table->dropColumn('category_id');
            }

            foreach (['is_active', 'stock', 'slug', 'name'] as $col) {
                if (Schema::hasColumn('products', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
