<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Movie>
 */
class MovieFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->words(rand(1,4),true),
            'url' =>$this->faker->unique()->word(),
            'release_year'=>$this->faker->numberBetween(1970,2020),
            'category_id' => $this->faker->numberBetween(1,Category::count()),
        ];
    }
}
