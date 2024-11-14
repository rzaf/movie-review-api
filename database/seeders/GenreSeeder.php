<?php

namespace Database\Seeders;

use App\Models\Genre;
use Illuminate\Database\Seeder;

class GenreSeeder extends Seeder
{
    private static $customRows = [
        ['name'=>'Action'],
        ['name'=>'Adventure'],
        ['name'=>'Animation'],
        ['name'=>'Anime'],
        ['name'=>'Comedy'],
        ['name'=>'Crime'],
        ['name'=>'Documentary'],
        ['name'=>'Drama'],
        ['name'=>'Family'],
        ['name'=>'Fantasy'],
        ['name'=>'Game Show'],
        ['name'=>'Horror'],
        ['name'=>'Lifestyle'],
        ['name'=>'Music'],
        ['name'=>'Musical'],
        ['name'=>'Mystery'],
        ['name'=>'Reality TV'],
        ['name'=>'Romance'],
        ['name'=>'Sci-Fi'],
        ['name'=>'Seasonal'],
        ['name'=>'Short'],
        ['name'=>'Sport'],
        ['name'=>'Thriller'],
        ['name'=>'Time Travel'],
        ['name'=>'History'],
        ['name'=>'War'],
        ['name'=>'Sitcom'],
        ['name'=>'Biography'],
        ['name'=>'Superhero'],
    ];
    
    
    public function run(): void
    {
        DatabaseSeeder::$genresCnt = count(self::$customRows);
        Genre::insert(self::$customRows);
    }
}
