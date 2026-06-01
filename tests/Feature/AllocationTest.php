<?php

/*
|--------------------------------------------------------------------------
| Allocation algorithm tests (report §7.2 cases M-1 .. M-6)
|--------------------------------------------------------------------------
|
| These exercise the REAL production allocation code by driving the two
| admin "Distribute" Filament actions through Livewire:
|   - Facility distribution: ListFacilityRegistrationRequests::distribute (per month)
|   - Track distribution:    ListTracks::distribute
|
| Nothing is re-implemented here — the assertions run against the database
| state produced by the actual action closures.
|
*/

use App\Filament\Resources\FacilityRegistrationRequestResource\Pages\ListFacilityRegistrationRequests;
use App\Filament\Resources\TrackResource\Pages\ListTracks;
use App\Models\Facility;
use App\Models\FacilityRegistrationRequest;
use App\Models\FacilitySeat;
use App\Models\FacilityWish;
use App\Models\RegistrationRequest;
use App\Models\Specialization;
use App\Models\Track;
use App\Models\TrackSpecialization;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

uses(RefreshDatabase::class);

const MONTH = 7;

beforeEach(function () {
    // The 'student'/'leader'/'data-entry' roles are seeded by migrations;
    // 'admin' is not, so create it and act as an admin to pass the action's
    // ->visible(fn () => auth()->user()->hasRole('admin')) gate.
    Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
    app(PermissionRegistrar::class)->forgetCachedPermissions();

    $admin = User::factory()->create(['gpa' => 5]);
    $admin->assignRole('admin');
    $this->actingAs($admin);

    Filament::setCurrentPanel(Filament::getPanel('admin'));
});

/*
|--------------------------------------------------------------------------
| Helpers
|--------------------------------------------------------------------------
*/

/**
 * Build one student's monthly facility request with a ranked wish list.
 *
 * @param  array<int, array{facility?: ?Facility, spec?: ?Specialization, custom?: bool}>  $wishes
 *         (index order = priority order, 1-based)
 */
function makeFacilityRequest(float $gpa, array $wishes, int $month = MONTH): FacilityRegistrationRequest
{
    $user = User::factory()->create(['gpa' => $gpa]);

    $request = FacilityRegistrationRequest::factory()->create([
        'user_id' => $user->id,
        'month_index' => $month,
        'assigned_facility_id' => null,
        'assigned_specialization_id' => null,
    ]);

    foreach ($wishes as $i => $wish) {
        $isCustom = $wish['custom'] ?? false;

        FacilityWish::factory()->create([
            'facility_registration_request_id' => $request->id,
            'priority' => $i + 1,
            'facility_id' => $isCustom ? null : ($wish['facility'] ?? null)?->id,
            'specialization_id' => $isCustom ? null : ($wish['spec'] ?? null)?->id,
            'custom_facility_name' => $isCustom ? 'Self-arranged clinic' : null,
            'custom_specialization_name' => $isCustom ? 'Self-arranged rotation' : null,
            'is_custom' => $isCustom,
        ]);
    }

    return $request->refresh();
}

function seat(Facility $facility, Specialization $spec, int $maxSeats, int $month = MONTH): FacilitySeat
{
    return FacilitySeat::factory()->create([
        'facility_id' => $facility->id,
        'specialization_id' => $spec->id,
        'month_index' => $month,
        'max_seats' => $maxSeats,
    ]);
}

function distributeFacilities(int $month = MONTH): void
{
    Livewire::test(ListFacilityRegistrationRequests::class)
        ->callAction('distribute', ['month_index' => $month])
        ->assertHasNoActionErrors();
}

function distributeTracks(): void
{
    Livewire::test(ListTracks::class)
        ->callAction('distribute')
        ->assertHasNoActionErrors();
}

/*
|--------------------------------------------------------------------------
| M-1 — GPA priority for the last seat (facility round)
|--------------------------------------------------------------------------
*/

test('M-1: the higher-GPA student wins the last contested facility seat', function () {
    $facility = Facility::factory()->create();
    $spec = Specialization::factory()->create(['duration_months' => 1]);
    seat($facility, $spec, maxSeats: 1); // only ONE seat

    $high = makeFacilityRequest(4.80, [['facility' => $facility, 'spec' => $spec]]);
    $low = makeFacilityRequest(3.20, [['facility' => $facility, 'spec' => $spec]]);

    distributeFacilities();

    // Higher GPA takes the single seat; lower GPA is left unassigned.
    expect($high->fresh()->assigned_facility_id)->toBe($facility->id)
        ->and($high->fresh()->assigned_specialization_id)->toBe($spec->id)
        ->and($low->fresh()->assigned_facility_id)->toBeNull()
        ->and($low->fresh()->assigned_specialization_id)->toBeNull();
});

