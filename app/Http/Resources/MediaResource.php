<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MediaResource extends JsonResource
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
            'release_date' => $this->release_date,
            'summary' => $this->whenHas('summary'),
            'storyline' => $this->whenHas('storyline'),
            'category_name' => $this->whenLoaded('category', function () {
                return $this->category->name;
            }),
            'genres' => $this->whenHas('genres_group_concat_name', function () {
                return $this->genres_group_concat_name ?? '';
            }),
            'countries' => $this->whenHas('countries_group_concat_country_name', function () {
                return $this->countries_group_concat_country_name ?? '';
            }),
            'languages' => $this->whenHas('languages_group_concat_name', function () {
                return $this->languages_group_concat_name ?? '';
            }),
            'keywords' => $this->whenHas('keywords_group_concat_name', function () {
                return $this->keywords_group_concat_name ?? '';
            }),
            'companies' => $this->whenHas('companies_group_concat_name', function () {
                return $this->companies_group_concat_name ?? '';
            }),
            'average_score' => $this->whenHas('reviews_avg_score', function () {
                if (isset($this->reviews_count) && $this->reviews_count == 0) {
                    return null;
                }
                return number_format($this->reviews_avg_score / 10, 2);
            }),
            'reviews_count' => $this->whenCounted('reviews'),
            'likes_count' => $this->whenCounted('likes'),
            'dislikes_count' => $this->whenCounted('dislikes'),
            'staff' => PersonResource::collection($this->whenLoaded('staff')),
            'worked_as' => $this->whenPivotLoaded('media_actors', function () {
                return $this->pivot->job;
            }),

        ];
    }
}
