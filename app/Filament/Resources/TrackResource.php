<?php

namespace App\Filament\Resources;

use App\Enums\Month;
use App\Filament\Resources\TrackResource\Pages\CreateTrack;
use App\Filament\Resources\TrackResource\Pages\EditTrack;
use App\Filament\Resources\TrackResource\Pages\ListTracks;
use App\Filament\Resources\TrackResource\Pages\TrackSchedule;
use App\Filament\Resources\TrackResource\RelationManagers\TrackSpecializationsRelationManager;
use App\Filament\Resources\TrackResource\RelationManagers\UsersRelationManager;
use App\Models\Track;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class TrackResource extends Resource
{
    protected static ?string $model = Track::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-academic-cap';

    protected static ?string $modelLabel = 'مسار';

    protected static ?string $pluralModelLabel = 'المسارات';

    protected static string|\UnitEnum|null $navigationGroup = 'الإعدادات';

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
                Toggle::make('is_leader_only')
                    ->label('مسار للمشرفين فقط')
                    ->helperText('إذا تم تفعيل هذا الخيار، لن يظهر هذا المسار للطلاب العاديين')
                    ->default(false),
                CheckboxList::make('elective_months')
                    ->label('الأشهر الاختيارية')
                    ->helperText('حدد الأشهر التي يمكن للطالب فيها اختيار تخصص/مستشفى مختلف')
                    ->options(Month::options())
                    ->rule(function (?Track $record): \Closure {
                        return function (string $attribute, mixed $value, \Closure $fail) use ($record): void {
                            if (! $record || ! is_array($value)) {
                                return;
                            }

                            $conflicts = $record->getConflictingElectiveMonths($value);

                            if ($conflicts === []) {
                                return;
                            }

                            $labels = implode('، ', array_map(
                                fn (int $month): string => Month::labelFor($month),
                                $conflicts,
                            ));

                            $fail("لا يمكن اختيار أشهر اختيارية تتداخل مع تخصصات موجودة: {$labels}.");
                        };
                    })
                    ->columns(4),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query) {
                // Count users per track
                $usersCountSubquery = DB::table('users')
                    ->select('track_id', DB::raw('COUNT(*) as count'))
                    ->whereNotNull('track_id')
                    ->groupBy('track_id');

                $query->leftJoinSub($usersCountSubquery, 'users_counts', function ($join) {
                    $join->on('tracks.id', '=', 'users_counts.track_id');
                });

                // Pre-compute first choice counts using a single aggregated subquery
                // A "first choice" is where sort equals the minimum sort for that registration_request
                $firstChoiceSubquery = DB::table('track_registration_requests as trr1')
                    ->select('trr1.track_id', DB::raw('COUNT(*) as count'))
                    ->whereRaw('trr1.sort = (
                        SELECT MIN(trr2.sort)
                        FROM track_registration_requests trr2
                        WHERE trr2.registration_request_id = trr1.registration_request_id
                    )')
                    ->groupBy('trr1.track_id');

                $query->leftJoinSub($firstChoiceSubquery, 'first_choices', function ($join) {
                    $join->on('tracks.id', '=', 'first_choices.track_id');
                })
                    ->addSelect('tracks.*')
                    ->addSelect(DB::raw('COALESCE(users_counts.count, 0) as users_count'))
                    ->addSelect(DB::raw('COALESCE(first_choices.count, 0) as first_choice_users_count'));
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
                IconColumn::make('is_leader_only')
                    ->label('للمشرفين فقط')
                    ->boolean()
                    ->sortable(),
                TextColumn::make('first_choice_users_count')
                    ->label('عدد الطلاب الذين إختاروه كأول رغبة')
                    ->default(0)
                    ->sortable(),
                TextColumn::make('users_count')
                    ->label('عدد الطلاب')
                    ->sortable(),
            ])
            ->defaultSort('sort')
            ->reorderable('sort')
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
            TrackSpecializationsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTracks::route('/'),
            'create' => CreateTrack::route('/create'),
            'schedule' => TrackSchedule::route('/schedule'),
            'edit' => EditTrack::route('/{record}/edit'),
        ];
    }
}
