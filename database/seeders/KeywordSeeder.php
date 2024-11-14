<?php

namespace Database\Seeders;

use Database\Factories\KeywordFactory;
use Illuminate\Database\Seeder;

class KeywordSeeder extends Seeder
{
    public function run(): void
    {
        KeywordFactory::times(DatabaseSeeder::$keywordsCnt)->create();
    }
}
