<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Genre;
use App\Models\Keyword;
use App\Models\Like;
use App\Models\Media;
use App\Models\MediaGenre;
use App\Models\MediaStaff;
use App\Models\Person;
use App\Models\Review;
use App\Models\User;
use DB;
use Faker\Factory;
use Illuminate\Database\Seeder;

class MediaSeeder extends Seeder
{

    public function run(): void
    {
        if (DatabaseSeeder::$usersCnt < 10) {
            throw new \Exception("users count should be greater than 10");
        }
        if (DatabaseSeeder::$peopleCnt < 10) {
            throw new \Exception("people count should be greater than 10");
        }
        $genresIds = Genre::pluck('id');
        $keywordsIds = Keyword::pluck('id');
        $companiesIds = Company::pluck('id');
        $userIds = User::pluck('id');
        $peopleIds = Person::pluck('id');
        $faker = Factory::create();

        $genresData = [];
        $likesData = [];
        $keywrodsData = [];
        $companiesData = [];
        $countriesData = [];
        $languagesData = [];
        $reviewsData = [];
        $staffData = [];


        Media::factory()
            ->createMany(DatabaseSeeder::$mediasCnt)
            ->each(function ($media) use ($userIds, $peopleIds, $genresIds, $keywordsIds, $companiesIds, $faker, &$genresData, &$likesData, &$keywrodsData, &$companiesData, &$countriesData, &$languagesData, &$reviewsData, &$staffData) {
                // adding genres to media
                foreach ($faker->randomElements($genresIds, rand(1, 5)) as $genreId) {
                    $genresData[] = [
                        'media_id' => $media->id,
                        'genre_id' => $genreId,
                    ];
                }

                // adding likes,dislike to media
                foreach ($faker->randomElements($userIds, rand(1, 10)) as $value) {
                    $likesData[] = [
                        'likeable_type' => Media::class,
                        'likeable_id' => $media->id,
                        'user_id' => $value,
                        'is_liked' => $faker->boolean(rand(20, 80)) ? 1 : 0,
                    ];
                }

                // // adding keywords to medias
                foreach ($faker->randomElements($keywordsIds, rand(1, 5)) as $keywordId) {
                    $keywrodsData[] = [
                        'media_id' => $media->id,
                        'keyword_id' => $keywordId,
                    ];
                }

                // // adding companies to media 
                if ($faker->boolean(20)) {
                    foreach ($faker->randomElements($companiesIds, rand(1, 2)) as $companyId) {
                        $companiesData[] = [
                            'media_id' => $media->id,
                            'company_id' => $companyId,
                        ];
                    }
                }

                // adding languages to media
                if ($faker->boolean(70)) {
                    $languageIds = [LanguagesSeeder::$famousLanguagesIds[0]];
                } else {
                    $languagesCnt = rand(1, 3);
                    $languageIds = $faker->randomElements(LanguagesSeeder::$famousLanguagesIds, $languagesCnt);
                }
                foreach ($languageIds as $langId) {
                    $languagesData[] = [
                        'media_id' => $media->id,
                        'language_id' => $langId,
                    ];
                }
    
                // // adding countries to media
                $countryCnt = $faker->boolean(80) ? 1 : rand(2, 3);
                foreach ($faker->randomElements(CountrySeeder::$famousCountriesIds, $countryCnt) as $countryId) {
                    $countriesData[] = [
                        'media_id' => $media->id,
                        'country_id' => $countryId,
                    ];
                }

                // adding reviews to media  
                if ($faker->boolean(95)) {
                    $reviewsCnt = rand(3, 10);
                    $randomUsersIds = $faker->randomElements($userIds, $reviewsCnt);
                    // $reviews = [];
                    for ($i = 0; $i < $reviewsCnt; $i++) {
                        $review = [];
                        $review['media_id'] = $media->id;
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
                        // array_push($reviews, $review);
                        $reviewsData[] = $review;
                    }
                    // $media->reviews()->createMany($reviews);
                }

                // adding staff to medias
                $staffCnt = rand(8, 10);
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
                // $staffs = [];
                for ($i = 0; $i < $staffCnt; $i++) {
                    $staf = [
                        'media_id' => $media->id,
                        'job' => $jobs[$i],
                        'person_id' => $randomPeaopleIds[$i],
                    ];
                    // $staffs[] = $staf;
                    $staffData[] = $staf;
                }
                // $media->staff()->attach($staffs);
            });
        MediaGenre::insert($genresData);
        Like::insert($likesData);
        DB::table('media_keywords')->insert($keywrodsData);
        DB::table('media_companies')->insert($companiesData);
        DB::table('media_countries')->insert($countriesData);
        DB::table('media_languages')->insert($languagesData);
        Review::insert($reviewsData);
        MediaStaff::insert($staffData);
    }
}
