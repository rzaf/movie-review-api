<?php

namespace Database\Factories;

use App\Models\Movie;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\review>
 */
class ReviewFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $resets = 0;
        $movieId = 1;
        $userId = 1;

        while ($resets < 10) {
            $movieId = $this->faker->numberBetween(1, Movie::count());
            $userId = $this->faker->numberBetween(1, User::count());
            if (!Review::where(['movie_id' => $movieId, 'user_id' => $userId])->exists()) {
                break;
            }
            $resets++;
        }
        return [
            'movie_id' => $movieId,
            'user_id' => $userId,
            'review' => $this->faker->text(200),
            'score' => $this->faker->numberBetween(0,100),
        ];
    }
}
