<?php

namespace Database\Factories;

use App\Models\Media;
use Carbon\Carbon;
use Database\Seeders\DatabaseSeeder;
use Date;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Media>
 */
class MediaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = $this->faker->unique()->words(rand(1, 4));
        return [
            'name' => implode(' ', $name),
            'url' => implode('_', $name),
            'release_date' => $this->faker->date(max: Date::createFromDate(2024, 1, 1)),
            'summary' => $this->faker->sentences(rand(3, 6), true),
            'storyline' => $this->faker->sentences(rand(10, 25), true),
            'category_id' => rand(1, DatabaseSeeder::$categoriesCnt),
        ];
    }
}
