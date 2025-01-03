<?php

use Illuminate\Support\Facades\Route;

Route::get('/', fn() => redirect()->route('request.create'));

Route::get('login', \App\Livewire\Login::class)->name('login');
Route::get('register', \App\Livewire\Register::class)->name('register');

Route::get('request/create', \App\Livewire\Request\Create::class)->name('request.create');
Route::get('request/edit', \App\Livewire\Request\Edit::class)->name('request.edit');
