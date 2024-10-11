<?php

namespace Database\Factories;

use App\Models\Movie;
use App\Models\MovieTag;
use App\Models\Tag;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\MovieTag>
 */
class MovieTagFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $resets = 0;
        $tagId = 1;
        $movieId = 1;

        while ($resets < 10) {
            $tagId = $this->faker->numberBetween(1, Tag::count());
            $movieId = $this->faker->numberBetween(1, Movie::count());
            if (!MovieTag::where(['tag_id' => $tagId, 'movie_id' => $movieId])->exists()) {
                break;
            }
            $resets++;
        }
        return [
            'tag_id' => $tagId,
            'movie_id' => $movieId,
        ];
    }
}
