<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Customer;
use App\Models\Product;
use Faker\Factory as Faker;
use Carbon\Carbon;

class OrderSeeder extends Seeder
{
    public function run()
    {
        $faker = Faker::create('pl_PL');
        $customers = Customer::where('is_active', true)->get();
        $products = Product::where('is_active', true)->where('stock_quantity', '>', 0)->get();

        // Generuj zamówienia z ostatnich 90 dni
        for ($i = 0; $i < 100; $i++) {
            $customer = $customers->random();
            $orderDate = Carbon::now()->subDays(rand(0, 90));
            
            $statuses = ['pending', 'processing', 'shipped', 'delivered', 'cancelled'];
            $weights = [20, 25, 20, 30, 5]; // Wagi prawdopodobieństwa
            $status = $this->weightedRandom($statuses, $weights);
            
            $shippingAddress = [
                'street' => $faker->streetAddress,
                'city' => $faker->city,
                'postal_code' => $faker->postcode,
                'country' => 'PL',
            ];

            $order = Order::create([
                'order_number' => Order::generateOrderNumber(),
                'customer_id' => $customer->id,
                'status' => $status,
                'payment_status' => in_array($status, ['delivered', 'shipped']) ? 'paid' : 
                                   ($status === 'cancelled' ? 'unpaid' : $faker->randomElement(['paid', 'unpaid'])),
                'payment_method' => $faker->randomElement(['transfer', 'card', 'cash', 'deferred']),
                'shipping_address' => $shippingAddress,
                'billing_address' => $faker->boolean(80) ? $shippingAddress : [
                    'street' => $faker->streetAddress,
                    'city' => $faker->city,
                    'postal_code' => $faker->postcode,
                    'country' => 'PL',
                ],
                'shipping_cost' => $faker->randomElement([0, 15, 20, 25]),
                'discount_amount' => $faker->randomElement([0, 0, 0, 50, 100, 200]),
                'notes' => $faker->boolean(30) ? $faker->sentence : null,
                'created_at' => $orderDate,
                'updated_at' => $orderDate,
            ]);

            // Ustaw daty wysyłki i dostawy
            if (in_array($status, ['shipped', 'delivered'])) {
                $order->shipped_at = $orderDate->copy()->addDays(rand(1, 3));
                if ($status === 'delivered') {
                    $order->delivered_at = $order->shipped_at->copy()->addDays(rand(1, 5));
                }
                $order->save();
            }

            // Dodaj pozycje zamówienia (1-10 produktów)
            $itemCount = rand(1, 10);
            $selectedProducts = $products->random(min($itemCount, $products->count()));
            
            foreach ($selectedProducts as $product) {
                $quantity = rand(1, min(20, $product->stock_quantity));
                $unitPrice = $product->getPriceForCustomer($customer);
                $discountPercent = $faker->randomElement([0, 0, 0, 5, 10]);
                
                $orderItem = OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'discount_percent' => $discountPercent,
                    'tax_rate' => 23,
                    'product_snapshot' => [
                        'sku' => $product->sku,
                        'name' => $product->name,
                        'category' => $product->category->name,
                    ],
                    'created_at' => $orderDate,
                    'updated_at' => $orderDate,
                ]);
            }

            // Przelicz totale
            $order->calculateTotals();
            
            // Aktualizuj bilans klienta dla niezapłaconych zamówień
            if ($order->payment_status === 'unpaid' && !in_array($status, ['cancelled'])) {
                $customer->updateBalance($order->total_amount);
            }
        }

        // Dodaj kilka zamówień z dzisiaj
        for ($i = 0; $i < 5; $i++) {
            $customer = $customers->random();
            
            $shippingAddress = [
                'street' => $faker->streetAddress,
                'city' => $faker->city,
                'postal_code' => $faker->postcode,
                'country' => 'PL',
            ];

            $order = Order::create([
                'order_number' => Order::generateOrderNumber(),
                'customer_id' => $customer->id,
                'status' => 'pending',
                'payment_status' => 'unpaid',
                'payment_method' => $faker->randomElement(['transfer', 'card']),
                'shipping_address' => $shippingAddress,
                'billing_address' => $shippingAddress,
                'shipping_cost' => 20,
                'discount_amount' => 0,
                'notes' => 'Zamówienie testowe z seedera',
            ]);

            // Dodaj 2-5 produktów
            $selectedProducts = $products->random(rand(2, 5));
            
            foreach ($selectedProducts as $product) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'quantity' => rand(1, 5),
                    'unit_price' => $product->getPriceForCustomer($customer),
                    'discount_percent' => 0,
                    'tax_rate' => 23,
                    'product_snapshot' => [
                        'sku' => $product->sku,
                        'name' => $product->name,
                        'category' => $product->category->name,
                    ],
                ]);
            }

            $order->calculateTotals();
        }
    }

    private function weightedRandom($items, $weights)
    {
        $totalWeight = array_sum($weights);
        $random = rand(1, $totalWeight);
        
        foreach ($items as $index => $item) {
            $random -= $weights[$index];
            if ($random <= 0) {
                return $item;
            }
        }
        
        return $items[0];
    }
}