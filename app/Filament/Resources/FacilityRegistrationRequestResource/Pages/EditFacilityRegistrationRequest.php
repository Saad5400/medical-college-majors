<?php

namespace App\Filament\Resources\FacilityRegistrationRequestResource\Pages;

use App\Filament\Resources\FacilityRegistrationRequestResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditFacilityRegistrationRequest extends EditRecord
{
    protected static string $resource = FacilityRegistrationRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function afterSave(): void
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
            } else {
                // Reset to competitive if not custom and not after custom
                $wish->update(['is_competitive' => true]);
            }
        }
    }
}
