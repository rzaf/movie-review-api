<?php

namespace Database\Factories;

use App\Models\Person;
use Carbon\Carbon;
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
        $resets = 0;
        $birthDate = $this->faker->date(max: Date::createFromDate(2005, 1, 1));
        $fname = $this->faker->firstName();
        $lname = $this->faker->lastName();
        $url0 = sprintf('%s-%s', strtolower($fname), strtolower($lname));
        $url = $url0;
        while ($resets < 10) {
            if (!Person::where(['url' => $url])->exists()) {
                break;
            }
            if ($resets == 0) {
                $url = sprintf('%s%d', $url0, Carbon::parse($birthDate)->yearOfCentury());
            } else {
                $url = sprintf("%s-%d", $url0, $resets);
            }
            $resets++;
        }
        return [
            'name' => "$fname $lname",
            'url' => $url,
            'is_male' => $this->faker->boolean(),
            'about' => $this->faker->paragraph(5),
            'birth_date' => $birthDate,
            'birth_country' => $this->faker->numberBetween(1, 200),
        ];
    }
}
