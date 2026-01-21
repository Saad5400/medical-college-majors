<?php

namespace Database\Factories;

use App\Enums\FacilityType;
use App\Models\Facility;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Facility>
 */
class FacilityFactory extends Factory
{
    protected $model = Facility::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $facilityType = fake()->randomElement(FacilityType::cases());

        return [
            'name' => fake()->unique()->company(),
            'type' => $facilityType->value,
        ];
    }
}
