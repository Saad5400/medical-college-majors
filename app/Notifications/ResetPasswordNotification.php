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
        $expireMinutes = config('auth.passwords.'.config('auth.defaults.passwords').'.expire');

        return (new MailMessage)
            ->subject('طلب تحديث بيانات الدخول')
            ->line('تلقينا طلباً لتحديث بيانات الدخول لحسابك.')
            ->action('تحديث بيانات الدخول', $this->url)
            ->line('هذا الرابط صالح لمدة '.$expireMinutes.' دقيقة.')
            ->line('إذا لم تقم بهذا الطلب، يرجى تجاهل هذه الرسالة.');
    }
}