/*
|--------------------------------------------------------------------------
| M-2 — Capacity is never exceeded
|--------------------------------------------------------------------------
*/

test('M-2: no facility/specialization cell ever exceeds max_seats', function () {
    $facility = Facility::factory()->create();
    $spec = Specialization::factory()->create(['duration_months' => 1]);
    seat($facility, $spec, maxSeats: 2); // demand (4) > seats (2)

    $requests = collect([4.90, 4.50, 3.90, 3.10])
        ->map(fn ($gpa) => makeFacilityRequest($gpa, [['facility' => $facility, 'spec' => $spec]]));

    distributeFacilities();

    $assigned = FacilityRegistrationRequest::where('month_index', MONTH)
        ->whereNotNull('assigned_facility_id')
        ->get();

    // Exactly max_seats placed — capacity respected.
    expect($assigned)->toHaveCount(2);

    // And the two winners are the two highest GPAs (4.90, 4.50).
    $winnerGpas = $assigned->map(fn ($r) => (float) $r->user->gpa)->sort()->values()->all();
    expect($winnerGpas)->toBe([4.50, 4.90]);
});

/*
|--------------------------------------------------------------------------
| M-3 — Fall back to the next wish when the top wish is full
|--------------------------------------------------------------------------
*/

test('M-3: a student whose top wish is full falls through to the next wish', function () {
    $spec = Specialization::factory()->create(['duration_months' => 1]);
    $top = Facility::factory()->create();      // 1 seat — taken by the top-GPA student
    $fallback = Facility::factory()->create();  // roomy fallback

    seat($top, $spec, maxSeats: 1);
    seat($fallback, $spec, maxSeats: 5);

    $first = makeFacilityRequest(4.90, [
        ['facility' => $top, 'spec' => $spec],
        ['facility' => $fallback, 'spec' => $spec],
    ]);
    $second = makeFacilityRequest(4.10, [
        ['facility' => $top, 'spec' => $spec],       // priority 1 — now full
        ['facility' => $fallback, 'spec' => $spec],   // priority 2 — should win
    ]);

    distributeFacilities();

    expect($first->fresh()->assigned_facility_id)->toBe($top->id)
        ->and($second->fresh()->assigned_facility_id)->toBe($fallback->id)
        ->and($second->fresh()->assigned_specialization_id)->toBe($spec->id);
});

/*
|--------------------------------------------------------------------------
| M-4 — A custom wish (and everything ranked after it) exits the competition
|--------------------------------------------------------------------------
*/

test('M-4: a custom wish at rank 3 makes rank-4+ wishes non-competitive', function () {
    $spec = Specialization::factory()->create(['duration_months' => 1]);

    $full1 = Facility::factory()->create();
    $full2 = Facility::factory()->create();
    $available = Facility::factory()->create();

    // Ranks 1 & 2 exist but have zero capacity; rank 4 has free seats.
    seat($full1, $spec, maxSeats: 0);
    seat($full2, $spec, maxSeats: 0);
    seat($available, $spec, maxSeats: 5);

    $request = makeFacilityRequest(4.50, [
        ['facility' => $full1, 'spec' => $spec],  // priority 1 — full
        ['facility' => $full2, 'spec' => $spec],  // priority 2 — full
        ['custom' => true],                        // priority 3 — custom => cut-off
        ['facility' => $available, 'spec' => $spec], // priority 4 — IGNORED
    ]);

    // Only the two pre-custom wishes are competitive.
    expect($request->competitiveWishes)->toHaveCount(2);

    distributeFacilities();

    // The available facility at rank 4 was NOT used because the custom wish
    // ended the competition first => the student stays unassigned.
    expect($request->fresh()->assigned_facility_id)->toBeNull();

    $usedSeats = FacilityRegistrationRequest::where('assigned_facility_id', $available->id)->count();
    expect($usedSeats)->toBe(0);
});

/*
|--------------------------------------------------------------------------
| M-5 — Elective-month specialization resolution
|--------------------------------------------------------------------------
*/

test('M-5: an elective wish uses the student-chosen specialization, not the track schedule', function () {
    $facility = Facility::factory()->create();
    $chosenSpec = Specialization::factory()->create(['duration_months' => 1]);
    $trackSpec = Specialization::factory()->create(['duration_months' => 1]);

    // Capacity exists ONLY for the chosen (elective) specialization.
    seat($facility, $chosenSpec, maxSeats: 1);
    seat($facility, $trackSpec, maxSeats: 1);

    // Give the student a track whose schedule maps MONTH to a DIFFERENT spec,
    // so we can prove the chosen elective spec wins over the derived one.
    $track = Track::factory()->create(['elective_months' => [MONTH]]);
    TrackSpecialization::factory()->create([
        'track_id' => $track->id,
        'specialization_id' => $trackSpec->id,
        'month_index' => MONTH,
    ]);

    $request = makeFacilityRequest(4.50, [
        ['facility' => $facility, 'spec' => $chosenSpec], // elective pick
    ]);
    $request->user->update(['track_id' => $track->id]);

    distributeFacilities();

    expect($request->fresh()->assigned_facility_id)->toBe($facility->id)
        ->and($request->fresh()->assigned_specialization_id)->toBe($chosenSpec->id);
});

