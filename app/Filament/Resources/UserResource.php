<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages\EditUser;
use App\Filament\Resources\UserResource\Pages\ListUsers;
use App\Models\User;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use STS\FilamentImpersonate\Actions\Impersonate;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $modelLabel = 'User';

    protected static ?string $pluralModelLabel = 'Users';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-user';

    protected static string|\UnitEnum|null $navigationGroup = 'Settings';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Name')
                    ->required()
                    ->maxLength(255),
                TextInput::make('email')
                    ->label('Email')
                    ->email()
                    ->required()
                    ->maxLength(255),
                TextInput::make('phone_number')
                    ->label('Phone number')
                    ->tel()
                    ->maxLength(255),
                TextInput::make('gpa')
                    ->label('GPA')
                    ->numeric()
                    ->default(null),
                TextInput::make('student_id')
                    ->label('Student ID')
                    ->maxLength(255)
                    ->default(null),
                Select::make('track_id')
                    ->label('Track')
                    ->relationship('track', 'name')
                    ->searchable()
                    ->preload()
                    ->nullable(),
                Select::make('roles')
                    ->label('Roles')
                    ->relationship('roles', 'name')
                    ->searchable()
                    ->preload()
                    ->multiple(),
                Group::make()
                    ->columnSpanFull()
                    ->schema([
                        Toggle::make('change_password')
                            ->label('Change password')
                            ->live()
                            ->default($schema->getOperation() === 'create')
                            ->hidden($schema->getOperation() === 'create'),
                        Group::make()
                            ->schema(function (Get $get) {
                                if ($get('change_password')) {
                                    return [
                                        TextInput::make('password')
                                            ->label('Password')
                                            ->password()
                                            ->required()
                                            ->minLength(8)
                                            ->maxLength(255),
                                    ];
                                }

                                return [];
                            }),
                    ]),
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
                TextColumn::make('email')
                    ->label('Email')
                    ->searchable(),
                TextColumn::make('phone_number')
                    ->label('Phone number')
                    ->searchable(),
                TextColumn::make('gpa')
                    ->label('GPA')
                    ->sortable(),
                TextColumn::make('student_id')
                    ->label('Student ID')
                    ->searchable(),
                TextColumn::make('track.name')
                    ->label('Track')
                    ->searchable()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('track')
                    ->label('Track')
                    ->relationship('track', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->recordActions([
                EditAction::make(),
                Impersonate::make(),
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
            'index' => ListUsers::route('/'),
            'edit' => EditUser::route('/{record}/edit'),
        ];
    }
}
