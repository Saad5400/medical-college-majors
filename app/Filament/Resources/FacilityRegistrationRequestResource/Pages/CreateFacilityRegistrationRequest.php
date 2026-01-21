<?php

namespace App\Filament\Resources\FacilityRegistrationRequestResource\Pages;

use App\Filament\Resources\FacilityRegistrationRequestResource;
use Filament\Resources\Pages\CreateRecord;

class CreateFacilityRegistrationRequest extends CreateRecord
{
    protected static string $resource = FacilityRegistrationRequestResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // If not admin, set user_id to current user
        if (! auth()->user()->hasRole('admin')) {
            $data['user_id'] = auth()->id();
        }

        return $data;
    }

    protected function afterCreate(): void
    {
        // Process wishes and update is_competitive flag
        $this->updateWishCompetitiveness();
    }

    protected function updateWishCompetitiveness(): void
    {
        $wishes = $this->record->wishes()->orderBy('priority')->get();
        $foundCustom = false;

        foreach ($wishes as $wish) {
            if ($foundCustom) {
                // All wishes after a custom one are non-competitive
                $wish->update(['is_competitive' => false]);
            } elseif ($wish->is_custom) {
                $foundCustom = true;
                $wish->update(['is_competitive' => false]);
            }
        }
    }
}
