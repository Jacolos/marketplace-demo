<?php

namespace Database\Factories;

use App\Models\OrderItem;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderItemFactory extends Factory
{
    protected $model = OrderItem::class;

    public function definition()
    {
        $quantity = $this->faker->numberBetween(1, 20);
        $unitPrice = $this->faker->randomFloat(2, 10, 1000);
        $discountPercent = $this->faker->randomElement([0, 0, 5, 10]);
        $taxRate = 23;
        
        $discountedPrice = $unitPrice * (1 - $discountPercent / 100);
        $subtotal = $discountedPrice * $quantity;
        $taxAmount = $subtotal * ($taxRate / 100);
        $total = $subtotal + $taxAmount;

        return [
            'order_id' => Order::factory(),
            'product_id' => Product::factory(),
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'discount_percent' => $discountPercent,
            'tax_rate' => $taxRate,
            'subtotal' => $subtotal,
            'tax_amount' => $taxAmount,
            'total' => $total,
            'product_snapshot' => [
                'sku' => $this->faker->bothify('SKU-####'),
                'name' => $this->faker->words(3, true),
                'category' => $this->faker->word(),
                'description' => $this->faker->sentence(),
            ],
        ];
    }

    public function withProduct(Product $product)
    {
        return $this->state(function (array $attributes) use ($product) {
            return [
                'product_id' => $product->id,
                'unit_price' => $product->price,
                'product_snapshot' => [
                    'sku' => $product->sku,
                    'name' => $product->name,
                    'category' => $product->category->name ?? 'Unknown',
                    'description' => $product->description,
                ],
            ];
        });
    }

    public function withQuantity($quantity)
    {
        return $this->state(function (array $attributes) use ($quantity) {
            $unitPrice = $attributes['unit_price'];
            $discountPercent = $attributes['discount_percent'];
            $taxRate = $attributes['tax_rate'];
            
            $discountedPrice = $unitPrice * (1 - $discountPercent / 100);
            $subtotal = $discountedPrice * $quantity;
            $taxAmount = $subtotal * ($taxRate / 100);
            $total = $subtotal + $taxAmount;
            
            return [
                'quantity' => $quantity,
                'subtotal' => $subtotal,
                'tax_amount' => $taxAmount,
                'total' => $total,
            ];
        });
    }
}