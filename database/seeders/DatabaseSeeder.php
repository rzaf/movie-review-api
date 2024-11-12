<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Following;
use App\Models\Like;
use App\Models\Person;
use App\Models\Reply;
use App\Models\User;
use Database\Factories\MovieFactory;
use Database\Factories\MovieStaffFactory;
use Database\Factories\ReviewFactory;
use Database\Factories\GenreFactory;
use Database\Factories\MovieGenreFactory;
use Database\Factories\UserFactory;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        UserFactory::times(100)->create();
        Category::factory()->createMany(25);
        Person::factory()->createMany(100);
        Following::factory()->createMany(20);
        MovieFactory::times(100)->create();
        MovieStaffFactory::times(20)->create();
        GenreFactory::times(40)->create();
        MovieGenreFactory::times(10)->create();
        ReviewFactory::times(40)->create();
        Reply::factory()->createMany(20);
        Like::factory()->createMany(20);
        User::factory()->create([
            'name' => 'Test User',
            'username' => 'test',
            'password' => '1234',
            'role' => 'admin',
            'email' => 'test@example.com',
        ]);
    }
}
