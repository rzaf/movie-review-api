<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MovieStaffResource extends JsonResource
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
            'category_name' => $this->whenHas('category_name'),
            'tags' => $this->whenHas('tags_group_concat_name', function () {
                return $this->tags_group_concat_name ?? '';
            }),
            'worked_as' => $this->whenHas('job'),
        ];
    }
}
