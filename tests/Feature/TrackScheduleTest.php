<?php

use App\Models\Specialization;
use App\Models\Track;
use App\Models\TrackSpecialization;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('expands specialization months using duration', function () {
    $track = Track::factory()->create();
    $specialization = Specialization::factory()->create([
        'duration_months' => 3,
    ]);

    TrackSpecialization::factory()->create([
        'track_id' => $track->id,
        'specialization_id' => $specialization->id,
        'month_index' => 2,
    ]);

    expect($track->getSpecializationMonths())->toEqual([2, 3, 4]);
});

it('detects elective month conflicts with specializations', function () {
    $track = Track::factory()->create();
    $specialization = Specialization::factory()->create([
        'duration_months' => 2,
    ]);

    TrackSpecialization::factory()->create([
        'track_id' => $track->id,
        'specialization_id' => $specialization->id,
        'month_index' => 4,
    ]);

    $conflicts = $track->getConflictingElectiveMonths([3, 4, 5, 13]);

    expect($conflicts)->toEqual([4, 5]);
});
