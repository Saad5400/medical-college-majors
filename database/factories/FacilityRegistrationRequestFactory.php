<?php

namespace Database\Factories;

use App\Models\Facility;
use App\Models\FacilityRegistrationRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\FacilityRegistrationRequest>
 */
class FacilityRegistrationRequestFactory extends Factory
{
    protected $model = FacilityRegistrationRequest::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'month_index' => fake()->numberBetween(1, 12),
            'assigned_facility_id' => null,
        ];
    }

    public function assigned(): static
    {
        return $this->state(fn (array $attributes) => [
            'assigned_facility_id' => Facility::factory(),
        ]);
    }
}
