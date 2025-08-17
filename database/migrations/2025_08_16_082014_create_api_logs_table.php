<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('api_logs', function (Blueprint $table) {
            $table->id();
            $table->string('endpoint');
            $table->string('method', 10);
            $table->string('ip_address', 45);
            $table->foreignId('customer_id')->nullable()->constrained();
            $table->json('request_headers')->nullable();
            $table->json('request_body')->nullable();
            $table->integer('response_code');
            $table->json('response_body')->nullable();
            $table->float('response_time'); // w milisekundach
            $table->timestamp('created_at');
            
            $table->index('endpoint');
            $table->index('customer_id');
            $table->index('created_at');
        });
    }

    public function down()
    {
        Schema::dropIfExists('api_logs');
    }
};
