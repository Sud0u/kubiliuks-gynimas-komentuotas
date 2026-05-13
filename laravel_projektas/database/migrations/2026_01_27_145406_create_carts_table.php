<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('carts', function (Blueprint $table) {
            $table->id();

            // cart priklauso vartotojui
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            // active - dabartinis krepselis, ordered - jau paverstas uzsakymu
            $table->string('status')->default('active');

            $table->timestamps();

            // vienas aktyvus krepselis vienam vartotojui
            $table->unique(['user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('carts');
    }
};
