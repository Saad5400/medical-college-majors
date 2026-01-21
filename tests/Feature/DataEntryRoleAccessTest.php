<?php

use App\Models\Facility;
use App\Models\FacilityRegistrationRequest;
use App\Models\FacilitySeat;
use App\Models\RegistrationRequest;
use App\Models\Specialization;
use App\Models\Track;
use App\Models\TrackSpecialization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

uses(RefreshDatabase::class);

beforeEach(function () {
    app(PermissionRegistrar::class)->forgetCachedPermissions();
    Permission::firstOrCreate(['name' => 'view-any User', 'guard_name' => 'web']);
});

it('allows data-entry role to manage reference data', function (string $modelClass) {
    $user = User::factory()->create();
    $user->assignRole(Role::findByName('data-entry'));

    expect($user->can('viewAny', $modelClass))->toBeTrue()
        ->and($user->can('create', $modelClass))->toBeTrue();
})->with([
    Facility::class,
    FacilitySeat::class,
    Specialization::class,
    Track::class,
    TrackSpecialization::class,
]);

it('blocks data-entry role from users and registration requests', function (string $modelClass) {
    $user = User::factory()->create();
    $user->assignRole(Role::findByName('data-entry'));

    expect($user->can('viewAny', $modelClass))->toBeFalse();
})->with([
    User::class,
    RegistrationRequest::class,
    FacilityRegistrationRequest::class,
]);

it('allows data-entry users with student role to view registration requests', function () {
    $user = User::factory()->create();
    $user->assignRole(Role::findByName('data-entry'));
    $user->assignRole(Role::findByName('student'));

    expect($user->can('viewAny', RegistrationRequest::class))->toBeTrue()
        ->and($user->can('viewAny', FacilityRegistrationRequest::class))->toBeTrue();
});
