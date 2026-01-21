<?php

namespace Database\Factories;

use App\Models\Facility;
use App\Models\FacilitySeat;
use App\Models\Specialization;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\FacilitySeat>
 */
class FacilitySeatFactory extends Factory
{
    protected $model = FacilitySeat::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'facility_id' => Facility::factory(),
            'specialization_id' => Specialization::factory(),
            'month_index' => fake()->numberBetween(1, 12),
            'max_seats' => fake()->numberBetween(1, 12),
        ];
    }
}
