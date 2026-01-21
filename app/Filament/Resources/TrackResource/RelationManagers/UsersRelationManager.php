<?php

namespace App\Filament\Resources\TrackResource\RelationManagers;

use App\Models\User;
use Filament\Actions\AttachAction;
use Filament\Actions\DetachAction;
use Filament\Actions\DetachBulkAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class UsersRelationManager extends RelationManager
{
    protected static string $relationship = 'users';

    protected static ?string $title = 'الطلاب';

    protected static ?string $pluralModelLabel = 'الطلاب';

    protected static ?string $modelLabel = 'طالب';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('name')
                    ->label('الطالب')
                    ->searchable(),
                TextColumn::make('email')
                    ->label('البريد الإلكتروني')
                    ->searchable(),
                TextColumn::make('student_id')
                    ->label('الرقم الجامعي')
                    ->searchable(),
                TextColumn::make('gpa')
                    ->label('المعدل')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                AttachAction::make()
                    ->preloadRecordSelect()
                    ->recordSelect(
                        fn () => \Filament\Forms\Components\Select::make('recordId')
                            ->label('الطالب')
                            ->options(
                                User::query()
                                    ->whereNull('track_id')
                                    ->pluck('name', 'id')
                            )
                            ->searchable()
                            ->required()
                    ),
            ])
            ->recordActions([
                DetachAction::make(),
            ])
            ->toolbarActions([
                DetachBulkAction::make(),
            ])
            ->defaultPaginationPageOption(25)
            ->paginationPageOptions([25, 50, 100]);
    }
}
