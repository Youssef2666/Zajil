<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Store extends Model
{
    protected $fillable = [
        'name',
        'description',
        'latitude',
        'longitude',
        'image',
        'user_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // public function productCategories(){
    //     return $this->hasManyThrough(ProductCategory::class, Product::class)->distinct();
    // }

    public function products()
    {
        return $this->hasMany(Product::class);
    }


    public function ratings()
    {
        return $this->belongsToMany(User::class, 'rate_store')
            ->withPivot('rating')
            ->withTimestamps();
    }

    public function favouritedByUsers()
    {
        return $this->belongsToMany(User::class, 'favourite_store')
            ->withTimestamps();
    }

    public function comments(): HasMany
    {
        return $this->hasMany(DB::table('comment_store'), 'store_id')->whereNull('parent_id');
    }

    public function allCommentsWithReplies()
    {
        return DB::table('comment_store')
            ->where('store_id', $this->id)
            ->leftJoin('comment_store as replies', 'comment_store.id', '=', 'replies.parent_id')
            ->select('comment_store.*', 'replies.id as reply_id', 'replies.comment as reply_comment')
            ->get();
    }

    public function productCategories()
    {
        return $this->hasManyThrough(ProductCategory::class, Product::class);
    }

    public function location(){
        return $this->hasOne(StoreLocation::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}
