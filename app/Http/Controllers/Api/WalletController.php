<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\WalletResource;
use App\Models\Wallet;
use App\Traits\ResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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

        return $this->success(new WalletResource($wallet));
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        $wallet = $user->wallet;
        $wallet->balance += $request->amount;
        $wallet->save();
        return $this->success($wallet);
    }

    public function transferBalance(Request $request)
    {
        $data = $request->validate([
            'receiver_wallet_id' => 'required|exists:wallets,id',
            'amount' => 'required|numeric|min:0.01',
        ]);

        $senderWallet = Wallet::where('user_id', Auth::id())->firstOrFail();
        $receiverWallet = Wallet::findOrFail($data['receiver_wallet_id']);

        if ($senderWallet->id === $receiverWallet->id) {
            return response()->json(['message' => 'لا يمكنك التحويل لنفس المحفظة'], 400);
        }

        if ($senderWallet->balance < $data['amount']) {
            return response()->json(['message' => 'رصيدك غير كافي'], 400);
        }

        DB::transaction(function () use ($senderWallet, $receiverWallet, $data) {
            $senderWallet->balance -= $data['amount'];
            $senderWallet->save();

            $receiverWallet->balance += $data['amount'];
            $receiverWallet->save();

            $senderWallet->transactions()->create([
                'type' => 'debit',
                'amount' => $data['amount'],
                'description' => "Transfer to Wallet #{$receiverWallet->id}",
                'receiver_user_id' => $receiverWallet->user_id,
            ]);

            $receiverWallet->transactions()->create([
                'type' => 'credit',
                'amount' => $data['amount'],
                'description' => "Transfer from Wallet #{$senderWallet->id}",
                'receiver_user_id' => $senderWallet->user_id,
            ]);
        });

        return response()->json(['message' => 'تم التحويل بنجاح'], 200);
    }

}
