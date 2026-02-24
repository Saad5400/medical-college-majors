<?php

namespace App\Filament\Resources\RegistrationRequestResource\Pages;

use App\Filament\Pages\TrackSchedule;
use App\Filament\Resources\RegistrationRequestResource;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListRegistrationRequests extends ListRecords
{
    protected static string $resource = RegistrationRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
            Action::make('schedule')
                ->label('View track schedule')
                ->icon('heroicon-o-table-cells')
                ->url(fn (): string => TrackSchedule::getUrl())
                ->visible(fn () => auth()->user()->hasRole('student'))
                ->color('info'),
        ];
    }
}
