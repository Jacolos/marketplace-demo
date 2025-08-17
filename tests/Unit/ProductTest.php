<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Product;
use App\Models\Customer;
use App\Models\Category;

class ProductTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_calculates_price_for_wholesale_customer()
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'price' => 100,
            'wholesale_price' => 80,
        ]);

        $customer = Customer::factory()->create([
            'customer_type' => 'wholesale',
            'discount_percent' => 10,
        ]);

        $price = $product->getPriceForCustomer($customer);

        // Wholesale price 80 - 10% discount = 72
        $this->assertEquals(72, $price);
    }

    /** @test */
    public function it_calculates_price_for_retail_customer()
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'price' => 100,
            'wholesale_price' => 80,
        ]);

        $customer = Customer::factory()->create([
            'customer_type' => 'retail',
            'discount_percent' => 5,
        ]);

        $price = $product->getPriceForCustomer($customer);

        // Regular price 100 - 5% discount = 95
        $this->assertEquals(95, $price);
    }

    /** @test */
    public function it_decrements_stock_correctly()
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'stock_quantity' => 100,
        ]);

        $product->decrementStock(25);
        $this->assertEquals(75, $product->fresh()->stock_quantity);
    }

    /** @test */
    public function it_increments_stock_correctly()
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'stock_quantity' => 50,
        ]);

        $product->incrementStock(30);
        $this->assertEquals(80, $product->fresh()->stock_quantity);
    }

    /** @test */
    public function it_searches_products_by_name_sku_and_description()
    {
        $category = Category::factory()->create();
        
        Product::factory()->create([
            'category_id' => $category->id,
            'name' => 'Test Product Alpha',
            'sku' => 'SKU123',
            'description' => 'Lorem ipsum',
        ]);

        Product::factory()->create([
            'category_id' => $category->id,
            'name' => 'Another Product',
            'sku' => 'SKU456',
            'description' => 'Alpha description',
        ]);

        Product::factory()->create([
            'category_id' => $category->id,
            'name' => 'Different Item',
            'sku' => 'ALPHA789',
            'description' => 'Something else',
        ]);

        $results = Product::search('Alpha')->get();

        $this->assertCount(3, $results);
    }
}