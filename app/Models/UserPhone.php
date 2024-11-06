<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserPhone extends Model
{
    protected $fillable = [
        'phone',
        'is_default',
        'is_verified',
        'user_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
