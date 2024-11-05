<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class VariationOptionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $variationOptions = [
            [
                'variation_id' => 1,
                'value' => 'Red'
            ],
            [
                'variation_id' => 1,
                'value' => 'Blue'
            ],
            [
                'variation_id' => 1,
                'value' => 'White'
            ],
            [
                'variation_id' => 1,
                'value' => 'Black'
            ],

            [
                'variation_id' => 2,
                'value' => 's'
            ],
            [
                'variation_id' => 2,
                'value' => 'm'
            ],
            [
                'variation_id' => 2,
                'value' => 'l'
            ],
            [
                'variation_id' => 2,
                'value' => 'xl'
            ],
        ];
        foreach ($variationOptions as $variationOption) {
            \App\Models\VariationOption::create($variationOption);
        }
    }
}
