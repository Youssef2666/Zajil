<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CommentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $comments = [
            [
                'user_id' => 1,
                'store_id' => 2,
                'comment' => 'متجر ملييييييييح',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => 3,
                'store_id' => 2,
                'comment' => 'منور عالتعليق يا شنابو',
                'parent_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => 2,
                'store_id' => 2,
                'comment' => 'سلملنا على ابراهيم قوله صاحبك ربيع',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => 3,
                'store_id' => 2,
                'comment' => 'على طول',
                'parent_id' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($comments as $comment) {
            DB::table('comment_store')->insert($comment);
        }
    }
}
