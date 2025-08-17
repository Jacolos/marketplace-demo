<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Category;

class OrderTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_generates_unique_order_numbers()
    {
        $number1 = Order::generateOrderNumber();
        $number2 = Order::generateOrderNumber();
        
        $this->assertNotEquals($number1, $number2);
        $this->assertStringStartsWith('ORD-', $number1);
        $this->assertStringContainsString(date('Ymd'), $number1);
    }

    /** @test */
    public function it_calculates_order_totals_correctly()
    {
        $customer = Customer::factory()->create();
        $category = Category::factory()->create();
        
        $order = Order::factory()->create([
            'customer_id' => $customer->id,
            'shipping_cost' => 20,
            'discount_amount' => 50,
        ]);

        $product1 = Product::factory()->create([
            'category_id' => $category->id,
        ]);
        
        $product2 = Product::factory()->create([
            'category_id' => $category->id,
        ]);

        // Item 1: 100 zł * 2 szt = 200 zł + 23% VAT = 246 zł
        OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_id' => $product1->id,
            'quantity' => 2,
            'unit_price' => 100,
            'discount_percent' => 0,
            'tax_rate' => 23,
        ]);

        // Item 2: 50 zł * 3 szt = 150 zł + 23% VAT = 184.50 zł
        OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_id' => $product2->id,
            'quantity' => 3,
            'unit_price' => 50,
            'discount_percent' => 0,
            'tax_rate' => 23,
        ]);

        $order->calculateTotals();

        // Subtotal: 200 + 150 = 350
        $this->assertEquals(350, $order->subtotal);
        
        // Tax: 46 + 34.50 = 80.50
        $this->assertEquals(80.50, $order->tax_amount);
        
        // Total: 350 + 80.50 + 20 (shipping) - 50 (discount) = 400.50
        $this->assertEquals(400.50, $order->total_amount);
    }

    /** @test */
    public function it_can_cancel_pending_orders()
    {
        $customer = Customer::factory()->create();
        $order = Order::factory()->create([
            'customer_id' => $customer->id,
            'status' => 'pending',
        ]);

        $this->assertTrue($order->canBeCancelled());
        $this->assertTrue($order->cancel());
        $this->assertEquals('cancelled', $order->status);
    }

    /** @test */
    public function it_cannot_cancel_delivered_orders()
    {
        $customer = Customer::factory()->create();
        $order = Order::factory()->create([
            'customer_id' => $customer->id,
            'status' => 'delivered',
        ]);

        $this->assertFalse($order->canBeCancelled());
        $this->assertFalse($order->cancel());
        $this->assertEquals('delivered', $order->status);
    }

    /** @test */
    public function it_restores_stock_when_cancelled()
    {
        $customer = Customer::factory()->create();
        $category = Category::factory()->create();
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'stock_quantity' => 100,
        ]);

        $order = Order::factory()->create([
            'customer_id' => $customer->id,
            'status' => 'pending',
        ]);

        $orderItem = OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 10,
        ]);

        // Symuluj zmniejszenie stanu
        $product->decrementStock(10);
        $this->assertEquals(90, $product->fresh()->stock_quantity);

        // Anuluj zamówienie
        $order->cancel();

        // Stan powinien zostać przywrócony
        $this->assertEquals(100, $product->fresh()->stock_quantity);
    }
}