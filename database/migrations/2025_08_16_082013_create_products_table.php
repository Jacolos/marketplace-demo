<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->onDelete('cascade');
            $table->string('sku')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2);
            $table->decimal('wholesale_price', 10, 2)->nullable();
            $table->integer('stock_quantity')->default(0);
            $table->integer('min_order_quantity')->default(1);
            $table->string('unit')->default('szt');
            $table->json('images')->nullable();
            $table->json('attributes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->integer('views_count')->default(0);
            $table->timestamps();
            
            $table->index('sku');
            $table->index(['category_id', 'is_active']);
            $table->index('is_featured');
            $table->fullText(['name', 'description']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('products');
    }
};
