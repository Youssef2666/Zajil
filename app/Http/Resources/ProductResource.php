<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'price' => $this->price,
            'stock' => $this->stock,
            'product_category' => $this->whenLoaded('productCategory', function () {
                return [
                    'id' => $this->productCategory->id,
                    'name' => $this->productCategory->name,
                ];
            }),
            // 'variation_options' => $this->whenLoaded('variationOptions', function () {
            //     return $this->variationOptions->map(function ($option) {
            //         return [
            //             'id' => $option->id,
            //             'name' => $option->value,
            //         ];
            //     });
            // }),
        ];
    }
}
