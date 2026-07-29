`<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->foreignId('car_id')->constrained()->nullOnDelete();
            $table->foreignId('unit_id')->constrained()->nullOnDelete();
            $table->string('image')->nullable();
            $table->string('title');
            $table->decimal('cost_price', 12, 2);
            $table->decimal('markup', 12, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};