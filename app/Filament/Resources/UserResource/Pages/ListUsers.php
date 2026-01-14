<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Imports\UserImporter;
use App\Filament\Resources\UserResource;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Actions\ImportAction;
use Filament\Resources\Pages\ListRecords;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
            Action::make('clear')
                ->label('مسح الطلاب')
                ->requiresConfirmation()
                ->action(function () {
                    // clear all users whom don't have the admin role
                    \App\Models\User::whereDoesntHave('roles', function ($query) {
                        $query->where('name', 'admin');
                    })->delete();
                    \Filament\Notifications\Notification::make()
                        ->title('تم مسح جميع الطلاب بنجاح.')
                        ->success()
                        ->send();
                })
                ->icon('heroicon-o-trash')
                ->color('danger'),
            ImportAction::make()
                ->label('استيراد الطلاب')
                ->importer(UserImporter::class)
                ->modalHeading('استيراد الطلاب')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('success'),
        ];
    }
}
