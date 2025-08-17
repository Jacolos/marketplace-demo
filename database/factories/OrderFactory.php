<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition()
    {
        $subtotal = $this->faker->randomFloat(2, 100, 10000);
        $taxAmount = $subtotal * 0.23;
        $shippingCost = $this->faker->randomElement([0, 15, 20, 25]);
        $discountAmount = $this->faker->randomElement([0, 0, 50, 100]);
        $totalAmount = $subtotal + $taxAmount + $shippingCost - $discountAmount;

        $shippingAddress = [
            'street' => $this->faker->streetAddress(),
            'city' => $this->faker->city(),
            'postal_code' => $this->faker->postcode(),
            'country' => 'PL',
        ];

        return [
            'order_number' => Order::generateOrderNumber(),
            'customer_id' => Customer::factory(),
            'status' => $this->faker->randomElement(['pending', 'processing', 'shipped', 'delivered']),
            'payment_status' => $this->faker->randomElement(['unpaid', 'paid']),
            'payment_method' => $this->faker->randomElement(['transfer', 'card', 'cash', 'deferred']),
            'subtotal' => $subtotal,
            'tax_amount' => $taxAmount,
            'shipping_cost' => $shippingCost,
            'discount_amount' => $discountAmount,
            'total_amount' => $totalAmount,
            'notes' => $this->faker->optional()->sentence(),
            'shipping_address' => $shippingAddress,
            'billing_address' => $this->faker->boolean(80) ? $shippingAddress : [
                'street' => $this->faker->streetAddress(),
                'city' => $this->faker->city(),
                'postal_code' => $this->faker->postcode(),
                'country' => 'PL',
            ],
            'external_id' => $this->faker->optional()->uuid(),
            'shipped_at' => null,
            'delivered_at' => null,
        ];
    }

    public function pending()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'pending',
                'payment_status' => 'unpaid',
                'shipped_at' => null,
                'delivered_at' => null,
            ];
        });
    }

    public function processing()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'processing',
                'shipped_at' => null,
                'delivered_at' => null,
            ];
        });
    }

    public function shipped()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'shipped',
                'payment_status' => 'paid',
                'shipped_at' => now()->subDays(rand(1, 5)),
                'delivered_at' => null,
            ];
        });
    }

    public function delivered()
    {
        return $this->state(function (array $attributes) {
            $shippedAt = now()->subDays(rand(5, 10));
            return [
                'status' => 'delivered',
                'payment_status' => 'paid',
                'shipped_at' => $shippedAt,
                'delivered_at' => $shippedAt->addDays(rand(1, 3)),
            ];
        });
    }

    public function cancelled()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'cancelled',
                'payment_status' => 'unpaid',
                'shipped_at' => null,
                'delivered_at' => null,
            ];
        });
    }

    public function paid()
    {
        return $this->state(function (array $attributes) {
            return [
                'payment_status' => 'paid',
            ];
        });
    }

    public function unpaid()
    {
        return $this->state(function (array $attributes) {
            return [
                'payment_status' => 'unpaid',
            ];
        });
    }
}
