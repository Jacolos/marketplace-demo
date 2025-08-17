<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition()
    {
        $price = $this->faker->randomFloat(2, 10, 5000);
        
        return [
            'category_id' => Category::factory(),
            'sku' => strtoupper($this->faker->unique()->bothify('PRD-####-??')),
            'name' => $this->faker->words(3, true),
            'description' => $this->faker->paragraph(3),
            'price' => $price,
            'wholesale_price' => $price * 0.85,
            'stock_quantity' => $this->faker->numberBetween(0, 500),
            'min_order_quantity' => $this->faker->randomElement([1, 5, 10, 20]),
            'unit' => $this->faker->randomElement(['szt', 'kg', 'opak', 'm', 'l']),
            'images' => [
                $this->faker->imageUrl(400, 400, 'product'),
                $this->faker->imageUrl(400, 400, 'product'),
            ],
            'attributes' => [
                'color' => $this->faker->colorName(),
                'size' => $this->faker->randomElement(['S', 'M', 'L', 'XL']),
                'weight' => $this->faker->numberBetween(100, 5000) . 'g',
            ],
            'is_active' => true,
            'is_featured' => $this->faker->boolean(20),
            'views_count' => $this->faker->numberBetween(0, 1000),
        ];
    }

    public function inactive()
    {
        return $this->state(function (array $attributes) {
            return [
                'is_active' => false,
            ];
        });
    }

    public function featured()
    {
        return $this->state(function (array $attributes) {
            return [
                'is_featured' => true,
            ];
        });
    }

    public function outOfStock()
    {
        return $this->state(function (array $attributes) {
            return [
                'stock_quantity' => 0,
            ];
        });
    }
}