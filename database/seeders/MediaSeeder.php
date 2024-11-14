<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Genre;
use App\Models\Keyword;
use App\Models\Media;
use App\Models\Person;
use App\Models\User;
use Faker\Factory;
use Illuminate\Database\Seeder;

class MediaSeeder extends Seeder
{

    public function run(): void
    {
        $genresIds = Genre::pluck('id');
        $keywordsIds = Keyword::pluck('id');
        $companiesIds = Company::pluck('id');
        $userIds = User::pluck('id');
        $peopleIds = Person::pluck('id');
        $faker = Factory::create();

        Media::factory()
            ->createMany(DatabaseSeeder::$mediasCnt)
            ->each(function ($media) use ($userIds, $peopleIds, $genresIds, $keywordsIds, $companiesIds, $faker) {
                // adding genres to media
                $media->genres()->attach($faker->randomElements($genresIds, rand(1, 5)));

                // adding likes,dislike to media
                $randomUsersIds = $faker->randomElements($userIds, rand(1, 10));
                $likeUsers = [];
                $dislikeUsers = [];
                foreach ($randomUsersIds as $key => $value) {
                    if ($faker->boolean(rand(20, 80))) {
                        array_push($likeUsers, ['user_id' => $value, 'is_liked' => 1]);
                    } else {
                        array_push($dislikeUsers, ['user_id' => $value, 'is_liked' => 0]);
                    }
                }
                $media->likes()->createMany($likeUsers);
                $media->dislikes()->createMany($dislikeUsers);

                // adding keywords to medias
                $media->keywords()->attach($faker->randomElements($keywordsIds, rand(1, 5)));

                // adding companies to media 
                if ($faker->boolean(20)) {
                    $media->companies()->attach($faker->randomElements($companiesIds, rand(1, 2)));
                }

                // adding reviews to media  
                if ($faker->boolean(95)) {
                    $reviewsCnt = rand(3, 10);
                    $randomUsersIds = $faker->randomElements($userIds, $reviewsCnt);
                    $reviews = [];
                    for ($i = 0; $i < $reviewsCnt; $i++) {
                        $review = [];
                        $review['user_id'] = $randomUsersIds[$i];
                        $review['review'] = $faker->text(200);
                        $chance = rand(0, 100);
                        if ($chance <= 50) {
                            $review['score'] = $faker->numberBetween(60, 80);
                        } else if ($chance <= 60) {
                            $review['score'] = $faker->numberBetween(80, 100);
                        } else if ($chance <= 80) {
                            $review['score'] = $faker->numberBetween(40, 60);
                        } else {
                            $review['score'] = $faker->numberBetween(10, 40);
                        }
                        array_push($reviews, $review);
                    }
                    $media->reviews()->createMany($reviews);
                }

                // adding languages to media
                if ($faker->boolean(70)) {
                    $languageIds = LanguagesSeeder::$famousLanguagesIds[0];
                } else {
                    $languagesCnt = rand(1, 3);
                    $languageIds = $faker->randomElements(LanguagesSeeder::$famousLanguagesIds, $languagesCnt);
                }
                $media->languages()->attach($languageIds);

                // adding countries to media
                $countryCnt = $faker->boolean(80) ? 1 : rand(2, 3);
                $media->countries()->attach($faker->randomElements(CountrySeeder::$famousCountriesIds, $countryCnt));

                // adding staff to medias
                $staffCnt = rand(8, 15);
                $jobs = [];
                for ($i = 0; $i < $staffCnt; $i++) {
                    $jobs[] = 'actor';
                }
                $jobs[0] = 'director';
                $jobs[1] = 'producer';
                if ($faker->boolean(30)) {
                    $jobs[2] = 'writer';
                }
                $jobs[3] = 'writer';
                if ($faker->boolean(30)) {
                    $jobs[4] = 'writer';
                }
                if ($faker->boolean(50)) {
                    $jobs[5] = 'music';
                }
                shuffle($jobs);
                $randomPeaopleIds = $faker->randomElements($peopleIds, $staffCnt);
                $staffs = [];
                for ($i = 0; $i < $staffCnt; $i++) {
                    $staf = [
                        'job' => $jobs[$i],
                        'person_id' => $randomPeaopleIds[$i],
                    ];
                    $staffs[] = $staf;
                }
                $media->staff()->attach($staffs);
            });
    }
}
