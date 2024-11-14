<?php

namespace Database\Seeders;

use App\Models\User;
use Database\Factories\UserFactory;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::factory()->times(DatabaseSeeder::$usersCnt)->create();
        User::factory()->create([
            'name' => 'Test User',
            'username' => 'test',
            'password' => '1234',
            'role' => 'admin',
            'email' => 'test@example.com',
        ]);
    }
}
