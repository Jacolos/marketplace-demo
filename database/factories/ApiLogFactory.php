<?php

namespace Database\Factories;

use App\Models\ApiLog;
use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;

class ApiLogFactory extends Factory
{
    protected $model = ApiLog::class;

    public function definition()
    {
        $endpoints = [
            '/api/orders',
            '/api/products',
            '/api/customer/profile',
            '/api/orders/ORD-20240101-ABCD',
        ];

        $methods = ['GET', 'POST', 'PUT', 'DELETE'];
        $responseCodes = [200, 201, 400, 401, 404, 422, 500];

        return [
            'endpoint' => $this->faker->randomElement($endpoints),
            'method' => $this->faker->randomElement($methods),
            'ip_address' => $this->faker->ipv4(),
            'customer_id' => $this->faker->optional(0.8)->randomElement(Customer::pluck('id')->toArray()),
            'request_headers' => [
                'content-type' => 'application/json',
                'user-agent' => $this->faker->userAgent(),
            ],
            'request_body' => [
                'test' => 'data',
                'timestamp' => now()->toIso8601String(),
            ],
            'response_code' => $this->faker->randomElement($responseCodes),
            'response_body' => [
                'success' => true,
                'message' => 'Request processed',
            ],
            'response_time' => $this->faker->randomFloat(2, 10, 2000),
            'created_at' => $this->faker->dateTimeBetween('-30 days', 'now'),
        ];
    }

    public function success()
    {
        return $this->state(function (array $attributes) {
            return [
                'response_code' => $this->faker->randomElement([200, 201]),
                'response_body' => [
                    'success' => true,
                    'data' => [],
                ],
            ];
        });
    }

    public function error()
    {
        return $this->state(function (array $attributes) {
            return [
                'response_code' => $this->faker->randomElement([400, 401, 404, 422, 500]),
                'response_body' => [
                    'success' => false,
                    'message' => 'Error occurred',
                ],
            ];
        });
    }
}