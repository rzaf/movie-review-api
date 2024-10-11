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
        $arr = [
            'id' => $this->id,
            'review' => $this->review,
            'score' => $this->score / 10,
        ];

        if ($this->relationLoaded('user')) {
            $arr['reviewer_username'] = $this->user->name;
        }
        if ($this->relationLoaded('movie')) {
            $arr['movie'] = new MovieResource($this->movie);
        }

        if (isset($this->replies_count)) {
            $arr['replies_count'] = $this->replies_count;
        }
        if (isset($this->likes_count)) {
            $arr['likes_count'] = $this->likes_count;
        }
        if (isset($this->dislikes_count)) {
            $arr['dislikes_count'] = $this->dislikes_count;
        }
        return $arr;
    }
}
