<?php

namespace App\Http\Responses;

use App\Filament\Resources\RegistrationRequestResource;
use Illuminate\Http\RedirectResponse;
use Livewire\Features\SupportRedirects\Redirector;

class RegistrationResponse implements \Filament\Auth\Http\Responses\Contracts\RegistrationResponse
{
    public function toResponse($request): RedirectResponse|Redirector
    {
        return redirect(RegistrationRequestResource::getUrl('create'));
    }
}
