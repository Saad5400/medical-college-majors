<?php

namespace Database\Seeders;

use App\Models\Facility;
use App\Models\FacilityRegistrationRequest;
use App\Models\FacilitySeat;
use App\Models\FacilityWish;
use App\Models\RegistrationRequest;
use App\Models\Specialization;
use App\Models\Track;
use App\Models\TrackRegistrationRequest;
use App\Models\TrackSpecialization;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;

class TestDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tracks = $this->seedTracks();
        $specializations = $this->seedSpecializations();
        $facilities = $this->seedFacilities();

        $this->seedTrackSpecializations($tracks, $specializations);
        $this->seedFacilitySeats($facilities, $specializations);

        $users = $this->seedUsers($tracks);

        $this->seedRegistrationRequests($users, $tracks);
        $this->seedFacilityRegistrationRequests($users, $facilities, $specializations);
    }

    /**
     * @return \Illuminate\Support\Collection<int, \App\Models\Track>
     */
    private function seedTracks(): Collection
    {
        $tracks = Track::query()->get();

        if ($tracks->isEmpty()) {
            $tracks = Track::factory()->count(6)->create();
        }

        return $tracks;
    }

    /**
     * @return \Illuminate\Support\Collection<int, \App\Models\Specialization>
     */
    private function seedSpecializations(): Collection
    {
        $specializations = Specialization::query()->get();

        if ($specializations->isEmpty()) {
            $specializations = Specialization::factory()->count(12)->create();
        }

        return $specializations;
    }

    /**
     * @return \Illuminate\Support\Collection<int, \App\Models\Facility>
     */
    private function seedFacilities(): Collection
    {
        $facilities = Facility::query()->get();

        if ($facilities->isEmpty()) {
            $facilities = Facility::factory()->count(8)->create();
        }

        return $facilities;
    }

    /**
     * @param  \Illuminate\Support\Collection<int, \App\Models\Track>  $tracks
     * @param  \Illuminate\Support\Collection<int, \App\Models\Specialization>  $specializations
     */
    private function seedTrackSpecializations(Collection $tracks, Collection $specializations): void
    {
        if ($tracks->isEmpty() || $specializations->isEmpty() || TrackSpecialization::query()->exists()) {
            return;
        }

        foreach ($tracks as $track) {
            $monthCount = fake()->numberBetween(6, 10);
            $monthIndices = collect(range(1, 12))
                ->shuffle()
                ->take($monthCount)
                ->sort()
                ->values();

            foreach ($monthIndices as $monthIndex) {
                $specialization = $specializations->random();

                TrackSpecialization::factory()->create([
                    'track_id' => $track->id,
                    'specialization_id' => $specialization->id,
                    'month_index' => $monthIndex,
                ]);
            }
        }
    }

    /**
     * @param  \Illuminate\Support\Collection<int, \App\Models\Facility>  $facilities
     * @param  \Illuminate\Support\Collection<int, \App\Models\Specialization>  $specializations
     */
    private function seedFacilitySeats(Collection $facilities, Collection $specializations): void
    {
        if ($facilities->isEmpty() || $specializations->isEmpty() || FacilitySeat::query()->exists()) {
            return;
        }

        foreach ($facilities as $facility) {
            $matchingSpecializations = $specializations->filter(
                fn (Specialization $specialization) => $specialization->facility_type === $facility->type
            );

            $selectedSpecializations = $matchingSpecializations->isNotEmpty()
                ? $matchingSpecializations->shuffle()->take(fake()->numberBetween(3, 6))
                : $specializations->shuffle()->take(fake()->numberBetween(3, 6));

            foreach ($selectedSpecializations as $specialization) {
                $monthIndices = collect(range(1, 12))->shuffle()->take(fake()->numberBetween(2, 4));

                foreach ($monthIndices as $monthIndex) {
                    FacilitySeat::factory()->create([
                        'facility_id' => $facility->id,
                        'specialization_id' => $specialization->id,
                        'month_index' => $monthIndex,
                        'max_seats' => fake()->numberBetween(2, 10),
                    ]);
                }
            }
        }
    }

    /**
     * @param  \Illuminate\Support\Collection<int, \App\Models\Track>  $tracks
     * @return \Illuminate\Support\Collection<int, \App\Models\User>
     */
    private function seedUsers(Collection $tracks): Collection
    {
        $users = User::query()->get();
        $targetCount = 30;

        if ($users->count() >= $targetCount) {
            return $users;
        }

        $trackIds = $tracks->pluck('id');
        $additional = $targetCount - $users->count();

        $newUsers = User::factory()
            ->count($additional)
            ->state(fn () => [
                'track_id' => $trackIds->isNotEmpty() ? (string) $trackIds->random() : null,
            ])
            ->create();

        return $users->merge($newUsers);
    }

    /**
     * @param  \Illuminate\Support\Collection<int, \App\Models\User>  $users
     * @param  \Illuminate\Support\Collection<int, \App\Models\Track>  $tracks
     */
    private function seedRegistrationRequests(Collection $users, Collection $tracks): void
    {
        if ($users->isEmpty() || $tracks->isEmpty() || RegistrationRequest::query()->exists()) {
            return;
        }

        $selectedUsers = $users->shuffle()->take(min($users->count(), 15));

        foreach ($selectedUsers as $user) {
            $request = RegistrationRequest::factory()->for($user)->create();
            $trackCount = $tracks->count();
            $selectionCount = $trackCount > 1
                ? fake()->numberBetween(2, min(5, $trackCount))
                : 1;
            $selectedTracks = $tracks->shuffle()->take($selectionCount);
            $sort = 1;

            foreach ($selectedTracks as $track) {
                TrackRegistrationRequest::factory()->create([
                    'track_id' => $track->id,
                    'registration_request_id' => $request->id,
                    'sort' => $sort,
                ]);

                $sort++;
            }
        }
    }

    /**
     * @param  \Illuminate\Support\Collection<int, \App\Models\User>  $users
     * @param  \Illuminate\Support\Collection<int, \App\Models\Facility>  $facilities
     * @param  \Illuminate\Support\Collection<int, \App\Models\Specialization>  $specializations
     */
    private function seedFacilityRegistrationRequests(
        Collection $users,
        Collection $facilities,
        Collection $specializations
    ): void {
        if ($users->isEmpty() || $facilities->isEmpty() || FacilityRegistrationRequest::query()->exists()) {
            return;
        }

        $selectedUsers = $users->shuffle()->take(min($users->count(), 20));

        foreach ($selectedUsers as $user) {
            $monthIndices = collect(range(1, 12))->shuffle()->take(fake()->numberBetween(1, 3));

            foreach ($monthIndices as $monthIndex) {
                $requestFactory = FacilityRegistrationRequest::factory()
                    ->for($user)
                    ->state(['month_index' => $monthIndex]);

                if (fake()->boolean(25)) {
                    $requestFactory = $requestFactory->state([
                        'assigned_facility_id' => $facilities->random()->id,
                    ]);
                }

                $request = $requestFactory->create();

                $this->seedFacilityWishes($request, $facilities, $specializations);
            }
        }
    }

    /**
     * @param  \Illuminate\Support\Collection<int, \App\Models\Facility>  $facilities
     * @param  \Illuminate\Support\Collection<int, \App\Models\Specialization>  $specializations
     */
    private function seedFacilityWishes(
        FacilityRegistrationRequest $request,
        Collection $facilities,
        Collection $specializations
    ): void {
        $wishCount = fake()->numberBetween(3, 5);
        $competitiveCount = fake()->numberBetween(1, min(3, $wishCount));

        for ($priority = 1; $priority <= $wishCount; $priority++) {
            if ($priority > $competitiveCount) {
                FacilityWish::factory()->custom()->create([
                    'facility_registration_request_id' => $request->id,
                    'priority' => $priority,
                ]);

                continue;
            }

            $facility = $facilities->random();
            $matchingSpecializations = $specializations->filter(
                fn (Specialization $specialization) => $specialization->facility_type === $facility->type
            );
            $specialization = $matchingSpecializations->isNotEmpty()
                ? $matchingSpecializations->random()
                : $specializations->random();

            FacilityWish::factory()->create([
                'facility_registration_request_id' => $request->id,
                'priority' => $priority,
                'facility_id' => $facility->id,
                'specialization_id' => $specialization->id,
                'custom_facility_name' => null,
                'custom_specialization_name' => null,
                'is_custom' => false,
                'is_competitive' => true,
            ]);
        }
    }
}
