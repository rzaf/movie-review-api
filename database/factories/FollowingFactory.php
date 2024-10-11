<?php

namespace Database\Factories;

use App\Models\Following;
use App\Models\Person;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Following>
 */
class FollowingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $resets = 0;
        while ($resets < 10) {
            $followerId = $this->faker->numberBetween(1, User::count());
            $followingId = $this->faker->numberBetween(1, Person::count());
            if (!Following::where(['follower_id' => $followerId, 'following_id' => $followingId])->exists()) {
                break;
            }
            $resets++;
        }
        return [
            'follower_id' => $followerId,
            'following_id' => $followingId,
        ];
    }
}
