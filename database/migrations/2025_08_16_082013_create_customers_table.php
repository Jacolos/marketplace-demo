<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('company_name');
            $table->string('nip', 20)->unique();
            $table->string('email')->unique();
            $table->string('phone');
            $table->string('contact_person');
            $table->string('address');
            $table->string('city');
            $table->string('postal_code', 10);
            $table->string('country', 2)->default('PL');
            $table->decimal('credit_limit', 10, 2)->default(0);
            $table->decimal('current_balance', 10, 2)->default(0);
            $table->enum('customer_type', ['wholesale', 'retail', 'vip'])->default('wholesale');
            $table->integer('discount_percent')->default(0);
            $table->boolean('is_active')->default(true);
            $table->string('api_key')->nullable()->unique();
            $table->timestamp('last_order_at')->nullable();
            $table->timestamps();
            
            $table->index('nip');
            $table->index('email');
            $table->index('customer_type');
            $table->index('api_key');
        });
    }

    public function down()
    {
        Schema::dropIfExists('customers');
    }
};
