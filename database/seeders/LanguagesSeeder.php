<?php

namespace Database\Seeders;

use App\Models\Language;
use DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class LanguagesSeeder extends Seeder
{
    public static $famousLanguagesIds;

    public function run(): void
    {
        DB::unprepared(file_get_contents(base_path('database/seeders/languages.sql')));
        self::$famousLanguagesIds = Language::whereIn('name', [
            'English',
            'Spanish',
            'German',
            'French',
        ])->select('id')->pluck('id');
        // print_r(self::$famousLanguagesIds);
    }
}
