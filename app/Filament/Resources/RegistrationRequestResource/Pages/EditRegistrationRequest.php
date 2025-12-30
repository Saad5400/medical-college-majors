<?php

namespace App\Filament\Resources\RegistrationRequestResource\Pages;

use App\Filament\Resources\RegistrationRequestResource;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditRegistrationRequest extends EditRecord
{
    protected static string $resource = RegistrationRequestResource::class;

    public function mount(int | string $record): void
    {
        parent::mount($record);

        $user = auth()->user();

        // Allow admins to bypass email verification check
        if ($user->hasRole('admin')) {
            return;
        }

        // Check if user's email is verified
        if (!$user->hasVerifiedEmail()) {
            Notification::make()
                ->title('يجب تأكيد البريد الإلكتروني')
                ->body('يجب عليك تأكيد بريدك الإلكتروني قبل تعديل طلب التسجيل.')
                ->warning()
                ->send();

            $this->redirect('/');
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getFormActions(): array
    {
        return [
            $this->getSaveFormAction(),
        ];
    }
}
