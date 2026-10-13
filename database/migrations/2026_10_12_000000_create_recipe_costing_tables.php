<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('app_settings', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->string('value');
        });

        Schema::create('exchange_rates', function (Blueprint $table) {
            $table->id();
            $table->string('from_currency', 3);
            $table->string('to_currency', 3);
            $table->decimal('rate', 18, 8);
            $table->string('source')->default('manual');
            $table->timestamp('effective_at');
            $table->timestamps();
            $table->index(['from_currency', 'to_currency', 'effective_at']);
        });

        Schema::create('ingredients', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('base_unit', 8);
            $table->decimal('package_quantity', 12, 4)->default(1);
            $table->string('package_unit', 8);
            $table->decimal('purchase_price', 14, 4);
            $table->string('purchase_currency', 3)->default('VES');
            $table->text('notes')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        Schema::create('recipes', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('yield_quantity', 12, 4);
            $table->string('yield_unit', 24)->default('unidades');
            $table->text('preparation_notes')->nullable();
            $table->timestamps();
        });

        Schema::create('recipe_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('recipe_id')->constrained()->cascadeOnDelete();
            $table->foreignId('ingredient_id')->constrained()->restrictOnDelete();
            $table->decimal('quantity', 12, 4);
            $table->unsignedSmallInteger('position')->default(0);
            $table->timestamps();
            $table->index(['recipe_id', 'position']);
        });

        DB::table('app_settings')->insert([
            'key' => 'base_currency',
            'value' => 'VES',
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('recipe_items');
        Schema::dropIfExists('recipes');
        Schema::dropIfExists('ingredients');
        Schema::dropIfExists('exchange_rates');
        Schema::dropIfExists('app_settings');
    }
};
