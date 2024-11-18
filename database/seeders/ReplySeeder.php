<?php

namespace Database\Seeders;

use App\Models\Like;
use App\Models\Reply;
use App\Models\User;
use Faker\Factory;
use Illuminate\Database\Seeder;

class ReplySeeder extends Seeder
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
        $repliesCount = Reply::count();
        for ($replyId = 1; $replyId <= $repliesCount; $replyId++) {
            // add replies to reviewReplies
            if ($faker->boolean(50)) {
                $cnt = rand(1, 2);
                $randomUsersIds = $faker->randomElements($userIds, $cnt);
                for ($i = 0; $i < $cnt; $i++) {
                    $repliesData[] = [
                        'review_id' => null,
                        'reply_id' => $replyId,
                        'user_id' => $randomUsersIds[$i],
                        'text' => $faker->text(250),
                    ];
                }
            }
            // add likes/dislikes to review
            $randomUsersIds = $faker->randomElements($userIds, rand(1, 5));
            foreach ($randomUsersIds as $key => $value) {
                $likesData[] = [
                    'likeable_type' => Reply::class,
                    'likeable_id' => $replyId,
                    'user_id' => $value,
                    'is_liked' => $faker->boolean(rand(20, 80)) ? 1 : 0,
                ];
            }
        }
        Reply::insert($repliesData);
        Like::insert($likesData);
    }
}
