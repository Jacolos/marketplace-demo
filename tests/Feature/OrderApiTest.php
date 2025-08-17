<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Order;
use App\Models\Category;

class OrderApiTest extends TestCase
{
    use RefreshDatabase;

    private $customer;
    private $apiKey;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Utwórz testowego klienta
        $this->customer = Customer::factory()->create([
            'is_active' => true,
            'credit_limit' => 10000,
            'current_balance' => 0,
        ]);
        $this->apiKey = $this->customer->api_key;
    }

    /** @test */
    public function it_requires_api_key_for_authentication()
    {
        $response = $this->getJson('/api/orders');
        
        $response->assertStatus(401)
            ->assertJson([
                'success' => false,
                'message' => 'API key is required',
            ]);
    }

    /** @test */
    public function it_authenticates_with_valid_api_key()
    {
        $response = $this->withHeaders([
            'X-API-Key' => $this->apiKey,
        ])->getJson('/api/orders');
        
        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);
    }

    /** @test */
    public function it_can_create_order_via_api()
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'stock_quantity' => 100,
            'price' => 100,
        ]);

        $orderData = [
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 5,
                ],
            ],
            'shipping_address' => [
                'street' => 'ul. Testowa 123',
                'city' => 'Warszawa',
                'postal_code' => '00-001',
            ],
            'payment_method' => 'transfer',
            'notes' => 'Test order',
        ];

        $response = $this->withHeaders([
            'X-API-Key' => $this->apiKey,
        ])->postJson('/api/orders', $orderData);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Order created successfully',
            ]);

        $this->assertDatabaseHas('orders', [
            'customer_id' => $this->customer->id,
            'status' => 'pending',
        ]);

        // Sprawdź czy stan magazynowy został zmniejszony
        $product->refresh();
        $this->assertEquals(95, $product->stock_quantity);

    }

    /** @test */
    public function it_validates_order_items_are_required()
    {
        $orderData = [
            'items' => [],
            'shipping_address' => [
                'street' => 'ul. Testowa 123',
                'city' => 'Warszawa',
                'postal_code' => '00-001',
            ],
        ];

        $response = $this->withHeaders([
            'X-API-Key' => $this->apiKey,
        ])->postJson('/api/orders', $orderData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['items']);
    }

    /** @test */
    public function it_checks_stock_availability()
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'stock_quantity' => 5,
        ]);

        $orderData = [
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 10, // Więcej niż dostępne
                ],
            ],
            'shipping_address' => [
                'street' => 'ul. Testowa 123',
                'city' => 'Warszawa',
                'postal_code' => '00-001',
            ],
        ];

        $response = $this->withHeaders([
            'X-API-Key' => $this->apiKey,
        ])->postJson('/api/orders', $orderData);

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
            ]);
    }

    /** @test */
    public function it_checks_credit_limit()
    {
        $this->customer->update([
            'credit_limit' => 100,
            'current_balance' => 90,
        ]);

        $category = Category::factory()->create();
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'stock_quantity' => 100,
            'price' => 100, // Całkowita wartość przekroczy limit
        ]);

        $orderData = [
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 1,
                ],
            ],
            'shipping_address' => [
                'street' => 'ul. Testowa 123',
                'city' => 'Warszawa',
                'postal_code' => '00-001',
            ],
        ];

        $response = $this->withHeaders([
            'X-API-Key' => $this->apiKey,
        ])->postJson('/api/orders', $orderData);

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
                'message' => 'Credit limit exceeded',
            ]);
    }

    /** @test */
    public function it_can_list_customer_orders()
    {
        Order::factory()->count(5)->create([
            'customer_id' => $this->customer->id,
        ]);

        $response = $this->withHeaders([
            'X-API-Key' => $this->apiKey,
        ])->getJson('/api/orders');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonCount(5, 'data.data');
    }

    /** @test */
    public function it_can_cancel_pending_order()
    {
        $order = Order::factory()->create([
            'customer_id' => $this->customer->id,
            'status' => 'pending',
        ]);

        $response = $this->withHeaders([
            'X-API-Key' => $this->apiKey,
        ])->deleteJson("/api/orders/{$order->order_number}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Order cancelled successfully',
            ]);

        $order->refresh();
        $this->assertEquals('cancelled', $order->status);
    }

    /** @test */
    public function it_cannot_cancel_delivered_order()
    {
        $order = Order::factory()->create([
            'customer_id' => $this->customer->id,
            'status' => 'delivered',
        ]);

        $response = $this->withHeaders([
            'X-API-Key' => $this->apiKey,
        ])->deleteJson("/api/orders/{$order->order_number}");

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
                'message' => 'This order cannot be cancelled',
            ]);
    }
}