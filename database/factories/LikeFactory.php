<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Movie;
use App\Models\Reply;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\Like;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Like>
 */
class LikeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */

    private static $types = [Movie::class, Review::class, Reply::class];

    public function definition(): array
    {
        $type = $this->faker->randomElement(self::$types);
        $userId = $this->faker->numberBetween(1, User::count());
        $typeId = 1;
        switch ($type) {
            case Movie::class:
                $typeId = $this->faker->numberBetween(1, Movie::count());
                break;
            case Review::class:
                $typeId = $this->faker->numberBetween(1, Review::count());
                break;
            case Reply::class:
                $typeId = $this->faker->numberBetween(1, Reply::count());
                break;
        }
        return [
            'is_liked' => $this->faker->boolean(),
            'user_id' => $userId,
            'likeable_type' => $type,
            'likeable_id' => $typeId,
        ];
    }
}
