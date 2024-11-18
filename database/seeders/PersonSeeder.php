<?php

namespace Database\Seeders;

use App\Models\Following;
use App\Models\Person;
use App\Models\User;
use Database\Factories\MediaStaffFactory;
use Database\Factories\PersonFactory;
use Faker\Factory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PersonSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (DatabaseSeeder::$usersCnt < 5) {
            throw new \Exception("users count should be greater than 4");
        }
        $userIds = User::pluck('id');
        $faker = Factory::create();
        $followers = [];
        PersonFactory::times(DatabaseSeeder::$peopleCnt)
            ->create()
            ->each(function ($person, $id) use ($userIds, $faker, &$followers) {
                // adding followers to people
                $followersCnt = rand(1, DatabaseSeeder::$usersCnt / 5);
                foreach ($faker->randomElements($userIds, $followersCnt) as $followerId) {
                    $followers[] = [
                        'follower_id' => $followerId,
                        'following_id' => $person->id,
                    ];
                }
            });
        Following::insert($followers);
    }
}
