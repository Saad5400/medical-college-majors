<?php

namespace App\Filament\Resources\TrackResource\RelationManagers;

use App\Models\User;
use Filament\Actions\AssociateAction;
use Filament\Actions\DissociateAction;
use Filament\Actions\DissociateBulkAction;
use Filament\Forms\Components\Select;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

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
                AssociateAction::make()
                    ->preloadRecordSelect()
                    ->recordSelect(function (Select $select): Select {
                        return $select
                            ->label('الطالب')
                            ->hiddenLabel(false)
                            ->options(fn (Select $component): array => $this->getAssignableStudentOptions(
                                $component->getOptionsLimit(),
                            ))
                            ->getSearchResultsUsing(fn (Select $component, string $search): array => $this->getAssignableStudentOptions(
                                $component->getOptionsLimit(),
                                $search,
                            ));
                    }),
            ])
            ->recordActions([
                DissociateAction::make(),
            ])
            ->toolbarActions([
                DissociateBulkAction::make(),
            ])
            ->defaultPaginationPageOption(25)
            ->paginationPageOptions([25, 50, 100]);
    }

    /**
     * @return array<int, string>
     */
    private function getAssignableStudentOptions(int $optionsLimit, ?string $search = null): array
    {
        $query = User::query()
            ->whereNull('track_id')
            ->whereHas('roles', function (Builder $query): void {
                $query->where('name', 'student');
            });

        if (filled($search)) {
            $query->where(function (Builder $query) use ($search): void {
                $query
                    ->where('name', 'ilike', "%{$search}%")
                    ->orWhere('email', 'ilike', "%{$search}%")
                    ->orWhere('student_id', 'ilike', "%{$search}%");
            });
        }

        return $query
            ->orderBy('name')
            ->limit($optionsLimit)
            ->pluck('name', 'id')
            ->all();
    }
}
