<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('registration.track_registration_open', true);
        $this->migrator->add('registration.facility_registration_open', false);
    }
};
