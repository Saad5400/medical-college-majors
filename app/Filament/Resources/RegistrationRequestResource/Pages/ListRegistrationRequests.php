<?php

namespace App\Filament\Resources\RegistrationRequestResource\Pages;

use App\Filament\Resources\RegistrationRequestResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListRegistrationRequests extends ListRecords
{
    protected static string $resource = RegistrationRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            Actions\Action::make('delete_all_requests')
                ->label('حذف جميع طلبات التسجيل')
                ->icon('heroicon-o-trash')
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading('حذف جميع طلبات التسجيل')
                ->modalDescription('هل أنت متأكد من رغبتك في حذف جميع طلبات التسجيل؟ هذا الإجراء لا يمكن التراجع عنه.')
                ->modalSubmitActionLabel('نعم، احذف جميع الطلبات')
                ->action(function () {
                    $deletedCount = \App\Models\RegistrationRequest::count();
                    \App\Models\RegistrationRequest::truncate();

                    \Filament\Notifications\Notification::make()
                        ->title('تم حذف طلبات التسجيل')
                        ->body("تم حذف {$deletedCount} طلب.")
                        ->success()
                        ->send();
                })
                ->visible(fn () => auth()->user()->hasRole('admin')),
        ];
    }
}
