<?php

namespace Database\Factories;

use App\Models\RegistrationRequest;
use App\Models\Track;
use App\Models\TrackRegistrationRequest;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TrackRegistrationRequest>
 */
class TrackRegistrationRequestFactory extends Factory
{
    protected $model = TrackRegistrationRequest::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'track_id' => Track::factory(),
            'registration_request_id' => RegistrationRequest::factory(),
            'sort' => fake()->numberBetween(1, 5),
        ];
    }
}
