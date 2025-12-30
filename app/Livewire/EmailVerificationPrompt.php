<?php

namespace App\Livewire;

use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Filament\Pages\SimplePage;
use Illuminate\Contracts\Support\Htmlable;

class EmailVerificationPrompt extends SimplePage
{
    protected static string $view = 'livewire.email-verification-prompt';

    public function getTitle(): string | Htmlable
    {
        return 'تأكيد البريد الإلكتروني';
    }

    public function getHeading(): string | Htmlable
    {
        return 'تأكيد البريد الإلكتروني';
    }

    public function resendEmailVerification()
    {
        $user = auth()->user();

        if ($user->hasVerifiedEmail()) {
            Notification::make()
                ->title('تم التأكيد مسبقاً')
                ->body('تم تأكيد بريدك الإلكتروني مسبقاً.')
                ->success()
                ->send();

            return redirect('/');
        }

        $user->sendEmailVerificationNotification();

        Notification::make()
            ->title('تم إرسال رسالة التأكيد')
            ->body('تم إرسال رسالة تأكيد جديدة إلى بريدك الإلكتروني.')
            ->success()
            ->send();
    }
}
