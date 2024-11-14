<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PersonResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            // 'id' => $this->id,
            'url' => $this->url,
            'name' => $this->name,
            'age' => $this->whenHas('birth_date', function () {
                return Carbon::parse($this->birth_date)->age;
            }),
            'country' => $this->whenLoaded('country', function () {
                return $this->country->name;
            }),
            'gender' => $this->whenHas('is_male', function () {
                return $this->is_male ? 'male' : 'female';
            }),
            'about' => $this->whenHas('about'),
            'medias' => MediaResource::collection($this->whenLoaded('mediasWorkedIn')),
            'followers_count' => $this->whenCounted('followers'),
            'medias_count' => $this->whenCounted('medias'),
            'worked_as' => $this->whenPivotLoaded('media_actors', function () {
                return $this->pivot->job;
            }),
        ];
    }
}
