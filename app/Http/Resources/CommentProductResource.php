<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CommentProductResource extends JsonResource
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
            'comment' => $this->comment,
            'created_at' => $this->created_at,
            'user' => $this->whenLoaded('user', function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'profile_photo_path' => $user->profile_photo_path,
                ];
            })
        ];
    }
}
