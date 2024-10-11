<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Following;
use App\Models\Like;
use App\Models\Movie;
use App\Models\Person;
use App\Models\Reply;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Database\Factories\CategoryFactory;
use Database\Factories\MovieFactory;
use Database\Factories\MovieStaffFactory;
use Database\Factories\MovieTagFactory;
use Database\Factories\PersonFactory;
use Database\Factories\ReplyFactory;
use Database\Factories\ReviewFactory;
use Database\Factories\TagFactory;
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
        PersonFactory::times(100)->create();
        Following::factory()->createMany(20);
        MovieFactory::times(50)->create();
        MovieStaffFactory::times(20)->create();
        TagFactory::times(40)->create();
        MovieTagFactory::times(10)->create();
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
