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
                'user_id' => 1,
                'image' => 'https://png.pngtree.com/png-vector/20221222/ourmid/pngtree-phoenix-bird-element-hand-drawn-cartoon-illustration-vector-png-image_6489402.png'
            ],
            [
                'name' => 'الهيرة',
                'description' => 'متجر الهيرة',
                'user_id' => 3,
                'image' => 'https://www.fay3.com/previews/2019-11/98fxuClrZy.png'
            ],
            [
                'name' => 'زمرد',
                'description' => 'متجر زمرد للجواهر',
                'user_id' => 2,
                'image' => 'https://th.bing.com/th/id/R.3575e1f3596f53da00138464c69cc60a?rik=ZrKN%2fCnVQmMKWw&riu=http%3a%2f%2fwww.dade-negar.ir%2fProduct%2fzomorod11_061117083341.png&ehk=hoTUGWNXtPNpwjBQdm0NeQMuFeRxbxpuvFCSuYgLlH0%3d&risl=&pid=ImgRaw&r=0'
            ]
            ];

        foreach ($stores as $store) {
            \App\Models\Store::create($store);
        }
    }
}
