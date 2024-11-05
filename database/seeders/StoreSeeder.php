<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class StoreSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $stores = [
            [
                'name' => 'العنقاء',
                'description' => 'متجر العنقاء',
                'latitude' => '32.87724846832993',
                'longitude' => '13.138964017917344',
                'user_id' => 1,
            ],
            [
                'name' => 'الهيرة',
                'description' => 'متجر الهيرة',
                'latitude' => '30.033333',
                'longitude' => '31.233333',
                'user_id' => 3,
            ],
            [
                'name' => 'زمرد',
                'description' => 'متجر زمرد للجواهر',
                'latitude' => '32.09375',
                'longitude' => '31.233333',
                'user_id' => 2,
            ]
            ];

        foreach ($stores as $store) {
            \App\Models\Store::create($store);
        }
    }
}
