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
                'name' => 'ملابس'
            ],
            [
                'name' => 'الكترونات',
            ],
            [
                'name' => 'أحذية',
                'parent_id' => 1
            ],
            [
                'name' => 'حقائب',
                'parent_id' => 1
            ],
            [
                'name' => 'جوارب',
                'parent_id' => 1
            ],
            [
                'name' => 'Laptop',
                'parent_id' => 2
            ],
            [
                'name' => 'Tablet',
                'parent_id' => 2
            ],
            [
                'name' => 'Phone',
                'parent_id' => 2
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
