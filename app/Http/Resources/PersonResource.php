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
        $arr = [
            'id' => $this->id,
            'name' => $this->name,
        ];

        if (isset($this->followers_count)) {
            $arr['followers_count'] = $this->followers_count;
        }

        if (isset($this->birth_date)) {
            $arr['age'] = Carbon::parse($this->birth_date)->age;
        }

        if (isset($this->is_male)) {
            $arr['gender'] = ($this->is_male ? 'male' : 'female');
        }

        if (isset($this->movies_count)) {
            $arr['movies_count'] = $this->movies_count;
        }

        if (isset($this->pivot)) {
            $arr['job'] = $this->pivot->job;
        }

        if ($this->relationLoaded('moviesWorkedIn')) {
            $arr['movies'] = MovieResource::collection($this->moviesWorkedIn);
        }

        return $arr;
        // return parent::toArray($request);
    }
}
