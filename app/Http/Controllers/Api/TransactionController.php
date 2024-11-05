<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Traits\ResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TransactionController extends Controller
{
    use ResponseTrait;
    public function index()
    {
        return $this->success(Auth::user()->transactions);
    }

    public function addTransaction($amount, $type = 'credit', $description = null)
    {
        $user = Auth::user();
        $wallet = $user->wallet;

        
        if ($type === 'debit' && $wallet->balance < $amount) {
            throw new \Exception('Insufficient balance');
        }

        $transaction = $wallet->transactions()->create([
            'type' => $type,
            'amount' => $amount,
            'description' => $description,
        ]);

        // Update wallet balance
        $wallet->balance += $type === 'credit' ? $amount : -$amount;
        $wallet->save();

        return $transaction;
    }

}
