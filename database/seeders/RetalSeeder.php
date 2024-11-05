<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RetalSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $seeders = [
            [
                'name' => 'Amazon',
            ],
            [
                'name' => 'Ebay',
            ],
            [
                'name' => 'Shein',
            ],
            [
                'name' => 'Lazada',
            ],
            [
                'name' => 'Aliexpress',
            ],
            [
                'name' => 'Alibaba',
            ]
        ];

        foreach ($seeders as $seeder) {
            \App\Models\Retal::create($seeder);
        }
    }
}
