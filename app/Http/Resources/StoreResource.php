<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StoreResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'name' => $this->name,
            'description' => $this->description,
            'image' => $this->image,
            'average_rating' => $this->ratings()->avg('rating') ?? 0,
            'number_of_ratings' => $this->ratings->count(),
            'categories' => $this->whenLoaded('products', function () {
                return $this->products
                    ->pluck('productCategory')
                    ->unique('id')
                    ->map(function ($category) {
                        return [
                            'id' => $category->id,
                            'name' => $category->name,
                        ];
                    })
                    ->values();
            }),
            'categories_string' => $this->whenLoaded('products', function () {
                return $this->products
                    ->pluck('productCategory.name')
                    ->unique()
                    ->join(' و '); 
            }),
            'location' => $this->whenLoaded('location'),
        ];
    }
}


