<?php

use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Support\Facades\Route;

// Email verification routes for Filament
Route::middleware(['web', 'auth'])->group(function () {
    Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
        $request->fulfill();

        return redirect('/')->with('success', 'تم تأكيد بريدك الإلكتروني بنجاح');
    })->middleware(['signed'])->name('filament.admin.auth.email-verification.verify');
});

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
