<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Categorias com suporte a árvore (nested set)
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->unsignedInteger('_lft')->nullable();
            $table->unsignedInteger('_rgt')->nullable();
            $table->unsignedInteger('_depth')->default(0);
            $table->string('image_url')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('parent_id')
                ->references('id')
                ->on('categories')
                ->nullOnDelete();
            $table->index(['_lft', '_rgt']);
        });

        // Produtos
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description');
            $table->text('short_description')->nullable();
            $table->decimal('price', 10, 2);
            $table->decimal('cost_price', 10, 2)->nullable();
            $table->string('sku')->unique();
            $table->integer('quantity')->default(0);
            $table->integer('min_quantity_alert')->default(10);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('slug');
            $table->index('sku');
        });

        // Variações de produto (tamanho, cor, etc)
        Schema::create('product_variations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('name'); // ex: Size, Color
            $table->timestamps();
        });

        // Valores das variações
        Schema::create('product_variation_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_variation_id')->constrained()->cascadeOnDelete();
            $table->string('value'); // ex: M, Red
            $table->string('label')->nullable();
            $table->timestamps();
        });

        // SKUs com variações
        Schema::create('product_skus', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('sku')->unique();
            $table->decimal('price', 10, 2)->nullable();
            $table->integer('quantity')->default(0);
            $table->json('variation_values'); // { variation_id: value_id }
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('sku');
        });

        // Preços e promoções
        Schema::create('product_prices', function (Blueprint $table) {
            $table->id();
            $table->morphs('priceable'); // product or product_sku
            $table->decimal('regular_price', 10, 2);
            $table->decimal('sale_price', 10, 2)->nullable();
            $table->timestamp('sale_starts_at')->nullable();
            $table->timestamp('sale_ends_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_prices');
        Schema::dropIfExists('product_skus');
        Schema::dropIfExists('product_variation_values');
        Schema::dropIfExists('product_variations');
        Schema::dropIfExists('products');
        Schema::dropIfExists('categories');
    }
};
