<?php

namespace App\Filament\Resources\RegistrationRequestResource\Pages;

use App\Filament\Resources\RegistrationRequestResource;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateRegistrationRequest extends CreateRecord
{
    protected static string $resource = RegistrationRequestResource::class;

    public function mount(): void
    {
        parent::mount();

        $user = auth()->user();

        // Allow admins to bypass email verification check
        if ($user->hasRole('admin')) {
            return;
        }

        // Check if user's email is verified
        if (!$user->hasVerifiedEmail()) {
            Notification::make()
                ->title('يجب تأكيد البريد الإلكتروني')
                ->body('يجب عليك تأكيد بريدك الإلكتروني قبل إنشاء طلب تسجيل جديد.')
                ->warning()
                ->send();

            $this->redirect('/');
        }
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data = parent::mutateFormDataBeforeCreate($data);

        if (!auth()->user()->hasRole('admin')) {
            $data['user_id'] = auth()->id();
        }

        return $data;
    }

    protected function getFormActions(): array
    {
        return [
            $this->getCreateFormAction(),
        ];
    }
}
