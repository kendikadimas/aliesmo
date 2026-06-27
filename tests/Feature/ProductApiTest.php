<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_products_list_is_paginated(): void
    {
        Product::factory(15)->create(['is_active' => true]);

        $response = $this->getJson('/api/v1/products');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [],
            'meta' => ['current_page', 'last_page', 'total'],
        ]);
    }

    public function test_products_can_be_filtered_by_category(): void
    {
        $category = Category::factory()->create(['slug' => 'electronics']);
        $otherCategory = Category::factory()->create(['slug' => 'clothing']);
        Product::factory(3)->create(['category_id' => $category->id, 'is_active' => true]);
        Product::factory(5)->create(['category_id' => $otherCategory->id, 'is_active' => true]);

        $response = $this->getJson('/api/v1/products?category=electronics');

        $response->assertStatus(200);
        $this->assertCount(3, $response->json('data'));
    }

    public function test_product_detail_by_slug(): void
    {
        $product = Product::factory()->create([
            'slug' => 'test-product',
            'is_active' => true,
        ]);

        $response = $this->getJson('/api/v1/products/test-product');

        $response->assertStatus(200);
        $response->assertJsonPath('data.slug', 'test-product');
    }

    public function test_inactive_product_is_not_listed(): void
    {
        Product::factory()->create([
            'is_active' => false,
            'slug' => 'inactive-product',
        ]);

        $response = $this->getJson('/api/v1/products');
        $this->assertCount(0, $response->json('data'));

        $response = $this->getJson('/api/v1/products/inactive-product');
        $response->assertStatus(404);
    }
}
