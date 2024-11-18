<?php

namespace Database\Seeders;

use App\Models\Like;
use App\Models\Reply;
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

        $repliesData = [];
        $likesData = [];
        $reviewsCount = Review::count();
        for ($reviewId = 1; $reviewId <= $reviewsCount; $reviewId++) {
            // add replies to review
            if ($faker->boolean(30)) {
                $cnt = rand(1, 4);
                $randomUsersIds = $faker->randomElements($userIds, $cnt);
                for ($i = 0; $i < $cnt; $i++) {
                    $repliesData[] = [
                        'review_id' => $reviewId,
                        'reply_id' => null,
                        'user_id' => $randomUsersIds[$i],
                        'text' => $faker->text(250),
                    ];
                }
            }

            // add likes/dislikes to review
            $randomUsersIds = $faker->randomElements($userIds, rand(1, 5));
            foreach ($randomUsersIds as $key => $value) {
                $likesData[] = [
                    'likeable_type' => Review::class,
                    'likeable_id' => $reviewId,
                    'user_id' => $value,
                    'is_liked' => $faker->boolean(rand(20, 80)) ? 1 : 0,
                ];
            }
        }
        Reply::insert($repliesData);
        Like::insert($likesData);
    }
}
