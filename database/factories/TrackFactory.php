<?php

namespace Database\Factories;

use App\Models\Track;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Track>
 */
class TrackFactory extends Factory
{
    protected $model = Track::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $electiveMonths = fake()->randomElements(range(1, 12), fake()->numberBetween(0, 4));
        sort($electiveMonths);

        return [
            'name' => fake()->unique()->words(2, true),
            'max_users' => fake()->numberBetween(10, 30),
            'is_leader_only' => fake()->boolean(20),
            'elective_months' => array_values($electiveMonths),
        ];
    }
}
