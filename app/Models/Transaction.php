<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $fillable = ['wallet_id', 'type', 'amount', 'description', 'order_id', 'receiver_user_id'];

    public function wallet()
    {
        return $this->belongsTo(Wallet::class);
    }

    // public function getStoreNameAttribute()
    // {
    //     $user = $this->wallet->user;
        
    //     $store = $user->orders()
    //         ->with('products.store')
    //         ->get()
    //         ->pluck('products')
    //         ->flatten()
    //         ->first()
    //         ->store ?? null;

    //     return $store ? $store->name : null;
    // }

    public function store(){
        return $this->hasOneThrough(Store::class, Order::class);
    }

    public function order(){
        return $this->belongsTo(Order::class);
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_user_id');
    }
}
