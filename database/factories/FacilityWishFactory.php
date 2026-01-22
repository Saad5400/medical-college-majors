<?php

namespace Database\Factories;

use App\Models\Facility;
use App\Models\FacilityRegistrationRequest;
use App\Models\FacilityWish;
use App\Models\Specialization;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\FacilityWish>
 */
class FacilityWishFactory extends Factory
{
    protected $model = FacilityWish::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'facility_registration_request_id' => FacilityRegistrationRequest::factory(),
            'priority' => fake()->numberBetween(1, 5),
            'facility_id' => Facility::factory(),
            'specialization_id' => Specialization::factory(),
            'custom_facility_name' => null,
            'custom_specialization_name' => null,
            'is_custom' => false,
        ];
    }

    public function custom(): static
    {
        return $this->state(fn (array $attributes) => [
            'facility_id' => null,
            'specialization_id' => null,
            'custom_facility_name' => fake()->company(),
            'custom_specialization_name' => fake()->jobTitle(),
            'is_custom' => true,
        ]);
    }
}
