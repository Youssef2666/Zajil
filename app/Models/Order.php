<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'user_id',
        'total',
        'quantity',
        'status',
        'shipment_method_id',
        'location_id',
        'store_id',
        'code',
    ];

    public function products()
    {
        return $this->belongsToMany(Product::class)
            ->withPivot('quantity', 'price_at_purchase')
            ->withTimestamps();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function shipmentMethod()
    {
        return $this->belongsTo(ShipmentMethod::class);
    }

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function transaction()
    {
        return $this->hasOne(Transaction::class);
    }

    public function location()
    {
        return $this->belongsTo(Location::class);
    }
}
