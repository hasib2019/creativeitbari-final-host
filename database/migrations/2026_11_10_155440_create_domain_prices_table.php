<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('domain_prices', function (Blueprint $table) {
            $table->id();
            $table->string('tld', 30)->unique();
            $table->decimal('register_price', 12, 2);
            $table->decimal('renew_price', 12, 2)->nullable();
            $table->decimal('transfer_price', 12, 2)->nullable();
            $table->boolean('is_popular')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('domain_prices');
    }
};
