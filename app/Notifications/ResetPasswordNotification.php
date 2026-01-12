<?php

namespace App\Notifications;

use Filament\Auth\Notifications\ResetPassword as FilamentResetPassword;
use Illuminate\Notifications\Messages\MailMessage;

class ResetPasswordNotification extends FilamentResetPassword
{
    /**
     * Build the mail representation of the notification.
     */
    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('اعادة تعيين البيانات')
            ->html(sprintf('<a href="%1$s">%1$s</a>', e($this->url)));
    }
}
