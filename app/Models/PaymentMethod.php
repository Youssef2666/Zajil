<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentMethod extends Model
{
    protected $fillable = [
        'name',
        'description',
        'image',
    ];

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }
}
