<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Category>
 */
class CategoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $parentId = null;
        if ($this->faker->boolean(30)){
            $parentId = $this->faker->numberBetween(1,Category::count()+1);
        }
        return [
            'name' => $this->faker->unique()->word(),
            'has_items' => 1,
            'parent_id' => $parentId,
        ];
    }
}
