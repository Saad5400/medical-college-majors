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
use Illuminate\Support\Facades\DB;

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
            ->modifyQueryUsing(function (Builder $query) {
                // Eager load users_count using withCount
                $query->withCount('users');

                // Calculate first_choice_users_count efficiently with a subquery
                // Find records where sort equals the minimum sort for that registration_request
                // Use raw SQL to ensure proper type casting for PostgreSQL
                $firstChoiceSubquery = DB::table('major_registration_request')
                    ->selectRaw('major_id::bigint as major_id, COUNT(*)::integer as count')
                    ->whereRaw('sort = (SELECT MIN(mrr2.sort) FROM major_registration_request mrr2 WHERE mrr2.registration_request_id = major_registration_request.registration_request_id)')
                    ->groupBy('major_id');

                $query->leftJoinSub($firstChoiceSubquery, 'first_choices', function ($join) {
                    $join->on('majors.id', '=', 'first_choices.major_id');
                })
                    ->addSelect('majors.*')
                    ->addSelect('first_choices.count as first_choice_users_count');
            })
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
                    ->default(0)
                    ->sortable(),
                TextColumn::make('users_count')
                    ->label('عدد الطلاب')
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
}
