<?php

namespace App\Http\Responses;

use App\Filament\Resources\RegistrationRequestResource;
use Filament\Http\Responses\Auth\Contracts\RegistrationResponse as Responsable;
use Illuminate\Http\RedirectResponse;
use Livewire\Features\SupportRedirects\Redirector;

class RegistrationResponse implements Responsable
{
    public function toResponse($request): RedirectResponse|Redirector
    {
        return redirect(RegistrationRequestResource::getUrl('create'));
    }
}
