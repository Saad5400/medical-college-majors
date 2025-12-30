<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    $user = auth()->user();

    if ($user === null) {
        return redirect('/login');
    }

    if ($user->hasRole('admin')) {
        return redirect(\App\Filament\Resources\MajorResource::getUrl());
    }

    // Check email verification for non-admin users
    if (!$user->hasVerifiedEmail()) {
        return redirect('/email/verify');
    }

    if ($user->registrationRequests()->exists()) {
        return redirect(\App\Filament\Resources\RegistrationRequestResource::getUrl('edit', ['record' => $user->registrationRequests()->first()->id]));
    }

    return redirect(\App\Filament\Resources\RegistrationRequestResource::getUrl('create'));
});
