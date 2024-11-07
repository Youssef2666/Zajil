<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransactionResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'amount' => $this->amount,
            'description' => $this->description,
            'order_id' => $this->order ? $this->order->id : null, 
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'store_name' => $this->order && $this->order->store 
                        ? $this->order->store->name 
                        : null,
            'store_image' => $this->order && $this->order->store 
                        ? $this->order->store->image 
                        : null,
        ];
    }
}
