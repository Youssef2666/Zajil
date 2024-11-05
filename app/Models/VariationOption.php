<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VariationOption extends Model
{
    protected $fillable = ['variation_id', 'value'];

    public function variation()
    {
        return $this->belongsTo(Variation::class);
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'product_configurations')
            ->withTimestamps();
    }
}
