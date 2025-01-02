<?php

namespace App\Filament\Resources\RegistrationRequestResource\Pages;

use App\Filament\Resources\RegistrationRequestResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateRegistrationRequest extends CreateRecord
{
    protected static string $resource = RegistrationRequestResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data = parent::mutateFormDataBeforeCreate($data);

        $data['user_id'] = auth()->id();

        return $data;
    }
}
