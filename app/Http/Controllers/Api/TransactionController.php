<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\TransactionResource;
use App\Traits\ResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TransactionController extends Controller
{
    use ResponseTrait;
    public function index()
    {
        $transactions = Auth::user()->transactions()->with('order.store','receiver')->get();
        return $this->success(TransactionResource::collection($transactions));
    }
    public function show(Request $request, $transactionId)
    {
        $transaction = Auth::user()->transactions()
        ->where('transactions.id', $transactionId)
        ->with('order.store')
            ->first();

        if (!$transaction) {
            return response()->json(['message' => 'Transaction not found'], 404);
        }

        return $this->success(new TransactionResource($transaction));
    }



    public function getUserTransactions(Request $request)
    {
        $transactions = Auth::user()->wallet->transactions;
        return $this->success(TransactionResource::collection($transactions));
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

        $wallet->balance += $type === 'credit' ? $amount : -$amount;
        $wallet->save();

        return $transaction;
    }

}
