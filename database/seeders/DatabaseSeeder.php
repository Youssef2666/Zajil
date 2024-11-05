<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory()->create([
            'name' => 'ربيع', 
            'email' => 'rabee@example.com',
        ]);
        
        User::factory()->create([
            'name' => 'أحمد', 
            'email' => 'ahmed@example.com',
        ]);

        User::factory()->create([
            'name' => 'سعود', 
            'email' => 'saoud@example.com',
        ]);
        
        User::factory(5)->create();
        
        $this->call([
            StoreSeeder::class,
            ProductCategorySeeder::class,
            ProductSeeder::class,
            ShipmentMethodSeeder::class,
        ]);
    }
}
