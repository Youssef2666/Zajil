<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WalletResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $transactions = $this->transactions;
        return [
            'id' => $this->id,
            'balance' => $this->balance,
            'user_id' => $this->user_id,
            'total_credit_amount' => $transactions->where('type', 'credit')->sum('amount'),
            'total_credit_count' => $transactions->where('type', 'credit')->count(),
            'total_debit_amount' => $transactions->where('type', 'debit')->sum('amount'),
            'total_debit_count' => $transactions->where('type', 'debit')->count(),
            'total_transactions' => $transactions->count(),
            'transactions' => TransactionResource::collection($transactions->sortByDesc('created_at')),
        ];
    }
}
