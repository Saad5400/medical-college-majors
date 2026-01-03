<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ResetPasswordNotification extends Notification
{
    use Queueable;

    public function __construct(
        public string $token
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $url = url(route('filament.admin.auth.password-reset.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ], false));

        return (new MailMessage)
            ->subject('إعادة تعيين كلمة المرور - نظام مسارات كلية الطب')
            ->greeting('مرحباً!')
            ->line('لقد تلقيت هذا البريد الإلكتروني لأننا استلمنا طلب إعادة تعيين كلمة المرور لحسابك.')
            ->action('إعادة تعيين كلمة المرور', $url)
            ->line('ستنتهي صلاحية رابط إعادة تعيين كلمة المرور خلال 60 دقيقة.')
            ->line('إذا لم تقم بطلب إعادة تعيين كلمة المرور، فلا يلزم اتخاذ أي إجراء.')
            ->salutation('مع تحيات نظام مسارات كلية الطب');
    }

    public function toArray(object $notifiable): array
    {
        return [];
    }
}
