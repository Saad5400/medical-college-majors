<?php

use App\Filament\Resources\TrackResource\Pages\TrackSchedule;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

use function Pest\Livewire\livewire;

uses(RefreshDatabase::class);

it('loads the track schedule page for authorized users', function () {
    app(PermissionRegistrar::class)->forgetCachedPermissions();

    $user = User::factory()->create();
    $user->assignRole(Role::findByName('data-entry'));

    $this->actingAs($user);
    Filament::setCurrentPanel('admin');

    livewire(TrackSchedule::class)
        ->assertOk();
});
