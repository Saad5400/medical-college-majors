<?php

namespace App\Filament\Resources\TrackResource\RelationManagers;

use App\Models\Specialization;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TrackSpecializationsRelationManager extends RelationManager
{
    protected static string $relationship = 'trackSpecializations';

    protected static ?string $title = 'التخصصات';

    protected static ?string $pluralModelLabel = 'التخصصات';

    protected static ?string $modelLabel = 'تخصص';

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
                TextColumn::make('specialization.duration_months')
                    ->label('المدة (أشهر)'),
                TextColumn::make('specialization.facility_type')
                    ->label('نوع المنشأة')
                    ->formatStateUsing(fn ($state): string => $state->label()),
            ])
            ->defaultSort('month_index')
            ->headerActions([
                CreateAction::make()
                    ->form([
                        Select::make('month_index')
                            ->searchable()
                            ->preload()
                            ->label('الشهر')
                            ->options(function () {
                                $options = [];
                                for ($i = 1; $i <= 12; $i++) {
                                    $options[$i] = "الشهر {$i}";
                                }

                                return $options;
                            })
                            ->required(),
                        Select::make('specialization_id')
                            ->label('التخصص')
                            ->options(Specialization::query()->pluck('name', 'id'))
                            ->searchable()
                            ->required(),
                    ]),
            ])
            ->recordActions([
                EditAction::make()
                    ->form([
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
                        Select::make('specialization_id')
                            ->label('التخصص')
                            ->options(Specialization::query()->pluck('name', 'id'))
                            ->searchable()
                            ->required(),
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
