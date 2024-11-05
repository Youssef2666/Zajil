<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductCategory extends Model
{
    protected $fillable = [
        'name',
    ];

    public function variations()
    {
        return $this->hasMany(Variation::class);
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }
}
