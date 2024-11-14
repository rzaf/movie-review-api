<?php

namespace Database\Seeders;

use App\Models\Review;
use App\Models\User;
use Faker\Factory;
use Illuminate\Database\Seeder;

class ReviewSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $userIds = User::pluck('id');
        $faker = Factory::create();

        Review::eachById(function ($review, $id) use ($faker, $userIds) {
            // add replies to review
            if ($faker->boolean(30)) {
                $cnt = rand(1, 4);
                $randomUsersIds = $faker->randomElements($userIds, $cnt);
                $replies = [];
                for ($i = 0; $i < $cnt; $i++) {
                    $replies[] = [
                        'user_id' => $randomUsersIds[$i],
                        'text' => $faker->text(250),
                        'reply_id' => null,
                    ];
                }
                $review->replies()->createMany($replies);
            }
            // add likes/dislikes to review
            $randomUsersIds = $faker->randomElements($userIds, rand(1, 5));
            $likeUsers = [];
            $dislikeUsers = [];
            foreach ($randomUsersIds as $key => $value) {
                if ($faker->boolean(rand(20, 80))) {
                    array_push($likeUsers, ['user_id' => $value, 'is_liked' => 1]);
                } else {
                    array_push($dislikeUsers, ['user_id' => $value, 'is_liked' => 0]);
                }
            }
            $review->likes()->createMany($likeUsers);
            $review->dislikes()->createMany($dislikeUsers);
        });
    }
}
