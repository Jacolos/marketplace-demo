<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->unsignedBigInteger('subtotal')->default(0)->change();
            $table->unsignedBigInteger('tax_amount')->default(0)->change();
            $table->unsignedBigInteger('shipping_cost')->default(0)->change();
            $table->unsignedBigInteger('discount_amount')->default(0)->change();
            $table->unsignedBigInteger('total_amount')->default(0)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            //
        });
    }
};
