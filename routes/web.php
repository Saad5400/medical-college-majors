<?php

use App\Settings\RegistrationSettings;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    $user = auth()->user();

    if ($user === null) {
        return redirect('/login');
    }

    if ($user->hasRole('admin')) {
        return redirect(\App\Filament\Pages\Dashboard::getUrl());
    }

    $settings = app(RegistrationSettings::class);

    if ($settings->track_registration_open) {
        if ($user->registrationRequests()->exists()) {
            return redirect(\App\Filament\Resources\RegistrationRequestResource::getUrl('edit', ['record' => $user->registrationRequests()->first()->id]));
        }

        return redirect(\App\Filament\Resources\RegistrationRequestResource::getUrl('create'));
    }

    if ($settings->facility_registration_open) {
        return redirect(\App\Filament\Resources\FacilityRegistrationRequestResource::getUrl('index'));
    }

    return redirect(\App\Filament\Pages\Dashboard::getUrl());
});
