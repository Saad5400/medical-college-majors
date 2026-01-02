<?php

namespace App\Filament\Resources\MajorResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class UsersRelationManager extends RelationManager
{
    protected static string $relationship = 'users';

    protected static ?string $title = 'الطلاب';

    protected static ?string $pluralModelLabel = 'الطلاب';

    protected static ?string $modelLabel = 'طالب';

    protected function canCreate(): bool
    {
        return false;
    }

    protected function canEdit(Model $record): bool
    {
        return false;
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('name')
                    ->label('الطالب'),
                TextColumn::make('email')
                    ->label('البريد الإلكتروني'),
                TextColumn::make('gpa')
                    ->label('المعدل'),
            ])
            ->filters([
                //
            ]);
    }
}
