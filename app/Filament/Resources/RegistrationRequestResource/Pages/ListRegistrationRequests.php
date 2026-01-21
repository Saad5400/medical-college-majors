<?php

namespace App\Filament\Resources\RegistrationRequestResource\Pages;

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
//            Action::make('clear')
//                ->visible(auth()->user()->hasRole('admin'))
//                ->label('مسح طلبات التسجيل')
//                ->requiresConfirmation()
//                ->action(function () {
//                    if (! auth()->user()->hasRole('admin')) {
//                        return;
//                    }
//
//                    // clear all registration requests
//                    \App\Models\RegistrationRequest::query()->delete();
//                    \Filament\Notifications\Notification::make()
//                        ->title('تم مسح جميع طلبات التسجيل بنجاح.')
//                        ->success()
//                        ->send();
//                })
//                ->icon('heroicon-o-trash')
//                ->color('danger'),
        ];
    }
}
