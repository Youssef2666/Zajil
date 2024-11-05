<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ShipmentMethodSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $shipmentMethods = [
            [
                'name' => 'Plane',
                'price' => 80.00
            ],
            [
                'name' => 'Ship',
                'price' => 30.00
            ],
            [
                'name' => 'Delivery',
                'price' => 0.00
            ],
        ];
        foreach ($shipmentMethods as $shipmentMethod) {
            \App\Models\ShipmentMethod::create($shipmentMethod);
        }
    }
}
