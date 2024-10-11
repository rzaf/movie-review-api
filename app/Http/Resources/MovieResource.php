<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MovieResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $arr = [
            'name' => $this->name,
            'url' => $this->url,
            'release_year' => $this->release_year,
        ];

        if ($this->relationLoaded('category')) {
            $arr['category'] = $this->category->name;
        }
        if (isset($this->reviews_avg_score)) {
            $arr['average_score'] = $this->reviews_avg_score / 10;
        }
        if (isset($this->reviews_count)) {
            $arr['reviews_count'] = $this->reviews_count;
            if ($this->reviews_count == 0) {
                $arr['average_score'] = null;
            }
        }
        if ($this->relationLoaded('staff')) {
            $arr['staff'] = PersonResource::collection($this->staff);
        }
        if (isset($this->tags_group_concat_name)) {
            $arr['tags'] = $this->tags_group_concat_name;
        }

        if (isset($this->likes_count)) {
            $arr['likes_count'] = $this->likes_count;
        }
        if (isset($this->dislikes_count)) {
            $arr['dislikes_count'] = $this->dislikes_count;
        }

        if (isset($this->pivot)) {
            $arr['job'] = $this->pivot->job;
        }

        return $arr;
    }
}
