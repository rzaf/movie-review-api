<?php

namespace Database\Factories;

use App\Models\Reply;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Reply>
 */
class ReplyFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $userId = $this->faker->numberBetween(1, User::count());
        $reviewId = $this->faker->numberBetween(1, Review::count());
        $replyId = null;
        if ($this->faker->boolean(20)) {
            $replyId = $this->faker->numberBetween(1, Reply::count());
            if ($replyId == 0) {
                $replyId = null;
            } else {
                $reviewId = null;
            }
        }

        return [
            'text' => $this->faker->text(250),
            'user_id' => $userId,
            'review_id' => $reviewId,
            'reply_id' => $replyId,
        ];
    }
}
