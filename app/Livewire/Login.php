<?php

namespace App\Livewire;

use App\Http\Responses\LoginResponse;
use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Component;

class Login extends \Filament\Auth\Pages\Login
{
    public function mount(): void
    {
        if (Filament::auth()->check()) {
            redirect()->intended();
        }

        $this->form->fill();
    }

    public function authenticate(): ?\Filament\Auth\Http\Responses\Contracts\LoginResponse
    {
        try {
            $this->rateLimit(5);
        } catch (TooManyRequestsException $exception) {
            $this->getRateLimitedNotification($exception)?->send();

            return null;
        }

        $data = $this->form->getState();

        if (! Filament::auth()->attempt($this->getCredentialsFromFormData($data), $data['remember'] ?? false)) {
            $this->throwFailureValidationException();
        }

        $user = Filament::auth()->user();

        session()->regenerate();

        return new LoginResponse;
    }

    protected function getEmailFormComponent(): Component
    {
        return TextInput::make('email')
            ->label(__('filament-panels::pages/auth/register.form.email.label'))
            ->email()
            ->regex('/^s[0-9]+@uqu.edu.sa/')
            ->placeholder('s000000@uqu.edu.sa')
            ->required()
            ->maxLength(255);
    }

    protected function getRememberFormComponent(): Component
    {
        return Checkbox::make('remember')
            ->label(__('filament-panels::pages/auth/login.form.remember.label'))
            ->default(true);
    }

    public function registerAction(): Action
    {
        return Action::make('register')
            ->link()
            ->label(__('filament-panels::pages/auth/login.actions.register.label'))
            ->url(route('register'));
    }

    protected function getFormActions(): array
    {
        return [
            $this->getAuthenticateFormAction(),
            $this->registerAction(),
        ];
    }
}
