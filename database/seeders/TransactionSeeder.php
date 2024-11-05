<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class TransactionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $transactions = [
            [
                'wallet_id' => 1,
                'type' => 'credit',
                'amount' => 1000,
                'description' => 'Charged via Anis',
            ],
            [
                'wallet_id' => 2,
                'type' => 'credit',
                'amount' => 200,
                'description' => 'Charged via Sadad',
            ],
            [
                'wallet_id' => 3,
                'type' => 'credit',
                'amount' => 20000,
                'description' => 'Charged via Adfali',
            ],
        ];
        foreach ($transactions as $transaction) {
            \App\Models\Transaction::create($transaction);
        }
    }
}
