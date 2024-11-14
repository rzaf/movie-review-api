<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    private static $customRows = [
        ['name'=>'Movie'],
        ['name'=>'Short'],
        ['name'=>'TV Series'],
        ['name'=>'TV Episode'],
        ['name'=>'TV Movie'],
        ['name'=>'TV Special'],
        ['name'=>'Video Game'],
        ['name'=>'Music Video'],
        ['name'=>'Podcast Series'],
        ['name'=>'Podcast Episode'],
    ];

    public function run(): void
    {
        DatabaseSeeder::$categoriesCnt = count(self::$customRows);
        Category::insert(self::$customRows);
    }
}