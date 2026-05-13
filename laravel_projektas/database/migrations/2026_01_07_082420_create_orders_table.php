<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            $table->string('customer_name');
            $table->string('customer_email');
            $table->string('customer_phone')->nullable();

            $table->string('shipping_address');
            $table->string('shipping_city')->nullable();
            $table->string('shipping_postcode')->nullable();
            $table->string('shipping_country')->nullable();

            $table->decimal('total_amount', 10, 2)->default(0);

            $table->string('status')->default('pending');
            $table->timestamp('paid_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};