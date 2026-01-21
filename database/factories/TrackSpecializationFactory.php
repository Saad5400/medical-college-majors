<?php

namespace Database\Factories;

use App\Models\Specialization;
use App\Models\Track;
use App\Models\TrackSpecialization;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TrackSpecialization>
 */
class TrackSpecializationFactory extends Factory
{
    protected $model = TrackSpecialization::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'track_id' => Track::factory(),
            'specialization_id' => Specialization::factory(),
            'month_index' => fake()->numberBetween(1, 12),
        ];
    }
}
