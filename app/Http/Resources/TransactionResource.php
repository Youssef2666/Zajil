<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TransactionResource extends JsonResource
{
    public function toArray($request)
{
    $data = [
        'id' => $this->id,
        'type' => $this->type,
        'amount' => $this->amount,
        'description' => $this->description,
        'created_at' => $this->created_at,
        'updated_at' => $this->updated_at,
    ];

    if ($this->order && $this->order->id) {
        $data['order_id'] = $this->order->id;
    }

    if ($this->order && $this->order->store) {
        if ($this->order->store->name) {
            $data['receiver_name'] = $this->order->store->name;
        }

        if ($this->order->store->image) {
            $data['receiver_image'] = $this->order->store->image;
        }
    }

    // and if receiver information is available
    if ($this->receiver) {
        if ($this->receiver->name) {
            $data['receiver_name'] = $this->receiver->name;
        }

        if ($this->receiver->profile_photo_path) {
            $data['receiver_image'] = $this->receiver->profile_photo_path;
        }
    }

    // Remove any fields with null values
    return array_filter($data, fn($value) => !is_null($value));
}


}
