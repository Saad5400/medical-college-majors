<?php

namespace App\Filament\Resources\FacilityResource\RelationManagers;

use App\Models\Specialization;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class FacilitySeatsRelationManager extends RelationManager
{
    protected static string $relationship = 'facilitySeats';

    protected static ?string $title = 'المقاعد المتاحة';

    protected static ?string $pluralModelLabel = 'المقاعد';

    protected static ?string $modelLabel = 'مقعد';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('specialization.name')
            ->columns([
                TextColumn::make('month_index')
                    ->label('الشهر')
                    ->sortable()
                    ->formatStateUsing(fn (int $state): string => "الشهر {$state}"),
                TextColumn::make('specialization.name')
                    ->label('التخصص')
                    ->searchable(),
                TextColumn::make('max_seats')
                    ->label('الحد الأقصى للمقاعد')
                    ->sortable(),
            ])
            ->defaultSort('month_index')
            ->headerActions([
                CreateAction::make()
                    ->form([
                        Select::make('specialization_id')
                            ->label('التخصص')
                            ->options(Specialization::query()->pluck('name', 'id'))
                            ->searchable()
                            ->required(),
                        Select::make('month_index')
                            ->label('الشهر')
                            ->options(function () {
                                $options = [];
                                for ($i = 1; $i <= 12; $i++) {
                                    $options[$i] = "الشهر {$i}";
                                }

                                return $options;
                            })
                            ->required(),
                        TextInput::make('max_seats')
                            ->label('الحد الأقصى للمقاعد')
                            ->required()
                            ->integer()
                            ->minValue(0),
                    ]),
            ])
            ->recordActions([
                EditAction::make()
                    ->form([
                        Select::make('specialization_id')
                            ->label('التخصص')
                            ->options(Specialization::query()->pluck('name', 'id'))
                            ->searchable()
                            ->required(),
                        Select::make('month_index')
                            ->label('الشهر')
                            ->options(function () {
                                $options = [];
                                for ($i = 1; $i <= 12; $i++) {
                                    $options[$i] = "الشهر {$i}";
                                }

                                return $options;
                            })
                            ->required(),
                        TextInput::make('max_seats')
                            ->label('الحد الأقصى للمقاعد')
                            ->required()
                            ->integer()
                            ->minValue(0),
                    ]),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                DeleteBulkAction::make(),
            ])
            ->defaultPaginationPageOption(25)
            ->paginationPageOptions([25, 50, 100]);
    }
}
