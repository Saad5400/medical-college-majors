<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RegistrationRequestResource\Pages;
use App\Filament\Resources\RegistrationRequestResource\RelationManagers;
use App\Models\Major;
use App\Models\RegistrationRequest;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class RegistrationRequestResource extends Resource
{
    protected static ?string $model = RegistrationRequest::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Group::make([
                    Forms\Components\TextInput::make('user_gpa')
                        ->required()
                        ->numeric(),
                    Forms\Components\Repeater::make('majors')
                        ->columnSpan(2)
                        ->relationship('majors')
                        ->reorderable('sort')
                        ->live()
                        ->schema([
                            Forms\Components\Select::make('major_id')
                                ->options(function (Forms\Get $get) {
                                    return Major::query()
                                        ->get()
                                        ->mapWithKeys(fn($major) => [$major->id => $major->name]);
                                })
                                ->searchable()
                                ->required(),
                        ]),
                ])
                    ->columnSpanFull()
                    ->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('user.name')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('user_gpa')
                    ->numeric()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
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
            'index' => Pages\ListRegistrationRequests::route('/'),
            'create' => Pages\CreateRegistrationRequest::route('/create'),
            'edit' => Pages\EditRegistrationRequest::route('/{record}/edit'),
        ];
    }
}
