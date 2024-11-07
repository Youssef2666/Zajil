<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'total' => $this->total,
            'shipment_method_id' => $this->shipment_method_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'store_id' => $this->store_id,
            'products' => ProductResource::collection($this->whenLoaded('products')),
        ];
    }
}
