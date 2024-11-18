<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public static $usersCnt = 100;
    public static $categoriesCnt;
    public static $genresCnt;
    public static $keywordsCnt = 25;
    public static $companiesCnt = 25;

    public static $mediasCnt = 100;
    // public static $reviewsCnt = 30;

    public static $peopleCnt = 100;

    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            CountrySeeder::class,
            LanguagesSeeder::class,
            CategorySeeder::class,
            KeywordSeeder::class,
            CompanySeeder::class,
            GenreSeeder::class,
            PersonSeeder::class,
            MediaSeeder::class,
            ReviewSeeder::class,
            ReplySeeder::class,
        ]);
    }
}
