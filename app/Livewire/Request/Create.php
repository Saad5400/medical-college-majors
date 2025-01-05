<?php

namespace App\Livewire\Request;

use App\Filament\Resources\RegistrationRequestResource;
use App\Models\Major;
use App\Models\RegistrationRequest;
use App\Models\User;
use Filament\Forms;
use Livewire\Component;

class Create extends Component implements Forms\Contracts\HasForms
{
    use Forms\Concerns\InteractsWithForms;

    public ?RegistrationRequest $record;
    public array $data = [];

    public function mount(): void
    {
        $this->record = User::query()->find(auth()->id())?->registrationRequests()->first();
        $this->record->load('majorRegistrationRequests');
        $this->form->fill($this->record?->toArray());
    }

    public function render(): \Illuminate\Contracts\View\View
    {
        return view('livewire.request.create');
    }

    public function form(Forms\Form $form): Forms\Form
    {
        return $form
            ->model(RegistrationRequest::class)
            ->statePath('data')
            ->schema(RegistrationRequestResource::getFormFields());
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
