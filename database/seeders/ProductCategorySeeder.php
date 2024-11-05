<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Clothes'
            ],
            [
                'name' => 'Laptop',
            ],
            [
                'name' => 'Tablet',
            ],
            [
                'name' => 'Phone',
            ],
            [
                'name' => 'مجوهرات',
            ]
        ];

        foreach ($categories as $category) {    
            \App\Models\ProductCategory::create($category);
        }
    }
}
