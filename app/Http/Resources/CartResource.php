<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CartResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        $totalPrice = $this->products->sum(function ($product) {
            return $product->price * $product->pivot->quantity;
        });

        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'products' => $this->whenLoaded('products', function () {
                return $this->products->map(function ($product) {
                    return [
                        'id' => $product->id,
                        'product_category_id' => $product->product_category_id,
                        'store_id' => $product->store_id,
                        'name' => $product->name,
                        'description' => $product->description,
                        'price' => number_format($product->price, 2), 
                        'stock' => $product->stock,
                        'created_at' => $product->created_at,
                        'updated_at' => $product->updated_at,
                        'quantity' => $product->pivot->quantity,
                    ];
                });
            }),
            'total_price' => number_format($totalPrice, 2),
        ];
    }

}
