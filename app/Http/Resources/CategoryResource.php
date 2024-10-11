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
            'has_items' => $this->has_items,
        ];
        if (isset($this->movies_count)) {
            $arr['movies_count'] = $this->movies_count;
        }
        return $arr;
    }
}
