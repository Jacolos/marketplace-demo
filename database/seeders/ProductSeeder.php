<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\Category;
use Faker\Factory as Faker;

class ProductSeeder extends Seeder
{
    public function run()
    {
        $faker = Faker::create('pl_PL');
        $categories = Category::all();

        // Produkty elektroniczne
        $electronics = Category::where('slug', 'elektronika')->first();
        if ($electronics) {
            $electronicsProducts = [
                ['name' => 'Laptop Dell XPS 13', 'price' => 5999.99, 'wholesale_price' => 5200.00],
                ['name' => 'Smartphone Samsung Galaxy S23', 'price' => 3999.99, 'wholesale_price' => 3500.00],
                ['name' => 'Słuchawki Sony WH-1000XM5', 'price' => 1599.99, 'wholesale_price' => 1400.00],
                ['name' => 'Tablet iPad Pro 11', 'price' => 4499.99, 'wholesale_price' => 4000.00],
                ['name' => 'Monitor LG UltraWide 34"', 'price' => 2299.99, 'wholesale_price' => 2000.00],
                ['name' => 'Klawiatura mechaniczna Logitech', 'price' => 499.99, 'wholesale_price' => 420.00],
                ['name' => 'Mysz gamingowa Razer', 'price' => 299.99, 'wholesale_price' => 250.00],
                ['name' => 'Webcam Logitech C920', 'price' => 349.99, 'wholesale_price' => 300.00],
                ['name' => 'Powerbank 20000mAh', 'price' => 149.99, 'wholesale_price' => 120.00],
                ['name' => 'Smartwatch Apple Watch 9', 'price' => 2199.99, 'wholesale_price' => 1900.00],
            ];

            foreach ($electronicsProducts as $product) {
                Product::create([
                    'category_id' => $electronics->id,
                    'sku' => 'ELE-' . strtoupper($faker->unique()->bothify('??###')),
                    'name' => $product['name'],
                    'description' => $faker->paragraph(3),
                    'price' => $product['price'],
                    'wholesale_price' => $product['wholesale_price'],
                    'stock_quantity' => $faker->numberBetween(0, 200),
                    'min_order_quantity' => $faker->randomElement([1, 5, 10]),
                    'unit' => 'szt',
                    'images' => [
                        'https://picsum.photos/400/400?random=' . $faker->unique()->numberBetween(1, 1000),
                        'https://picsum.photos/400/400?random=' . $faker->unique()->numberBetween(1, 1000),
                    ],
                    'attributes' => [
                        'gwarancja' => $faker->randomElement(['12 miesięcy', '24 miesiące', '36 miesięcy']),
                        'kolor' => $faker->randomElement(['Czarny', 'Biały', 'Srebrny', 'Szary']),
                    ],
                    'is_active' => true,
                    'is_featured' => $faker->boolean(30),
                ]);
            }
        }

        // Produkty żywnościowe
        $food = Category::where('slug', 'zywnosc')->first();
        if ($food) {
            $foodProducts = [
                ['name' => 'Kawa ziarnista Lavazza 1kg', 'price' => 89.99, 'wholesale_price' => 75.00],
                ['name' => 'Herbata Earl Grey 100 torebek', 'price' => 24.99, 'wholesale_price' => 20.00],
                ['name' => 'Oliwa z oliwek Extra Virgin 1L', 'price' => 49.99, 'wholesale_price' => 42.00],
                ['name' => 'Czekolada gorzka 70% 100g', 'price' => 12.99, 'wholesale_price' => 10.00],
                ['name' => 'Makaron penne 500g', 'price' => 8.99, 'wholesale_price' => 7.00],
                ['name' => 'Ryż basmati 1kg', 'price' => 15.99, 'wholesale_price' => 13.00],
                ['name' => 'Miód wielokwiatowy 500g', 'price' => 35.99, 'wholesale_price' => 30.00],
                ['name' => 'Orzechy włoskie 500g', 'price' => 39.99, 'wholesale_price' => 34.00],
            ];

            foreach ($foodProducts as $product) {
                Product::create([
                    'category_id' => $food->id,
                    'sku' => 'FOO-' . strtoupper($faker->unique()->bothify('??###')),
                    'name' => $product['name'],
                    'description' => $faker->paragraph(2),
                    'price' => $product['price'],
                    'wholesale_price' => $product['wholesale_price'],
                    'stock_quantity' => $faker->numberBetween(50, 500),
                    'min_order_quantity' => $faker->randomElement([1, 10, 20, 50]),
                    'unit' => $faker->randomElement(['szt', 'kg', 'opak']),
                    'is_active' => true,
                    'is_featured' => $faker->boolean(20),
                ]);
            }
        }

        // Generuj losowe produkty dla pozostałych kategorii
        foreach ($categories as $category) {
            if (!in_array($category->slug, ['elektronika', 'zywnosc'])) {
                for ($i = 0; $i < rand(5, 15); $i++) {
                    $price = $faker->randomFloat(2, 10, 1000);
                    Product::create([
                        'category_id' => $category->id,
                        'sku' => strtoupper(substr($category->slug, 0, 3)) . '-' . strtoupper($faker->unique()->bothify('??###')),
                        'name' => $faker->words(3, true),
                        'description' => $faker->paragraph(2),
                        'price' => $price,
                        'wholesale_price' => $price * 0.85,
                        'stock_quantity' => $faker->numberBetween(0, 300),
                        'min_order_quantity' => $faker->randomElement([1, 5, 10, 20]),
                        'unit' => $faker->randomElement(['szt', 'kg', 'opak', 'm', 'l']),
                        'is_active' => $faker->boolean(90),
                        'is_featured' => $faker->boolean(15),
                    ]);
                }
            }
        }
    }
}
