<?php

declare(strict_types=1);

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// ---------------------------------------------------------------------------
// Categories
// ---------------------------------------------------------------------------

it('lists root categories with children count', function () {
    Category::factory()->count(3)->create(['parent_id' => null, 'is_active' => true]);

    $this->getJson('/api/v1/categories')
        ->assertOk()
        ->assertJsonStructure(['data' => ['*' => ['id', 'name', 'slug']]])
        ->assertJsonCount(3, 'data');
});

it('shows a category with its children', function () {
    $parent = Category::factory()->create(['is_active' => true]);
    Category::factory()->count(2)->create(['parent_id' => $parent->id, 'is_active' => true]);

    $this->getJson("/api/v1/categories/{$parent->id}")
        ->assertOk()
        ->assertJsonStructure(['data' => ['id', 'name', 'children']])
        ->assertJsonCount(2, 'data.children');
});

it('returns 404 for an inactive category', function () {
    $category = Category::factory()->create(['is_active' => false]);

    $this->getJson("/api/v1/categories/{$category->id}")
        ->assertNotFound();
});

// ---------------------------------------------------------------------------
// Products — listing & filters
// ---------------------------------------------------------------------------

it('lists active products with pagination', function () {
    Category::factory()->create();
    Product::factory()->count(10)->create();

    $this->getJson('/api/v1/products')
        ->assertOk()
        ->assertJsonStructure([
            'data'       => ['*' => ['id', 'name', 'slug', 'price', 'quantity']],
            'pagination' => ['total', 'per_page', 'current_page', 'last_page'],
        ])
        ->assertJsonCount(10, 'data');
});

it('filters products by category', function () {
    $category = Category::factory()->create();
    Product::factory()->count(5)->create(['category_id' => $category->id]);
    Product::factory()->count(3)->create(); // other category

    $this->getJson("/api/v1/products?category_id={$category->id}")
        ->assertOk()
        ->assertJsonCount(5, 'data');
});

it('searches products by name', function () {
    Category::factory()->create();
    Product::factory()->create(['name' => 'Tênis Nike Air Max']);
    Product::factory()->count(3)->create();

    $this->getJson('/api/v1/products?search=Nike')
        ->assertOk()
        ->assertJsonCount(1, 'data');
});

it('filters products by price range', function () {
    Category::factory()->create();
    Product::factory()->create(['price' => 100.00]);
    Product::factory()->create(['price' => 500.00]);
    Product::factory()->create(['price' => 1000.00]);

    $this->getJson('/api/v1/products?min_price=200&max_price=800')
        ->assertOk()
        ->assertJsonCount(1, 'data');
});

it('filters in-stock products only', function () {
    Category::factory()->create();
    Product::factory()->count(3)->create(['quantity' => 5]);
    Product::factory()->count(2)->create(['quantity' => 0]);

    $this->getJson('/api/v1/products?in_stock=1')
        ->assertOk()
        ->assertJsonCount(3, 'data');
});

it('does not list inactive products', function () {
    Category::factory()->create();
    Product::factory()->count(2)->create(['is_active' => true]);
    Product::factory()->count(3)->create(['is_active' => false]);

    $this->getJson('/api/v1/products')
        ->assertOk()
        ->assertJsonCount(2, 'data');
});

// ---------------------------------------------------------------------------
// Products — detail & related
// ---------------------------------------------------------------------------

it('shows product detail with variations and skus', function () {
    Category::factory()->create();
    $product = Product::factory()->create();

    $this->getJson("/api/v1/products/{$product->id}")
        ->assertOk()
        ->assertJsonStructure([
            'data' => ['id', 'name', 'slug', 'description', 'price', 'category', 'variations', 'skus'],
        ]);
});

it('returns 404 for an inactive product', function () {
    Category::factory()->create();
    $product = Product::factory()->create(['is_active' => false]);

    $this->getJson("/api/v1/products/{$product->id}")
        ->assertNotFound();
});

it('returns related products from same category', function () {
    $category = Category::factory()->create();
    $product  = Product::factory()->create(['category_id' => $category->id]);
    Product::factory()->count(4)->create(['category_id' => $category->id]);
    Product::factory()->count(3)->create(); // other category

    $this->getJson("/api/v1/products/{$product->id}/related")
        ->assertOk()
        ->assertJsonCount(4, 'data');
});
