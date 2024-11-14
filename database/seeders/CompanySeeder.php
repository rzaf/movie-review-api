<?php

namespace Database\Seeders;

use Database\Factories\CompanyFactory;
use Illuminate\Database\Seeder;

class CompanySeeder extends Seeder
{
    public function run(): void
    {
        CompanyFactory::times(DatabaseSeeder::$companiesCnt)->create();
    }
}
