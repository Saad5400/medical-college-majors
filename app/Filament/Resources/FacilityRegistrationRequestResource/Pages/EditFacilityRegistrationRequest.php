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

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Ensure there are exactly 5 wishes for the repeater
        $wishes = $data['wishes'] ?? [];
        $wishCount = 5;

        // Pad with empty wishes if needed
        while (count($wishes) < $wishCount) {
            $wishes[] = [
                'priority' => count($wishes) + 1,
                'facility_id' => null,
                'specialization_id' => null,
                'custom_facility_name' => null,
                'custom_specialization_name' => null,
                'is_custom' => false,
                'is_competitive' => true,
            ];
        }

        // Trim if there are too many
        $wishes = array_slice($wishes, 0, $wishCount);

        $data['wishes'] = $wishes;

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Filter out empty wishes (those without facility_id and not custom)
        if (isset($data['wishes'])) {
            $data['wishes'] = array_values(array_filter($data['wishes'], function ($wish) {
                // Keep wish if it has a facility_id OR is custom with a custom_facility_name
                return ! empty($wish['facility_id']) || ($wish['is_custom'] && ! empty($wish['custom_facility_name']));
            }));

            // Re-index priorities to be sequential
            foreach ($data['wishes'] as $index => &$wish) {
                $wish['priority'] = $index + 1;
            }
        }

        return $data;
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
