<?php

namespace App\Filament\Pages;

use App\Settings\RegistrationSettings;
use Filament\Forms\Components\Toggle;
use Filament\Pages\SettingsPage;

class ManageRegistrationSettings extends SettingsPage
{
    protected static string|null|\BackedEnum $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static string $settings = RegistrationSettings::class;

    protected static ?string $title = 'Registration Settings';

    protected static ?string $navigationLabel = 'Registration Settings';

    protected static string|\UnitEnum|null $navigationGroup = 'Settings';

    public static function canAccess(): bool
    {
        return auth()->user()->hasRole('admin');
    }

    public function form(\Filament\Schemas\Schema $schema): \Filament\Schemas\Schema
    {
        return $schema
            ->schema([
                Toggle::make('track_registration_open')
                    ->label('Track registration open')
                    ->helperText('Allow students to submit track registration requests'),
                Toggle::make('facility_registration_open')
                    ->label('Facility registration open')
                    ->helperText('Allow students to submit facility registration requests'),
            ]);
    }
}
