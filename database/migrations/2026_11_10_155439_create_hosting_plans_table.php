<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hosting_plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('type', 30)->default('shared')->index();
            $table->string('short_description', 500)->nullable();
            $table->decimal('monthly_price', 12, 2)->nullable();
            $table->decimal('yearly_price', 12, 2)->nullable();
            $table->json('features')->nullable();
            $table->string('order_url')->nullable();
            $table->boolean('is_popular')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hosting_plans');
    }
};
