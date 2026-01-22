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
}
