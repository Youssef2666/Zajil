<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CartResource extends JsonResource
{
    private $MyProducts;

    public function __construct($resource, $products = null)
    {
        parent::__construct($resource);
        $this->MyProducts = $products;
    }

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
            'products' =>  ProductResource::collection($this->MyProducts)->response()->getData(true),
            'total_price' => number_format($totalPrice, 2),
        ];
    }
}
