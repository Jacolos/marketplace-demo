<?php

namespace Database\Factories;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class CustomerFactory extends Factory
{
    protected $model = Customer::class;

    public function definition()
    {
        return [
            'company_name' => $this->faker->company(),
            'nip' => $this->faker->unique()->numerify('##########'),
            'email' => $this->faker->unique()->companyEmail(),
            'phone' => $this->faker->phoneNumber(),
            'contact_person' => $this->faker->name(),
            'address' => $this->faker->streetAddress(),
            'city' => $this->faker->city(),
            'postal_code' => $this->faker->postcode(),
            'country' => 'PL',
            'credit_limit' => $this->faker->randomElement([5000, 10000, 20000, 50000]),
            'current_balance' => 0,
            'customer_type' => $this->faker->randomElement(['wholesale', 'retail', 'vip']),
            'discount_percent' => $this->faker->numberBetween(0, 15),
            'is_active' => true,
            'api_key' => Str::random(32),
            'last_order_at' => $this->faker->optional()->dateTimeBetween('-1 year', 'now'),
        ];
    }

    public function vip()
    {
        return $this->state(function (array $attributes) {
            return [
                'customer_type' => 'vip',
                'credit_limit' => $this->faker->randomElement([50000, 75000, 100000]),
                'discount_percent' => $this->faker->numberBetween(10, 20),
            ];
        });
    }

    public function wholesale()
    {
        return $this->state(function (array $attributes) {
            return [
                'customer_type' => 'wholesale',
                'credit_limit' => $this->faker->randomElement([20000, 30000, 40000]),
                'discount_percent' => $this->faker->numberBetween(5, 10),
            ];
        });
    }

    public function retail()
    {
        return $this->state(function (array $attributes) {
            return [
                'customer_type' => 'retail',
                'credit_limit' => $this->faker->randomElement([5000, 10000]),
                'discount_percent' => $this->faker->numberBetween(0, 5),
            ];
        });
    }

    public function inactive()
    {
        return $this->state(function (array $attributes) {
            return [
                'is_active' => false,
            ];
        });
    }

    public function withBalance($balance)
    {
        return $this->state(function (array $attributes) use ($balance) {
            return [
                'current_balance' => $balance,
            ];
        });
    }
}