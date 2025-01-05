<?php

namespace App\Livewire\Request;

use App\Models\Major;
use App\Models\RegistrationRequest;
use Filament\Forms;
use Livewire\Component;

class Create extends Component implements Forms\Contracts\HasForms
{
    use Forms\Concerns\InteractsWithForms;

    public ?array $data = [];

    public function mount(RegistrationRequest $record): void
    {
        $this->form->fill($record->toArray());
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
            ->schema([
                Forms\Components\Repeater::make('majorRegistrationRequests')
                    ->label('رغبات التسكين')
                    ->relationship('majorRegistrationRequests')
                    ->live()
                    ->deletable(false)
                    ->minItems(fn() => Major::query()->count())
                    ->defaultItems(fn() => Major::query()->count())
                    ->schema([
                        Forms\Components\Hidden::make('sort')
                            ->label('ترتيب')
                            ->default(function (Forms\Get $get, $component) {
                                $requests = $get('data.majorRegistrationRequests', true);
                                $path = explode('.', $component->getStatePath())[2];
                                return array_search($path, array_keys($requests));
                            })
                            ->required(),
                        Forms\Components\Select::make('major_id')
                            ->label('')
                            ->relationship('major', 'name')
                            ->options(function (Forms\Get $get) {
                                // Retrieve current requests to exclude already selected majors
                                $requests = $get('data.majorRegistrationRequests', true);
                                $requests = array_values($requests);

                                $selectedIds = array_map(fn($request) => $request['major_id'], $requests);
                                $selectedIds = array_filter($selectedIds, fn($id) => $id !== null);

                                return Major::query()
                                    ->whereNotIn('id', $selectedIds)
                                    ->get()
                                    ->mapWithKeys(fn($major) => [$major->id => $major->name]);
                            })
                            ->searchable()
                            ->required(),
                    ])
            ]);
    }

    public function create(): void
    {
        $record = RegistrationRequest::create($this->form->getState());

        $this->form->model($record)->saveRelationships();
    }
}
