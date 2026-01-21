<?php

namespace App\Filament\Resources;

use App\Enums\FacilityType;
use App\Filament\Resources\SpecializationResource\Pages\CreateSpecialization;
use App\Filament\Resources\SpecializationResource\Pages\EditSpecialization;
use App\Filament\Resources\SpecializationResource\Pages\ListSpecializations;
use App\Models\Specialization;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\ColorColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SpecializationResource extends Resource
{
    protected static ?string $model = Specialization::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-beaker';

    protected static ?string $modelLabel = 'تخصص';

    protected static ?string $pluralModelLabel = 'التخصصات';

    protected static string|\UnitEnum|null $navigationGroup = 'الإعدادات';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('اسم التخصص')
                    ->required()
                    ->maxLength(255),
                ColorPicker::make('color')
                    ->label('لون التخصص')
                    ->required()
                    ->default('#94a3b8')
                    ->regex('/^#([a-fA-F0-9]{6})$/'),
                TextInput::make('duration_months')
                    ->label('مدة التخصص (بالأشهر)')
                    ->required()
                    ->integer()
                    ->minValue(1)
                    ->maxValue(12)
                    ->default(1),
                Select::make('facility_type')
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
                    ->label('اسم التخصص')
                    ->searchable(),
                ColorColumn::make('color')
                    ->label('اللون'),
                TextColumn::make('duration_months')
                    ->label('المدة (أشهر)')
                    ->sortable(),
                TextColumn::make('facility_type')
                    ->label('نوع المنشأة')
                    ->formatStateUsing(fn (FacilityType $state): string => $state->label())
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
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
            'index' => ListSpecializations::route('/'),
            'create' => CreateSpecialization::route('/create'),
            'edit' => EditSpecialization::route('/{record}/edit'),
        ];
    }
}
