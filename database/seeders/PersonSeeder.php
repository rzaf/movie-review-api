<?php

namespace Database\Seeders;

use App\Models\Following;
use App\Models\Person;
use App\Models\User;
use Database\Factories\MediaStaffFactory;
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
        $userIds = User::pluck('id');
        $faker = Factory::create();

        Person::factory()
            ->createMany(DatabaseSeeder::$peopleCnt)
            ->each(function ($person, $id) use ($userIds, $faker) {
                // adding followers to people
                $followersCnt = rand(3, 20);
                $person->followers()->attach($faker->randomElements($userIds, $followersCnt));
            });
    }
}
