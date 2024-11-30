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
            // 'latitude' => $this->latitude,
            // 'longitude' => $this->longitude,
            'image' => $this->image,
            'average_rating' => $this->ratings()->avg('rating') ?? 0,
            'number_of_ratings' => $this->ratings->count(),
            'products' => ProductResource::collection($this->whenLoaded('products')),
            'categories' => $this->whenLoaded('products', function () {
                return $this->products
                    ->pluck('productCategory')
                    ->unique('id')
                    ->map(function ($category) {
                        return [
                            'id' => $category->id,
                            'name' => $category->name,
                        ];
                    });
            }),
            'location' => $this->whenLoaded('location'),
        ];
    }

}
