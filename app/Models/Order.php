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
    ];

    public function products()
    {
        return $this->belongsToMany(Product::class)
            ->withPivot('quantity');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function shipmentMethod()
    {
        return $this->belongsTo(ShipmentMethod::class);
    }

}
