<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StoreLocation extends Model
{
    protected $fillable = [
        'name',
        'address',
        'country',
        'city',
        'postal_code',
        'latitude',
        'longitude',
        'store_id',
    ];

    public function store()
    {
        return $this->belongsTo(Store::class);
    }
}
