<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Traits\ResponseTrait;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\WalletResource;

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
}
