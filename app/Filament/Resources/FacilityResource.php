<?php

namespace App\Filament\Resources;

use App\Enums\FacilityType;
use App\Filament\Resources\FacilityResource\Pages\CreateFacility;
use App\Filament\Resources\FacilityResource\Pages\EditFacility;
use App\Filament\Resources\FacilityResource\Pages\ListFacilities;
use App\Filament\Resources\FacilityResource\RelationManagers\FacilitySeatsRelationManager;
use App\Models\Facility;
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

    protected static ?string $modelLabel = 'منشأة';

    protected static ?string $pluralModelLabel = 'المنشآت';

    protected static string|\UnitEnum|null $navigationGroup = 'الإعدادات';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('اسم المنشأة')
                    ->required()
                    ->maxLength(255),
                Select::make('type')
                    ->label('نوع المنشأة')
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
                    ->label('تاريخ الإنشاء')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label('تاريخ التعديل')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('name')
                    ->label('اسم المنشأة')
                    ->searchable(),
                TextColumn::make('type')
                    ->label('نوع المنشأة')
                    ->formatStateUsing(fn (FacilityType $state): string => $state->label())
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('type')
                    ->label('نوع المنشأة')
                    ->options([
                        FacilityType::Hospital->value => FacilityType::Hospital->label(),
                        FacilityType::HealthcareCenter->value => FacilityType::HealthcareCenter->label(),
                    ]),
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
