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

    protected static ?string $modelLabel = 'طلب تسجيل';
    protected static ?string $pluralModelLabel = 'طلبات التسجيل';

    public static function form(Form $form): Form
    {
        return $form
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
