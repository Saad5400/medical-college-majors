<?php

namespace App\Livewire;

use App\Models\User;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Livewire\Component;

class Profile extends Component implements HasActions, HasForms
{
    use InteractsWithActions;
    use InteractsWithForms;

    public ?User $record;

    public array $data = [];

    public function mount(): void
    {
        $this->record = auth()->user();
        $this->form->fill($this->record->toArray());
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->model($this->record)
            ->statePath('data')
            ->columns(['sm' => 2])
            ->components([
                TextInput::make('name')
                    ->label(__('filament-panels::pages/auth/register.form.name.label'))
                    ->required()
                    ->maxLength(255)
                    ->autofocus(),
                TextInput::make('gpa')
                    ->label('المعدل التراكمي')
                    ->placeholder('0.00')
                    ->required()
                    ->numeric()
                    ->minValue(0)
                    ->maxValue(4)
                    ->live(onBlur: true),
                TextInput::make('student_id')
                    ->label('الرقم الجامعي')
                    ->placeholder('0000000000')
                    ->required()
                    ->live(onBlur: true)
                    ->afterStateUpdated(function (Set $set, $state) {
                        $set('email', 's'.$state.'@uqu.edu.sa');
                    })
                    ->unique(User::class),
                TextInput::make('email')
                    ->label(__('filament-panels::pages/auth/register.form.email.label'))
                    ->email()
                    ->regex('/^s[0-9]+@uqu.edu.sa/')
                    ->placeholder('s000000@uqu.edu.sa')
                    ->required()
                    ->maxLength(255)
                    ->unique(User::class),
            ]);
    }

    public function update()
    {
        User::query()->find(auth()->id())->update($this->data);
    }

    public function logout()
    {
        auth()->logout();

        return redirect()->route('login');
    }

    public function render()
    {
        return view('livewire.profile');
    }
}
