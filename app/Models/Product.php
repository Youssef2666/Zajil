<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Product extends Model
{
    protected $fillable = [
        'name',
        'description',
        'price',
        'stock',
        'product_category_id',
        'store_id',
        'image',
        'discount_value',
        'discount_percentage',
        'discount_start',
        'discount_end',
    ];

    public function productCategory()
    {
        return $this->belongsTo(ProductCategory::class);
    }

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function orders()
    {
        return $this->belongsToMany(Order::class, 'order_product')
            ->withPivot('quantity', 'price_at_purchase')
            ->withTimestamps();
    }

    public function variationOptions()
    {
        return $this->belongsToMany(VariationOption::class, 'product_configurations')
            ->withTimestamps();
    }

    public function favouritedByUsers()
    {
        return $this->belongsToMany(User::class, 'favourite_products')
            ->withTimestamps();
    }

    public function ratings()
    {
        return $this->belongsToMany(User::class, 'product_rating')
            ->withPivot('rating')
            ->withTimestamps();
    }

    public static function getMostOrderedProducts($limit = 10)
    {
        return self::withCount(['orders as total_ordered_quantity' => function ($query) {
            $query->select(DB::raw("SUM(order_product.quantity)"));
        }])
            ->orderByDesc('total_ordered_quantity')
            ->take($limit)
            ->get();
    }

    public function getFinalPriceAttribute()
    {
        $price = $this->price;

        if ($this->discount_start && $this->discount_end) {
            $now = now();
            if ($now->between($this->discount_start, $this->discount_end)) {
                if ($this->discount_value) {
                    $price -= $this->discount_value;
                } elseif ($this->discount_percentage) {
                    $price -= $price * ($this->discount_percentage / 100);
                }
            }
        }

        return $price > 0 ? $price : 0; // Ensure the final price is not negative
    }

    public function images()
    {
        return $this->hasMany(ProductImage::class);
    }

}
