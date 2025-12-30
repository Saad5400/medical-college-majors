<?php

namespace App\Http\Responses;

use Illuminate\Http\RedirectResponse;
use Livewire\Features\SupportRedirects\Redirector;

class LoginResponse implements \Filament\Auth\Http\Responses\Contracts\LoginResponse
{
    public function toResponse($request): RedirectResponse|Redirector
    {
        return redirect()->intended();
    }
}
