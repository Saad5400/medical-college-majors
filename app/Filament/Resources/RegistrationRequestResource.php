<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RegistrationRequestResource\Pages\CreateRegistrationRequest;
use App\Filament\Resources\RegistrationRequestResource\Pages\EditRegistrationRequest;
use App\Filament\Resources\RegistrationRequestResource\Pages\ListRegistrationRequests;
use App\Models\RegistrationRequest;
use App\Models\Track;
use App\Settings\RegistrationSettings;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class RegistrationRequestResource extends Resource
{
    protected static ?string $model = RegistrationRequest::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-document-arrow-up';

    protected static ?string $modelLabel = 'Registration request';

    protected static ?string $pluralModelLabel = 'Registration requests';

    public static function canCreate(): bool
    {
        $user = auth()->user();

        // Admins can always create
        if ($user->hasRole('admin')) {
            return true;
        }

        if (! $user->hasRole('student')) {
            return false;
        }

        // Leaders cannot create registration requests (their track is manually assigned)
        if ($user->hasRole('leader')) {
            return false;
        }

        // Check if track registration is open
        $settings = app(RegistrationSettings::class);
        if (! $settings->track_registration_open) {
            return false;
        }

        return true;
    }

    public static function canEdit($record): bool
    {
        $user = auth()->user();

        // Admins can always edit
        if ($user->hasRole('admin')) {
            return true;
        }

        if (! $user->hasRole('student')) {
            return false;
        }

        // Leaders cannot edit registration requests
        if ($user->hasRole('leader')) {
            return false;
        }

        // Check if track registration is open
        $settings = app(RegistrationSettings::class);
        if (! $settings->track_registration_open) {
            return false;
        }

        // Regular students can only edit their own requests
        return $record->user_id === $user->id;
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        if (auth()->user()->hasRole('admin')) {
            return $query;
        }

        return $query->where('user_id', auth()->id());
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Fieldset::make('Student details')
                    ->schema([
                        TextEntry::make('user_name')
                            ->label('Name')
                            ->state(auth()->user()->name)
                            ->disabled(),
                        TextEntry::make('user_email')
                            ->label('Email')
                            ->state(auth()->user()->email)
                            ->disabled(),
                        TextEntry::make('user_student_id')
                            ->label('Student ID')
                            ->state(auth()->user()->student_id)
                            ->disabled(),
                        TextEntry::make('user_gpa')
                            ->label('GPA')
                            ->state(auth()->user()->gpa)
                            ->disabled(),
                    ])
                    ->visible(fn () => ! auth()->user()->hasRole('admin'))
                    ->columnSpanFull(),
                Select::make('user_id')
                    ->label('Student')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload()
                    ->visible(fn () => auth()->user()->hasRole('admin'))
                    ->required(),
                ...static::getFormFields(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['user', 'trackRegistrationRequests.track']))
            ->columns([
                TextColumn::make('created_at')
                    ->label('Created at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('updated_at')
                    ->label('Updated at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('user.name')
                    ->label('Student')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('user.gpa')
                    ->label('GPA')
                    ->sortable(),
                TextColumn::make('trackRegistrationRequests')
                    ->getStateUsing(fn ($record) => $record->trackRegistrationRequests->pluck('track.name'))
                    ->label('Track preferences'),
            ])
            ->defaultSort('user.gpa', 'desc')
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultPaginationPageOption(25)
            ->paginationPageOptions([25, 50, 100]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListRegistrationRequests::route('/'),
            'create' => CreateRegistrationRequest::route('/create'),
            'edit' => EditRegistrationRequest::route('/{record}/edit'),
        ];
    }

    public static function getFormFields(): array
    {
        // Get available tracks count (excluding leader-only for non-admin/non-leader users)
        $trackQuery = Track::query();
        $user = auth()->user();

        // Non-admin and non-leader users should not see leader-only tracks
        if (! $user->hasRole('admin') && ! $user->hasRole('leader')) {
            $trackQuery->where('is_leader_only', false);
        }

        $trackCount = $trackQuery->count();

        return [
            Repeater::make('trackRegistrationRequests')
                ->columnSpanFull()
                ->label('Track preferences')
                ->relationship('trackRegistrationRequests')
                ->live()
                ->deletable(false)
                ->addable(false)
                ->minItems(fn () => $trackCount)
                ->maxItems(fn () => $trackCount)
                ->defaultItems(fn () => $trackCount)
                ->schema([
                    Hidden::make('sort')
                        ->label('Sort order')
                        ->default(function (Get $get, $component) {
                            $requests = $get('data.trackRegistrationRequests', true);
                            $path = explode('.', $component->getStatePath())[2];

                            return array_search($path, array_keys($requests));
                        })
                        ->required(),
                    Select::make('track_id')
                        ->label(function (Get $get, $component): ?string {
                            return 'Preference '.($component->getParentRepeaterItemIndex() + 1);
                        })
                        ->live()
                        ->relationship('track', 'name')
                        ->options(function (Get $get) {
                            // Retrieve current requests to exclude already selected tracks
                            $requests = $get('data.trackRegistrationRequests', true);
                            $requests = array_values($requests);
                            $requests = array_filter($requests, fn ($request) => ! empty($request['track_id']));

                            $selectedIds = array_map(fn ($request) => $request['track_id'], $requests);
                            $selectedIds = array_filter($selectedIds, fn ($id) => $id !== null);

                            $query = Track::query()->whereNotIn('id', $selectedIds);

                            // Filter out leader-only tracks for non-admin/non-leader users
                            $user = auth()->user();
                            if (! $user->hasRole('admin') && ! $user->hasRole('leader')) {
                                $query->where('is_leader_only', false);
                            }

                            return $query->orderBy('sort')
                                ->orderBy('id')
                                ->get()
                                ->mapWithKeys(fn ($track) => [$track->id => $track->name]);
                        })
                        ->searchable()
                        ->preload()
                        ->required(),
                ]),
        ];
    }
}
