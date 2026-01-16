<?php

namespace App\Providers;

use App\Listeners\SendPasswordResetTelegramNotification;
use Illuminate\Auth\Events\PasswordResetLinkSent;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        PasswordResetLinkSent::class => [
            SendPasswordResetTelegramNotification::class,
        ],
    ];
}
