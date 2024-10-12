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
        return [
            'name' => $this->name,
            'url' => $this->url,
            'release_year' => $this->release_year,
            'category_name' => $this->whenLoaded('category', function () {
                return $this->category->name;
            }),
            'staff' => PersonResource::collection($this->whenLoaded('staff')),
            'likes_count' => $this->whenCounted('likes'),
            'dislikes_count' => $this->whenCounted('dislikes'),
            // 'tags' => $this->whenAggregated('tags', 'name', 'group_concat'),
            'tags' => $this->whenHas('tags_group_concat_name', function () {
                return $this->tags_group_concat_name ?? '';
            }),
            'worked_as' => $this->whenPivotLoaded('movie_actors', function () {
                return $this->pivot->job;
            }),
            'reviews_count' => $this->whenCounted('reviews'),
            'average_score' => $this->whenHas('reviews_avg_score', function () {
                if (isset($this->reviews_count) && $this->reviews_count == 0) {
                    return null;
                }
                return number_format($this->reviews_avg_score / 10, 2);
            }),
        ];
    }
}
