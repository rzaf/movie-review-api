<?php

namespace Tests\Unit;

use Artisan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class MigrationsTest extends TestCase
{

    private function allTablesExist(): bool
    {
        return Schema::hasTable('users')
            &&
            Schema::hasTable('personal_access_tokens')
            &&
            Schema::hasTable('categories')
            &&
            Schema::hasTable('medias')
            &&
            Schema::hasTable('media_reviews')
            &&
            Schema::hasTable('review_replies')
            &&
            Schema::hasTable('people')
            &&
            Schema::hasTable('followings')
            &&
            Schema::hasTable('media_actors')
            &&
            Schema::hasTable('countries')
            &&
            Schema::hasTable('media_countries')
            &&
            Schema::hasTable('genres')
            &&
            Schema::hasTable('media_genres')
            &&
            Schema::hasTable('languages')
            &&
            Schema::hasTable('media_languages')
            &&
            Schema::hasTable('keywords')
            &&
            Schema::hasTable('media_keywords')
            &&
            Schema::hasTable('companies')
            &&
            Schema::hasTable('media_companies')
            &&
            Schema::hasTable('likes')
            &&
            Schema::hasTable('photos')
            &&
            Schema::hasTable('videos');
    }

    private function allTablesMissing(): bool
    {
        return !Schema::hasTable('users')
            &&
            !Schema::hasTable('personal_access_tokens')
            &&
            !Schema::hasTable('categories')
            &&
            !Schema::hasTable('medias')
            &&
            !Schema::hasTable('media_reviews')
            &&
            !Schema::hasTable('review_replies')
            &&
            !Schema::hasTable('people')
            &&
            !Schema::hasTable('followings')
            &&
            !Schema::hasTable('media_actors')
            &&
            !Schema::hasTable('countries')
            &&
            !Schema::hasTable('media_countries')
            &&
            !Schema::hasTable('genres')
            &&
            !Schema::hasTable('media_genres')
            &&
            !Schema::hasTable('languages')
            &&
            !Schema::hasTable('media_languages')
            &&
            !Schema::hasTable('keywords')
            &&
            !Schema::hasTable('media_keywords')
            &&
            !Schema::hasTable('companies')
            &&
            !Schema::hasTable('media_companies')
            &&
            !Schema::hasTable('likes')
            &&
            !Schema::hasTable('photos')
            &&
            !Schema::hasTable('videos');
    }

    public function test_that_migrations_run_succusfully(): void
    {
        Artisan::call('migrate:fresh');
        $this->assertTrue($this->allTablesExist());
        Artisan::call('migrate:reset');
        $this->assertTrue($this->allTablesMissing());
    }
}
