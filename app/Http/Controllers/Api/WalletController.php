<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Traits\ResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WalletController extends Controller
{
    use ResponseTrait;
    public function index()
    {
        $wallet = Auth::user()->wallet()->with(['transactions' => function ($query) {
            $query->orderBy('created_at', 'desc');
        }])->first();

        if (!$wallet) {
            return $this->error('Wallet not found.', 404);
        }

        $transactions = $wallet->transactions;

        $totalCreditAmount = $transactions->where('type', 'credit')->sum('amount');
        $totalCreditCount = $transactions->where('type', 'credit')->count();

        $totalDebitAmount = $transactions->where('type', 'debit')->sum('amount');
        $totalDebitCount = $transactions->where('type', 'debit')->count();

        $totalTransactions = $transactions->count();

        return $this->success([
            'total_credit_amount' => $totalCreditAmount,
            'total_credit_count' => $totalCreditCount,
            'total_debit_amount' => $totalDebitAmount,
            'total_debit_count' => $totalDebitCount,
            'total_transactions' => $totalTransactions,
            'wallet' => $wallet,
        ]);
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        $wallet = $user->wallet;
        $wallet->balance += $request->amount;
        $wallet->save();
        return $this->success($wallet);
    }
}
