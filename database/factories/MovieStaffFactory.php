<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Movie;
use App\Models\MovieStaff;
use App\Models\Person;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Movie>
 */
class MovieStaffFactory extends Factory
{
    static private $jobs = [
        'director',
        'producer',
        'writer',
        'actor',
        'music',
    ];

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $resets = 0;
        $movieId = 1;
        $personId = 1;
        $job = '';

        while ($resets < 10) {
            $movieId = $this->faker->numberBetween(1, Movie::count());
            $personId = $this->faker->numberBetween(1, Person::count());
            $job = $this->faker->randomElement(self::$jobs);
            if (!MovieStaff::where(['movie_id' => $movieId, 'person_id' => $personId, 'job' => $job,])->exists()) {
                break;
            }
            $resets++;
        }
        return [
            'movie_id' => $movieId,
            'person_id' => $personId,
            'job' => $job,
        ];
    }
}
