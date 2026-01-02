<?php

namespace App\Livewire\Request;

use App\Filament\Resources\RegistrationRequestResource;
use App\Models\RegistrationRequest;
use App\Models\User;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Schemas\Schema;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class Create extends Component implements HasActions, HasForms
{
    use InteractsWithActions;
    use InteractsWithForms;

    public ?RegistrationRequest $record;

    public array $data = [];

    public function mount(): void
    {
        $this->record = User::query()->find(auth()->id())?->registrationRequests()->first();
        $this->record->load('majorRegistrationRequests');
        $this->form->fill($this->record?->toArray());
    }

    public function render(): View
    {
        return view('livewire.request.create');
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->model(RegistrationRequest::class)
            ->statePath('data')
            ->components(RegistrationRequestResource::getFormFields());
    }

    public function upsertRequest(): void
    {
        $record = RegistrationRequest::updateOrCreate(
            ['user_id' => auth()->id()],
            $this->form->getState()
        );

        $this->form->model($record)->saveRelationships();
    }
}
