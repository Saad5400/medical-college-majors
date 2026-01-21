<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class RegistrationSettings extends Settings
{
    public bool $track_registration_open;

    public bool $facility_registration_open;

    public static function group(): string
    {
        return 'registration';
    }
}
