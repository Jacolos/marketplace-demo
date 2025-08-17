<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Customer;
use Faker\Factory as Faker;
use Illuminate\Support\Str;

class CustomerSeeder extends Seeder
{
    public function run()
    {
        $faker = Faker::create('pl_PL');

        // Klienci VIP
        $vipCustomers = [
            [
                'company_name' => 'Hurtownia Elektroniczna MEGA',
                'nip' => '5252525252',
                'email' => 'zamowienia@mega-hurt.pl',
                'customer_type' => 'vip',
                'credit_limit' => 100000,
                'discount_percent' => 15,
            ],
            [
                'company_name' => 'Sklep Internetowy TechnoShop',
                'nip' => '6363636363',
                'email' => 'zakupy@technoshop.pl',
                'customer_type' => 'vip',
                'credit_limit' => 75000,
                'discount_percent' => 12,
            ],
        ];

        foreach ($vipCustomers as $customer) {
            Customer::create(array_merge($customer, [
                'phone' => $faker->phoneNumber,
                'contact_person' => $faker->name,
                'address' => $faker->streetAddress,
                'city' => $faker->city,
                'postal_code' => $faker->postcode,
                'country' => 'PL',
                'current_balance' => $faker->randomFloat(2, 0, $customer['credit_limit'] * 0.5),
                'is_active' => true,
                'api_key' => Str::random(32),
            ]));
        }

        // Klienci hurtowi
        for ($i = 0; $i < 20; $i++) {
            Customer::create([
                'company_name' => $faker->company,
                'nip' => $faker->unique()->numerify('##########'),
                'email' => $faker->unique()->companyEmail,
                'phone' => $faker->phoneNumber,
                'contact_person' => $faker->name,
                'address' => $faker->streetAddress,
                'city' => $faker->city,
                'postal_code' => $faker->postcode,
                'country' => 'PL',
                'credit_limit' => $faker->randomElement([10000, 20000, 30000, 50000]),
                'current_balance' => $faker->randomFloat(2, 0, 10000),
                'customer_type' => 'wholesale',
                'discount_percent' => $faker->randomElement([0, 5, 8, 10]),
                'is_active' => $faker->boolean(90),
                'api_key' => Str::random(32),
            ]);
        }

        // Klienci detaliczni
        for ($i = 0; $i < 10; $i++) {
            Customer::create([
                'company_name' => $faker->company,
                'nip' => $faker->unique()->numerify('##########'),
                'email' => $faker->unique()->companyEmail,
                'phone' => $faker->phoneNumber,
                'contact_person' => $faker->name,
                'address' => $faker->streetAddress,
                'city' => $faker->city,
                'postal_code' => $faker->postcode,
                'country' => 'PL',
                'credit_limit' => $faker->randomElement([5000, 10000]),
                'current_balance' => $faker->randomFloat(2, 0, 5000),
                'customer_type' => 'retail',
                'discount_percent' => $faker->randomElement([0, 3, 5]),
                'is_active' => true,
                'api_key' => Str::random(32),
            ]);
        }
    }
}