test('M-5b: a non-elective wish derives the specialization from the track schedule (duration-aware)', function () {
    $facility = Facility::factory()->create();
    $scheduledSpec = Specialization::factory()->create(['duration_months' => 2]); // spans months 6-7

    // Capacity keyed on the derived specialization.
    seat($facility, $scheduledSpec, maxSeats: 1);

    // Schedule starts at month 6 with a 2-month specialization => covers MONTH (7).
    $track = Track::factory()->create(['elective_months' => []]);
    TrackSpecialization::factory()->create([
        'track_id' => $track->id,
        'specialization_id' => $scheduledSpec->id,
        'month_index' => 6,
    ]);

    // Wish carries NO specialization_id => must be derived from the schedule.
    $request = makeFacilityRequest(4.50, [
        ['facility' => $facility, 'spec' => null],
    ]);
    $request->user->update(['track_id' => $track->id]);

    distributeFacilities();

    expect($request->fresh()->assigned_facility_id)->toBe($facility->id)
        ->and($request->fresh()->assigned_specialization_id)->toBe($scheduledSpec->id);
});

/*
|--------------------------------------------------------------------------
| M-6 — Re-running distribution is idempotent (assignments reset first)
|--------------------------------------------------------------------------
*/

test('M-6: re-running facility distribution yields a stable, non-duplicated result', function () {
    $facility = Facility::factory()->create();
    $spec = Specialization::factory()->create(['duration_months' => 1]);
    seat($facility, $spec, maxSeats: 2);

    collect([4.90, 4.50, 3.90, 3.10])
        ->each(fn ($gpa) => makeFacilityRequest($gpa, [['facility' => $facility, 'spec' => $spec]]));

    distributeFacilities();
    $firstRun = FacilityRegistrationRequest::where('month_index', MONTH)
        ->orderBy('id')
        ->pluck('assigned_facility_id', 'id')
        ->all();

    distributeFacilities(); // run again
    $secondRun = FacilityRegistrationRequest::where('month_index', MONTH)
        ->orderBy('id')
        ->pluck('assigned_facility_id', 'id')
        ->all();

    expect($secondRun)->toBe($firstRun);

    // Still exactly max_seats placed — no double-booking from the second run.
    $placed = FacilityRegistrationRequest::where('month_index', MONTH)
        ->whereNotNull('assigned_facility_id')
        ->count();
    expect($placed)->toBe(2);
});

/*
|--------------------------------------------------------------------------
| Track-round coverage (the other half of the allocation feature)
|--------------------------------------------------------------------------
*/

test('M-1/M-3 (tracks): GPA decides the last seat and losers fall back to the next track', function () {
    $popular = Track::factory()->create(['max_users' => 1, 'is_leader_only' => false]);
    $fallback = Track::factory()->create(['max_users' => 5, 'is_leader_only' => false]);

    $build = function (float $gpa) use ($popular, $fallback) {
        $user = User::factory()->create(['gpa' => $gpa]);
        $user->assignRole('student');
        $rr = RegistrationRequest::factory()->create(['user_id' => $user->id]);
        $rr->tracks()->attach([
            $popular->id => ['sort' => 1],
            $fallback->id => ['sort' => 2],
        ]);

        return $user;
    };

    $high = $build(4.90);
    $low = $build(3.50);

    distributeTracks();

    expect($high->fresh()->track_id)->toBe($popular->id)   // wins the single seat
        ->and($low->fresh()->track_id)->toBe($fallback->id); // falls back
});

test('M-6 (tracks): re-running track distribution is idempotent', function () {
    $popular = Track::factory()->create(['max_users' => 1, 'is_leader_only' => false]);
    $fallback = Track::factory()->create(['max_users' => 5, 'is_leader_only' => false]);

    foreach ([4.90, 3.50] as $gpa) {
        $user = User::factory()->create(['gpa' => $gpa]);
        $user->assignRole('student');
        $rr = RegistrationRequest::factory()->create(['user_id' => $user->id]);
        $rr->tracks()->attach([$popular->id => ['sort' => 1], $fallback->id => ['sort' => 2]]);
    }

    distributeTracks();
    $first = User::role('student')->orderBy('id')->pluck('track_id', 'id')->all();

    distributeTracks();
    $second = User::role('student')->orderBy('id')->pluck('track_id', 'id')->all();

    expect($second)->toBe($first);
    expect(User::where('track_id', $popular->id)->count())->toBe(1); // capacity still respected
});
