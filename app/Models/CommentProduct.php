<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CommentProduct extends Model
{
    protected $fillable = [
        'user_id',
        'product_id',
        'comment',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}