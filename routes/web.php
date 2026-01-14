<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    $user = auth()->user();

    if ($user === null) {
        return redirect('/login');
    }

    if ($user->hasRole('admin')) {
        return redirect(\App\Filament\Pages\Dashboard::getUrl());
    }

    if ($user->registrationRequests()->exists()) {
        return redirect(\App\Filament\Resources\RegistrationRequestResource::getUrl('edit', ['record' => $user->registrationRequests()->first()->id]));
    }

    return redirect(\App\Filament\Resources\RegistrationRequestResource::getUrl('create'));
});
