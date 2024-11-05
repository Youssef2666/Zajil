<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = [
            [
                'product_category_id' => 1,
                'store_id' => 1,
                'name' => 'Laptop',
                'description' => 'HP OMEN 15',
                'price' => 200,
                'stock' => 10,
            ],
            [
                'product_category_id' => 4,
                'store_id' => 3,
                'name' => 'طاقم ذهب',
                'description' => 'طاقم ذهب',
                'price' => 1200,
                'stock' => 3,
            ],
            [
                'product_category_id' => 3,
                'store_id' => 2,
                'name' => 'IPhone 16',
                'description' => 'لون اسود',
                'price' => 600,
                'stock' => 5,
            ],
            [
                'product_category_id' => 2,
                'store_id' => 1,
                'name' => 'Ipad Air 2',
                'description' => 'لون ذهبي',
                'price' => 500,
                'stock' => 7,
            ]
        ];

        foreach ($products as $product) {
            \App\Models\Product::create($product);
        }
    }
}
