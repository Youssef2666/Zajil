<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray($request)
    {
        $data = [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'price' => $this->price,
            'stock' => $this->stock,
            'average_rating' => $this->ratings()->avg('rating') ?? 0,
            'number_of_ratings' => $this->ratings->count(),
            'image' => $this->image,
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
            'total_ordered_quantity' => $this->total_ordered_quantity ?? 0, // Add this line
        ];

        if ($this->pivot && $this->pivot->quantity !== null) {
            $data['quantity'] = $this->pivot->quantity;
        }

        return $data;
    }
}
