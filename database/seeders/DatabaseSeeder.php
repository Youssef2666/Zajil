<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory()->create([
            'name' => 'ربيع', 
            'email' => 'rabee@gmail.com',
            'password' => Hash::make('12345678'),

        ]);
        
        User::factory()->create([
            'name' => 'أحمد', 
            'email' => 'ahmed@gmail.com',
            'password' => Hash::make('12345678'),

        ]);
        User::factory()->create([
            'name' => 'سعود', 
            'email' => 'saoud@gmail.com',
            'password' => Hash::make('12345678'),
        ]);

        User::factory()->create([
            'name' => 'يوسف', 
            'email' => 'youssef@gmail.com',
            'password' => Hash::make('12345678'),
        ]);
        User::factory()->create([
            'name' => 'حمزة', 
            'email' => 'hamza@gmail.com',
            'password' => Hash::make('12345678'),
        ]);
        User::factory()->create([
            'name' => 'الزابطي', 
            'email' => 'alzabati@gmail.com',
            'password' => Hash::make('12345678'),
        ]);
        User::factory()->create([
            'name' => 'ابراهيم', 
            'email' => 'ibrahim@gmail.com',
            'password' => Hash::make('12345678'),
        ]);
        User::factory()->create([
            'name' => 'منذر', 
            'email' => 'monther@gmail.com',
            'password' => Hash::make('12345678'),
        ]);
        
        User::factory(5)->create();
        
        $this->call([
            StoreSeeder::class,
            ProductCategorySeeder::class,
            ProductSeeder::class,
            ShipmentMethodSeeder::class,
            CommentSeeder::class,
            TransactionSeeder::class
        ]);
    }
}
