<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run()
    {
        $categories = [
            ['name' => 'Elektronika', 'description' => 'Sprzęt elektroniczny i akcesoria'],
            ['name' => 'Odzież', 'description' => 'Odzież damska, męska i dziecięca'],
            ['name' => 'Żywność', 'description' => 'Produkty spożywcze i napoje'],
            ['name' => 'Dom i Ogród', 'description' => 'Artykuły do domu i ogrodu'],
            ['name' => 'Sport', 'description' => 'Sprzęt sportowy i fitness'],
            ['name' => 'Książki', 'description' => 'Książki, e-booki i audiobooki'],
            ['name' => 'Zabawki', 'description' => 'Zabawki dla dzieci'],
            ['name' => 'Zdrowie', 'description' => 'Produkty zdrowotne i suplementy'],
            ['name' => 'Motoryzacja', 'description' => 'Części i akcesoria samochodowe'],
            ['name' => 'Biuro', 'description' => 'Artykuły biurowe i papiernicze'],
        ];

        foreach ($categories as $index => $category) {
            Category::create([
                'name' => $category['name'],
                'slug' => Str::slug($category['name']),
                'description' => $category['description'],
                'is_active' => true,
                'sort_order' => $index + 1,
            ]);
        }
    }
}