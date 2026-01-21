<?php

namespace App\Filament\Pages;

use App\Settings\RegistrationSettings;
use Filament\Forms\Components\Toggle;
use Filament\Pages\SettingsPage;

class ManageRegistrationSettings extends SettingsPage
{
    protected static string|null|\BackedEnum $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static string $settings = RegistrationSettings::class;

    protected static ?string $title = 'إعدادات التسجيل';

    protected static ?string $navigationLabel = 'إعدادات التسجيل';

    protected static string|\UnitEnum|null $navigationGroup = 'الإعدادات';

    public static function canAccess(): bool
    {
        return auth()->user()->hasRole('admin');
    }

    public function form(\Filament\Schemas\Schema $schema): \Filament\Schemas\Schema
    {
        return $schema
            ->schema([
                Toggle::make('track_registration_open')
                    ->label('تسجيل المسارات مفتوح')
                    ->helperText('السماح للطلاب بتقديم طلبات التسجيل في المسارات'),
                Toggle::make('facility_registration_open')
                    ->label('تسجيل المنشآت مفتوح')
                    ->helperText('السماح للطلاب بتقديم طلبات التسجيل في المنشآت'),
            ]);
    }
}
