<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Company;
use App\Models\Country;
use App\Models\Following;
use App\Models\Genre;
use App\Models\Keyword;
use App\Models\Language;
use App\Models\Like;
use App\Models\Media;
use App\Models\MediaStaff;
use App\Models\Person;
use App\Models\Reply;
use App\Models\Review;
use App\Models\User;
use Database\Seeders\CategorySeeder;
use Database\Seeders\CompanySeeder;
use Database\Seeders\CountrySeeder;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\GenreSeeder;
use Database\Seeders\KeywordSeeder;
use Database\Seeders\LanguagesSeeder;
use Database\Seeders\MediaSeeder;
use Database\Seeders\PersonSeeder;
use Database\Seeders\ReplySeeder;
use Database\Seeders\ReviewSeeder;
use Database\Seeders\UserSeeder;
use DB;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeedersTest extends TestCase
{
    use RefreshDatabase;

    public function test_country_seeder_run_successfully(): void
    {
        $this->assertDatabaseCount(Country::class, 0);
        (new CountrySeeder)->run();
        $this->assertGreaterThan(200, Country::count());
    }

    public function test_language_seeder_run_successfully(): void
    {
        $this->assertDatabaseCount(Language::class, 0);
        (new LanguagesSeeder)->run();
        $this->assertGreaterThan(100, Language::count());
    }

    public function test_category_seeder_run_successfully(): void
    {
        $this->assertDatabaseCount(Category::class, 0);
        (new CategorySeeder)->run();
        $this->assertDatabaseCount(Category::class, DatabaseSeeder::$categoriesCnt);
    }

    public function test_keyword_seeder_run_successfully(): void
    {
        DatabaseSeeder::$keywordsCnt = 10;
        $this->assertDatabaseCount(Keyword::class, 0);
        (new KeywordSeeder)->run();
        $this->assertDatabaseCount(Keyword::class, DatabaseSeeder::$keywordsCnt);
    }


    public function test_genre_seeder_run_successfully(): void
    {
        DatabaseSeeder::$genresCnt = 10;
        $this->assertDatabaseCount(Genre::class, 0);
        (new GenreSeeder)->run();
        $this->assertDatabaseCount(Genre::class, DatabaseSeeder::$genresCnt);
    }

    public function test_company_seeder_run_successfully(): void
    {
        DatabaseSeeder::$companiesCnt = 10;
        $this->assertDatabaseCount(Company::class, 0);
        (new CompanySeeder)->run();
        $this->assertDatabaseCount(Company::class, DatabaseSeeder::$companiesCnt);
    }

    public function test_user_seeder_run_successfully(): void
    {
        DatabaseSeeder::$usersCnt = 4;
        $this->assertDatabaseCount(User::class, 0);
        (new UserSeeder)->run();
        $this->assertDatabaseCount(User::class, DatabaseSeeder::$usersCnt + 1);
        $this->assertDatabaseHas(User::class, [
            'name' => 'Test User',
            'username' => 'test',
            'role' => 'admin',
        ]);
    }

    public function test_person_seeder_run_successfully(): void
    {
        DatabaseSeeder::$usersCnt = 5;
        (new UserSeeder)->run();
        (new CountrySeeder)->run();
        DatabaseSeeder::$peopleCnt = 10;
        $this->assertDatabaseCount(Person::class, 0);
        (new PersonSeeder)->run();
        $this->assertDatabaseCount(Person::class, DatabaseSeeder::$peopleCnt);
        // each person should have at least one follower
        $this->assertGreaterThanOrEqual(DatabaseSeeder::$peopleCnt, Following::count());
    }

    public function test_media_seeder_run_successfully(): void
    {
        DatabaseSeeder::$usersCnt = 11;
        (new UserSeeder)->run();
        (new CountrySeeder)->run();
        (new LanguagesSeeder)->run();
        (new GenreSeeder)->run();
        (new CompanySeeder)->run();
        (new CategorySeeder)->run();
        (new KeywordSeeder)->run();
        DatabaseSeeder::$peopleCnt = 10;
        (new PersonSeeder)->run();
        DatabaseSeeder::$mediasCnt = 10;
        $this->assertDatabaseCount(Media::class, 0);
        (new MediaSeeder)->run();
        $this->assertDatabaseCount(Media::class, DatabaseSeeder::$mediasCnt);
        // $this->assertGreaterThanOrEqual(DatabaseSeeder::$mediasCnt/10, DB::table('media_companies')->count());
        $this->assertGreaterThanOrEqual(DatabaseSeeder::$mediasCnt, DB::table('likes')->count());
        $this->assertGreaterThanOrEqual(DatabaseSeeder::$mediasCnt, DB::table('media_genres')->count());
        $this->assertGreaterThanOrEqual(DatabaseSeeder::$mediasCnt, DB::table('media_keywords')->count());
        $this->assertGreaterThanOrEqual(DatabaseSeeder::$mediasCnt, DB::table('media_countries')->count());
        $this->assertGreaterThanOrEqual(DatabaseSeeder::$mediasCnt, DB::table('media_languages')->count());
        // each media has at least 8 stff
        $this->assertGreaterThanOrEqual(DatabaseSeeder::$mediasCnt * 8, MediaStaff::count());
    }

    public function test_review_and_replies_seeder_run_successfully(): void
    {
        DatabaseSeeder::$usersCnt = 11;
        (new UserSeeder)->run();
        (new CountrySeeder)->run();
        (new LanguagesSeeder)->run();
        (new GenreSeeder)->run();
        (new CompanySeeder)->run();
        (new CategorySeeder)->run();
        (new KeywordSeeder)->run();
        DatabaseSeeder::$peopleCnt = 10;
        (new PersonSeeder)->run();
        DatabaseSeeder::$mediasCnt = 10;
        (new MediaSeeder)->run();

        (new ReviewSeeder)->run();
        $this->assertGreaterThanOrEqual(Review::count(), Like::where('likeable_type', '=', Review::class)->count());
        $repliesCnt = Reply::count();
        $this->assertGreaterThanOrEqual(1, $repliesCnt);

        (new ReplySeeder)->run();
        $this->assertGreaterThanOrEqual($repliesCnt + 1, Reply::count());
        $this->assertGreaterThanOrEqual(Reply::count(), Like::where('likeable_type', '=', Reply::class)->count());
    }
}
