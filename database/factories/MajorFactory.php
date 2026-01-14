<?php

namespace Database\Factories;

use App\Models\Major;
use Illuminate\Database\Eloquent\Factories\Factory;

class MajorFactory extends Factory
{
    protected $model = Major::class;

    public function definition(): array
    {
        return [
            'name' => fake()->words(2, true),
            'max_users' => fake()->numberBetween(10, 100),
        ];
    }
}
