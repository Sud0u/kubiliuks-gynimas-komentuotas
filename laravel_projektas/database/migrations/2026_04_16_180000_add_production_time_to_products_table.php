<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('products', 'production_time')) {
            Schema::table('products', function (Blueprint $table) {
                $table->string('production_time', 255)->nullable()->after('description');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('products', 'production_time')) {
            Schema::table('products', function (Blueprint $table) {
                $table->dropColumn('production_time');
            });
        }
    }
};
