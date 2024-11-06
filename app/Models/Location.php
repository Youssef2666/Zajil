<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    protected $fillable = [
        'name',
        'address',
        'country',
        'city',
        'postal_code',
        'latitude',
        'longitude',
        'user_id',
        'is_default',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
