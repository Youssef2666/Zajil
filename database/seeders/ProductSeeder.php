<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
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
                'image' => 'https://www.omen.com/content/dam/sites/omen/worldwide/laptops/omen-15-laptop/2-0/starmade-15-50-w-numpad-4-zone-oled-shadow-black-nt-h-dcam-non-odd-non-fpr-freedos-core-set-front-right-copy.png'
            ],
            [
                'product_category_id' => 4,
                'store_id' => 3,
                'name' => 'طاقم ذهب',
                'description' => 'طاقم ذهب',
                'price' => 1200,
                'stock' => 3,
                'image' => 'https://png.pngtree.com/png-clipart/20230417/original/pngtree-golden-necklace-jewelry-png-image_9059019.png'
            ],
            [
                'product_category_id' => 3,
                'store_id' => 2,
                'name' => 'IPhone 16',
                'description' => 'IPhone 16 From Dubai',
                'price' => 600,
                'stock' => 5,
                'image' => 'https://static1.xdaimages.com/wordpress/wp-content/uploads/2023/09/iphone-15-pro-max-render.png'
            ],
            [
                'product_category_id' => 2,
                'store_id' => 1,
                'name' => 'Ipad Air 2',
                'description' => 'لون ذهبي',
                'price' => 500,
                'stock' => 7,
                'image' => 'https://freepngimg.com/thumb/apple/68529-ipad-mini-apple-tablet-air-free-transparent-image-hq.png'
            ]
        ];

        foreach ($products as $product) {
            \App\Models\Product::create($product);
        }
    }
}
