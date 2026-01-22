<?php

namespace App\Filament\Resources;

use App\Enums\FacilityType;
use App\Enums\Month;
use App\Filament\Resources\FacilityResource\Pages\CreateFacility;
use App\Filament\Resources\FacilityResource\Pages\EditFacility;
use App\Filament\Resources\FacilityResource\Pages\ListFacilities;
use App\Filament\Resources\FacilityResource\RelationManagers\FacilitySeatsRelationManager;
use App\Models\Facility;
use App\Models\Specialization;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class FacilityResource extends Resource
{
    protected static ?string $model = Facility::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-building-office-2';

    protected static ?string $modelLabel = 'Facility';

    protected static ?string $pluralModelLabel = 'Facilities';

    protected static string|\UnitEnum|null $navigationGroup = 'Settings';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Name')
                    ->required()
                    ->maxLength(255),
                Select::make('type')
                    ->label('Facility type')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->options([
                        FacilityType::Hospital->value => FacilityType::Hospital->label(),
                        FacilityType::HealthcareCenter->value => FacilityType::HealthcareCenter->label(),
                    ])
                    ->default(FacilityType::Hospital->value),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')
                    ->label('Created at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label('Updated at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('name')
                    ->label('Name')
                    ->searchable(),
                TextColumn::make('type')
                    ->label('Type')
                    ->formatStateUsing(fn (FacilityType $state): string => $state->label())
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('type')
                    ->label('Facility type')
                    ->options([
                        FacilityType::Hospital->value => FacilityType::Hospital->label(),
                        FacilityType::HealthcareCenter->value => FacilityType::HealthcareCenter->label(),
                    ]),
                SelectFilter::make('specialization_id')
                    ->label('Specialization')
                    ->searchable()
                    ->preload()
                    ->options(fn (): array => Specialization::query()->orderBy('name')->pluck('name', 'id')->all())
                    ->query(function ($query, array $data) {
                        if (! empty($data['value'])) {
                            $query->whereHas('facilitySeats', function ($q) use ($data) {
                                $q->where('specialization_id', $data['value']);
                            });
                        }
                    }),
                SelectFilter::make('month')
                    ->label('Month')
                    ->searchable()
                    ->options(Month::options())
                    ->query(function ($query, array $data) {
                        if (! empty($data['value'])) {
                            $query->whereHas('facilitySeats', function ($q) use ($data) {
                                $q->where('month_index', $data['value']);
                            });
                        }
                    }),
            ])
            ->deferFilters()
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
            FacilitySeatsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListFacilities::route('/'),
            'create' => CreateFacility::route('/create'),
            'edit' => EditFacility::route('/{record}/edit'),
        ];
    }
}
