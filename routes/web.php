<?php

use App\Settings\RegistrationSettings;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    $user = auth()->user();

    if ($user === null) {
        return redirect('/login');
    }

    return redirect(\App\Filament\Pages\Dashboard::getUrl());
});
