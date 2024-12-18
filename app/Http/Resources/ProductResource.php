<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray($request)
    {
        $user = $request->user();
        $data = [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'price' => $this->price,
            'final_price' => $this->final_price,
            'is_favourite' => $user ? $user->favouriteProducts->contains($this->id) : false,
            'stock' => $this->stock,
            'average_rating' => $this->ratings()->avg('rating') ?? 0,
            'number_of_ratings' => $this->ratings->count(),
            // 'image' => $this->image,
            'images' => $this->whenLoaded('images', function () {
                return $this->images->map(function ($image) {
                    return $image->image;
                });
            }),
            'product_category' => $this->whenLoaded('productCategory', function () {
                return [
                    'id' => $this->productCategory->id,
                    'name' => $this->productCategory->name,
                ];
            }),
            'variation_options' => $this->whenLoaded('variationOptions', function () {
                return $this->variationOptions->map(function ($option) {
                    return [
                        'id' => $option->id,
                        'name' => $option->value,
                    ];
                });
            }),
        ];

        if ($request->routeIs('mostOrderedProducts')) {
            $data['total_ordered_quantity'] = $this->total_ordered_quantity ?? 0;
        }

        if ($this->pivot && $this->pivot->quantity !== null) {
            $data['quantity'] = $this->pivot->quantity;
        }

        return $data;
    }
}
