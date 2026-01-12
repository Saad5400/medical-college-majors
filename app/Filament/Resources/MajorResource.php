<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MajorResource\Pages\CreateMajor;
use App\Filament\Resources\MajorResource\Pages\EditMajor;
use App\Filament\Resources\MajorResource\Pages\ListMajors;
use App\Filament\Resources\MajorResource\RelationManagers\UsersRelationManager;
use App\Models\Major;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use pxlrbt\FilamentExcel\Actions\Tables\ExportAction;
use pxlrbt\FilamentExcel\Columns\Column;
use pxlrbt\FilamentExcel\Exports\ExcelExport;

class MajorResource extends Resource
{
    protected static ?string $model = Major::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-academic-cap';

    protected static ?string $modelLabel = 'مسار';

    protected static ?string $pluralModelLabel = 'المسارات';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('المسار')
                    ->required()
                    ->maxLength(255),
                TextInput::make('max_users')
                    ->label('الحد الأقصى لعدد الطلاب')
                    ->required()
                    ->integer()
                    ->minValue(0),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with('users'))
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
                    ->label('المسار')
                    ->searchable(),
                TextColumn::make('max_users')
                    ->label('الحد الأقصى لعدد الطلاب')
                    ->sortable(),
                TextColumn::make('first_choice_users_count')
                    ->label('عدد الطلاب الذين إختاروه كأول رغبة')
                    ->getStateUsing(function (Major $record) {
                        $count = 0;
                        foreach ($record->registrationRequests as $registrationRequest) {
                            if ($registrationRequest->majors()->orderByPivot('sort')->first()->id === $record->id) {
                                $count++;
                            }
                        }

                        return $count;
                    })
                    ->sortable(),
                TextColumn::make('users_count')
                    ->label('عدد الطلاب')
                    ->getStateUsing(function (Major $record) {
                        return $record->users()->count();
                    })
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
                ExportAction::make()
                    ->label('تصدير المقبولين')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->visible(fn () => auth()->user()->hasRole('admin'))
                    ->exports([
                        ExcelExport::make()
                            ->withFilename('majors-with-students')
                            ->withColumns(static::getExportColumns()),
                    ]),
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
            UsersRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMajors::route('/'),
            'create' => CreateMajor::route('/create'),
            'edit' => EditMajor::route('/{record}/edit'),
        ];
    }

    public static function getExportColumns(): array
    {
        $columns = [
            Column::make('name')
                ->heading('المسار'),
            Column::make('max_users')
                ->heading('الحد الأقصى لعدد الطلاب'),
            Column::make('users_count')
                ->heading('عدد الطلاب المقبولين')
                ->getStateUsing(fn (Major $record): int => $record->users->count()),
            Column::make('accepted_students')
                ->heading('الطلاب المقبولون')
                ->getStateUsing(function (Major $record): string {
                    return $record->users
                        ->map(function ($user): string {
                            $details = [
                                $user->name,
                                'ID: '.$user->student_id,
                                'GPA: '.$user->gpa,
                                'Email: '.$user->email,
                                'Phone: '.$user->phone_number,
                            ];

                            return implode(' | ', $details);
                        })
                        ->implode("\n");
                }),
        ];

        return $columns;
    }
}
