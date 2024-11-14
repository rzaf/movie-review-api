<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReviewResource extends JsonResource
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
            'review' => $this->review,
            'score' => $this->score / 10,
            'reviewer_username' => $this->whenLoaded('user', function () {
                return $this->user->name;
            }),
            'media' => new MediaResource($this->whenLoaded('media')),
            'replies_count' => $this->whenCounted('replies'),
            'likes_count' => $this->whenCounted('likes'),
            'dislikes_count' => $this->whenCounted('dislikes'),
        ];
    }
}
