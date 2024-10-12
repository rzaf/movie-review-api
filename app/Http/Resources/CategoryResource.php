<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CategoryResource extends JsonResource
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
            'parent_name' => $this->parentCategory?->name,
            'movies_count' => $this->whenCounted('movies'),
            'has_items' => $this->has_items,
        ];
        return $arr;
    }
}
