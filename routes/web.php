<?php

use Illuminate\Support\Facades\Route;

Route::get('/', fn() => redirect()->route('request.create'));

Route::get('login', \App\Livewire\Login::class)->name('login');
Route::get('register', \App\Livewire\Register::class)->name('register');

Route::get('request', [\App\Http\Controllers\RequestController::class, 'index'])->name('request')->middleware('auth');
