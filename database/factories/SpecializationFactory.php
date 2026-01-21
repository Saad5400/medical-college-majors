<?php

namespace Database\Factories;

use App\Enums\FacilityType;
use App\Models\Specialization;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Specialization>
 */
class SpecializationFactory extends Factory
{
    protected $model = Specialization::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $facilityType = fake()->randomElement(FacilityType::cases());

        return [
            'name' => fake()->unique()->jobTitle(),
            'duration_months' => fake()->numberBetween(1, 2),
            'facility_type' => $facilityType->value,
        ];
    }
}
