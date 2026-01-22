<?php

namespace App\Enums;

enum FacilityType: string
{
    case Hospital = 'hospital';
    case HealthcareCenter = 'healthcare_center';

    public function label(): string
    {
        return match ($this) {
            self::Hospital => 'Hospital',
            self::HealthcareCenter => 'Healthcare center',
        };
    }
}
