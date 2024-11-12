<?php

namespace Database\Factories;

use App\Models\Genre;
use App\Models\Movie;
use App\Models\MovieGenre;
use App\Models\MovieTag;
use App\Models\Tag;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\MovieGenre>
 */
class MovieGenreFactory extends Factory
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
            $tagId = $this->faker->numberBetween(1, Genre::count());
            $movieId = $this->faker->numberBetween(1, Movie::count());
            if (!MovieGenre::where(['genre_id' => $tagId, 'movie_id' => $movieId])->exists()) {
                break;
            }
            $resets++;
        }
        return [
            'genre_id' => $tagId,
            'movie_id' => $movieId,
        ];
    }
}
