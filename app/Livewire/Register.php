<?php

namespace App\Livewire;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Set;

class Register extends \Filament\Pages\Auth\Register
{
    protected function getForms(): array
    {
        return [
            'form' => $this->form(
                $this->makeForm()
                    ->schema([
                        $this->getNameFormComponent(),
                        $this->getGpaFormComponent(),
                        $this->getStudentIdFormComponent(),
                        $this->getEmailFormComponent(),
                        $this->getPasswordFormComponent(),
                        $this->getPasswordConfirmationFormComponent(),
                    ])
                    ->statePath('data'),
            ),
        ];
    }

    protected function getGpaFormComponent(): \Filament\Forms\Components\Component
    {
        return TextInput::make('gpa')
            ->label('المعدل التراكمي')
            ->placeholder('0.00')
            ->required()
            ->numeric()
            ->minValue(0)
            ->maxValue(4)
            ->live(onBlur: true);
    }

    protected function getStudentIdFormComponent(): \Filament\Forms\Components\Component
    {
        return TextInput::make('student_id')
            ->label('الرقم الجامعي')
            ->placeholder('0000000000')
            ->required()
            ->live(onBlur: true)
            ->afterStateUpdated(function (Set $set, $state) {
                $set('email', 's' . $state . '@uqu.edu.sa');
            })
            ->unique($this->getUserModel());
    }

    protected function getEmailFormComponent(): \Filament\Forms\Components\Component
    {
        return TextInput::make('email')
            ->label(__('filament-panels::pages/auth/register.form.email.label'))
            ->email()
            ->regex('/^s[0-9]+@uqu.edu.sa/')
            ->placeholder('s000000@uqu.edu.sa')
            ->required()
            ->maxLength(255)
            ->unique($this->getUserModel());
    }
}
