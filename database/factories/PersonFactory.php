<?php

namespace Database\Factories;

use App\Models\Category;
use Date;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Person>
 */
class PersonFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->word(),
            'is_male' => $this->faker->boolean(),
            'birth_date' => $this->faker->date(max:Date::createFromDate(2005,1,1)),
        ];
    }
}
