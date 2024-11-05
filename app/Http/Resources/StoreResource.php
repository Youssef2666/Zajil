<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StoreResource extends JsonResource
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
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'average_rating' => $this->ratings()->avg('rating'),
            'number_of_ratings' => $this->ratings->count(),
            'products' => $request->has('with') && in_array('products', explode(',', $request->input('with')))
                ? $this->whenLoaded('products')
                : null,
        ];
    }
}
