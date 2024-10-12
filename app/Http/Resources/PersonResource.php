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
            'id' => $this->id,
            'name' => $this->name,
            'age' => $this->whenHas('birth_date', function () {
                return Carbon::parse($this->birth_date)->age;
            }),
            'gender' => $this->whenHas('is_male', function () {
                return $this->is_male ? 'male' : 'female';
            }),
            'movies' => MovieResource::collection($this->whenLoaded('moviesWorkedIn')),
            'followers_count' => $this->whenCounted('followers'),
            'movies_count' => $this->whenCounted('movies'),
            'worked_as' => $this->whenPivotLoaded('movie_actors', function () {
                return $this->pivot->job;
            }),
        ];
    }
}
