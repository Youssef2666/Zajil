<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;

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
            ->withPivot('quantity')
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


}
