<?php

namespace Database\Seeders;

use App\Models\Country;
use DB;
use Illuminate\Database\Seeder;

class CountrySeeder extends Seeder
{
    public static $famousCountriesIds;

    public function run(): void
    {
        DB::unprepared(file_get_contents(base_path('database/seeders/countries.sql')));
        self::$famousCountriesIds = Country::whereIn('country_code', [
            'us',
            'uk',
            'ca',
            'fr',
            'in',
            'de',
        ])->select('id')->pluck('id');
    }
}
