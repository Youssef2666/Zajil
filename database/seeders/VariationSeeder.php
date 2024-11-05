<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class VariationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $variations = [
            [
                'name' => 'color',
                'product_category_id' => 1
            ],
            [
                'name' => 'size',
                'product_category_id' => 1
            ]
        ];

        foreach ($variations as $variation) {
            \App\Models\Variation::create($variation);
        }
    }
}
