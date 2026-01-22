<?php

namespace App\Filament\Resources;

use App\Enums\Month;
use App\Filament\Resources\FacilityRegistrationRequestResource\Pages\CreateFacilityRegistrationRequest;
use App\Filament\Resources\FacilityRegistrationRequestResource\Pages\EditFacilityRegistrationRequest;
use App\Filament\Resources\FacilityRegistrationRequestResource\Pages\ListFacilityRegistrationRequests;
use App\Models\Facility;
use App\Models\FacilityRegistrationRequest;
use App\Models\FacilitySeat;
use App\Models\Specialization;
use App\Models\TrackSpecialization;
use App\Models\User;
use App\Settings\RegistrationSettings;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class FacilityRegistrationRequestResource extends Resource
{
    protected static ?string $model = FacilityRegistrationRequest::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-building-office';

    protected static ?string $modelLabel = 'طلب تسجيل منشأة';

    protected static ?string $pluralModelLabel = 'طلبات تسجيل المنشآت';

    private const WISH_COUNT = 5;

    public static function canCreate(): bool
    {
        $user = auth()->user();

        // Admins can always create
        if ($user->hasRole('admin')) {
            return true;
        }

        if (! $user->hasRole('student')) {
            return false;
        }

        // Check if facility registration is open
        $settings = app(RegistrationSettings::class);
        if (! $settings->facility_registration_open) {
            return false;
        }

        // User must have an assigned track
        if (! $user->track_id) {
            return false;
        }

        return true;
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        if (auth()->user()->hasRole('admin')) {
            return $query;
        }

        return $query->where('user_id', auth()->id());
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('user_id')
                    ->label('الطالب')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload()
                    ->live()
                    ->visible(fn () => auth()->user()->hasRole('admin'))
                    ->required(),
                Select::make('month_index')
                    ->searchable()
                    ->preload()
                    ->label('الشهر')
                    ->options(fn (Get $get, ?FacilityRegistrationRequest $record): array => static::getAvailableMonthOptions(
                        $get,
                        $record,
                    ))
                    ->disabled(fn (Get $get): bool => auth()->user()->hasRole('admin') && ! $get('user_id'))
                    ->required()
                    ->live(),
                Fieldset::make('معلومات الطالب والشهر المحدد')
                    ->schema([
                        TextEntry::make('month_track')
                            ->label('المسار')
                            ->state(fn (Get $get, ?FacilityRegistrationRequest $record): string => static::getMonthTrackInfo($get, $record)),
                        TextEntry::make('user_gpa')
                            ->label('المعدل')
                            ->state(function (Get $get, ?FacilityRegistrationRequest $record): string {
                                $user = static::resolveFormUser($get, $record);

                                return $user?->gpa ?? '-';
                            }),
                        TextEntry::make('month_specialization')
                            ->label('التخصص')
                            ->state(fn (Get $get, ?FacilityRegistrationRequest $record): string => static::getMonthSpecializationInfo($get, $record)),
                        TextEntry::make('month_duration')
                            ->label('المدة')
                            ->state(fn (Get $get, ?FacilityRegistrationRequest $record): string => static::getMonthDurationInfo($get, $record)),
                    ])
                    ->visible(fn (Get $get, ?FacilityRegistrationRequest $record): bool => static::resolveMonthIndex($get, $record) !== null)
                    ->columnSpanFull(),
                ...static::getWishFormFields(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['user', 'assignedFacility', 'assignedSpecialization', 'wishes']))
            ->columns([
                TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('user.name')
                    ->label('الطالب')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('user.gpa')
                    ->label('المعدل')
                    ->sortable(),
                TextColumn::make('month_index')
                    ->label('الشهر')
                    ->formatStateUsing(fn (int $state): string => Month::labelFor($state))
                    ->sortable(),
                TextColumn::make('assignedFacility.name')
                    ->label('المنشأة المعينة')
                    ->placeholder('لم يتم التعيين'),
                TextColumn::make('assignedSpecialization.name')
                    ->label('التخصص المعين')
                    ->placeholder('لم يتم التعيين'),
                TextColumn::make('wishes_count')
                    ->label('عدد الرغبات')
                    ->counts('wishes'),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('user')
                    ->label('الطالب')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload(),
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
            'index' => ListFacilityRegistrationRequests::route('/'),
            'create' => CreateFacilityRegistrationRequest::route('/create'),
            'edit' => EditFacilityRegistrationRequest::route('/{record}/edit'),
        ];
    }

    public static function getWishFormFields(): array
    {
        return [
            Repeater::make('wishes')
                ->columnSpanFull()
                ->label('رغبات المنشأة')
                ->relationship('wishes')
                ->live()
                ->deletable(false)
                ->addable(false)
                ->minItems(self::WISH_COUNT)
                ->maxItems(self::WISH_COUNT)
                ->defaultItems(self::WISH_COUNT)
                ->visible(fn (Get $get, ?FacilityRegistrationRequest $record): bool => static::shouldShowWishes(
                    $get,
                    $record,
                ))
                ->schema([
                    Hidden::make('priority')
                        ->default(function (Get $get, $component) {
                            $wishes = $get('data.wishes', true);
                            $path = explode('.', $component->getStatePath())[2];

                            return array_search($path, array_keys($wishes)) + 1;
                        })
                        ->required(),
                    Toggle::make('is_custom')
                        ->label('منشأة مخصصة')
                        ->helperText('اختر منشأة غير مسجلة في النظام (لن تدخل في المنافسة)')
                        ->live(),
                    Select::make('specialization_id')
                        ->label('التخصص (للأشهر الاختيارية)')
                        ->live()
                        ->afterStateUpdated(function (callable $set): void {
                            $set('facility_id', null);
                        })
                        ->options(fn (Get $get): array => static::getElectiveSpecializationOptions(
                            $get,
                        ))
                        ->searchable()
                        ->preload()
                        ->visible(fn (Get $get): bool => static::isElectiveMonth(
                            $get,
                        ) && ! $get('is_custom'))
                        ->required(fn (Get $get): bool => static::isElectiveMonth(
                            $get,
                        ) && ! $get('is_custom')),
                    Select::make('facility_id')
                        ->label(function (Get $get, $component): string {
                            $priority = ($component->getParentRepeaterItemIndex() ?? 0) + 1;

                            return "الرغبة {$priority}";
                        })
                        ->live()
                        ->options(fn (Get $get, Select $component): array => static::getAvailableFacilityOptions(
                            $get,
                            $component,
                        ))
                        ->searchable()
                        ->preload()
                        ->visible(fn (Get $get) => ! $get('is_custom'))
                        ->disabled(fn (Get $get): bool => static::isElectiveMonth(
                            $get,
                        ) && ! $get('specialization_id') && ! $get('is_custom')),
                    TextInput::make('custom_facility_name')
                        ->label('اسم المنشأة المخصصة')
                        ->visible(fn (Get $get) => $get('is_custom'))
                        ->required(fn (Get $get) => $get('is_custom')),
                    TextInput::make('custom_specialization_name')
                        ->label('اسم التخصص المخصص')
                        ->visible(fn (Get $get) => $get('is_custom'))
                        ->required(fn (Get $get) => $get('is_custom')),
                ]),
        ];
    }

    private static function shouldShowWishes(Get $get, ?FacilityRegistrationRequest $record = null): bool
    {
        $user = static::resolveFormUser($get, $record);

        if (! $user) {
            return false;
        }

        $monthIndex = static::resolveMonthIndex($get, $record);

        return $monthIndex !== null;
    }

    /**
     * @return array<int, string>
     */
    private static function getAvailableMonthOptions(Get $get, ?FacilityRegistrationRequest $record = null): array
    {
        $user = static::resolveFormUser($get, $record);

        if (! $user || ! $user->track) {
            return [];
        }

        $track = $user->track;
        $trackSpecializations = $track->trackSpecializations;
        $electiveMonths = $track->elective_months ?? [];
        $blockedMonths = static::getBlockedMonthsForUser($user, $trackSpecializations, $record?->id);

        // Build a map of start months with their ranges
        $monthRanges = [];

        // Process track specializations
        foreach ($trackSpecializations as $trackSpecialization) {
            $startMonth = (int) $trackSpecialization->month_index;
            $durationMonths = static::normalizeDuration($trackSpecialization->specialization?->duration_months);
            $endMonth = min(12, $startMonth + $durationMonths - 1);

            if (! in_array($startMonth, $blockedMonths, true)) {
                $monthRanges[$startMonth] = [
                    'start' => $startMonth,
                    'end' => $endMonth,
                    'duration' => $durationMonths,
                ];
            }
        }

        // Process elective months
        foreach ($electiveMonths as $month) {
            $month = (int) $month;

            if ($month < 1 || $month > 12) {
                continue;
            }

            if (in_array($month, $blockedMonths, true)) {
                continue;
            }

            // Check if this month is already covered by a track specialization
            $isCovered = false;

            foreach ($monthRanges as $range) {
                if ($month >= $range['start'] && $month <= $range['end']) {
                    $isCovered = true;
                    break;
                }
            }

            if (! $isCovered) {
                $monthRanges[$month] = [
                    'start' => $month,
                    'end' => $month,
                    'duration' => 1,
                ];
            }
        }

        // Add record month if it's not in the available months
        if ($record?->month_index) {
            $recordMonth = (int) $record->month_index;

            if (! isset($monthRanges[$recordMonth])) {
                // Find the range for this record month
                $trackSpecialization = static::findTrackSpecializationForMonth($trackSpecializations, $recordMonth);

                if ($trackSpecialization) {
                    $startMonth = (int) $trackSpecialization->month_index;
                    $durationMonths = static::normalizeDuration($trackSpecialization->specialization?->duration_months);
                    $endMonth = min(12, $startMonth + $durationMonths - 1);

                    $monthRanges[$recordMonth] = [
                        'start' => $recordMonth,
                        'end' => $endMonth,
                        'duration' => $durationMonths,
                    ];
                } elseif (in_array($recordMonth, $electiveMonths, true)) {
                    $monthRanges[$recordMonth] = [
                        'start' => $recordMonth,
                        'end' => $recordMonth,
                        'duration' => 1,
                    ];
                }
            }
        }

        // Build options with range labels
        $options = [];
        $orderedMonths = Month::orderedMonths(array_keys($monthRanges));

        foreach ($orderedMonths as $monthIndex) {
            $range = $monthRanges[$monthIndex];

            if ($range['duration'] === 1) {
                $options[$monthIndex] = Month::labelFor($monthIndex);
            } else {
                $startLabel = Month::labelFor($range['start']);
                $endLabel = Month::labelFor($range['end']);
                $options[$monthIndex] = "{$startLabel} - {$endLabel}";
            }
        }

        return $options;
    }

    /**
     * @return array<int, string>
     */
    private static function getAvailableFacilityOptions(
        Get $get,
        Select $component,
    ): array {
        $monthIndex = static::resolveWishMonthIndex($get);
        $isElective = static::isElectiveMonth($get);

        if (! $monthIndex) {
            return [];
        }

        $specializationId = $isElective
            ? $get('specialization_id')
            : static::resolveSpecializationIdForWish($get);

        if (! $isElective && ! $specializationId) {
            return [];
        }

        $currentItemKey = static::resolveRepeaterItemKey($component);
        $selectedFacilityIds = static::getSelectedFacilityIds($get, $currentItemKey);

        $seatQuery = FacilitySeat::query()
            ->select('facility_id')
            ->where('month_index', $monthIndex);

        if ($specializationId) {
            $seatQuery->where('specialization_id', $specializationId);
        }

        $query = Facility::query()
            ->whereIn('id', $seatQuery->distinct());

        // Filter facilities by type matching the specialization's facility_type
        if ($specializationId) {
            $specialization = Specialization::find($specializationId);

            if ($specialization) {
                $query->where('type', $specialization->facility_type);
            }
        }

        if ($selectedFacilityIds !== []) {
            $query->whereNotIn('id', $selectedFacilityIds);
        }

        return $query->orderBy('name')->pluck('name', 'id')->all();
    }

    /**
     * @return array<int, string>
     */
    private static function getElectiveSpecializationOptions(Get $get): array
    {
        $monthIndex = static::resolveWishMonthIndex($get);

        if (! $monthIndex) {
            return [];
        }

        $specializationIds = FacilitySeat::query()
            ->where('month_index', $monthIndex)
            ->select('specialization_id')
            ->distinct();

        return Specialization::query()
            ->whereIn('id', $specializationIds)
            ->orderBy('name')
            ->pluck('name', 'id')
            ->all();
    }

    private static function isElectiveMonth(Get $get): bool
    {
        $monthIndex = static::resolveWishMonthIndex($get);

        if (! $monthIndex) {
            return false;
        }

        $track = static::resolveFormUser($get)?->track;
        $electiveMonths = $track?->elective_months ?? [];

        return in_array($monthIndex, $electiveMonths, true);
    }

    private static function resolveFormUser(Get $get, ?FacilityRegistrationRequest $record = null): ?User
    {
        if (! auth()->user()->hasRole('admin')) {
            $user = auth()->user();

            if (! $user) {
                return null;
            }

            $user->loadMissing('track.trackSpecializations.specialization');

            return $user;
        }

        // Try to get user_id from different contexts (root level or from within repeater)
        $userId = $get('user_id') ?? $get('../../user_id') ?? $record?->user_id;

        if (! $userId) {
            return null;
        }

        return User::query()
            ->with('track.trackSpecializations.specialization')
            ->find($userId);
    }

    private static function resolveMonthIndex(Get $get, ?FacilityRegistrationRequest $record = null): ?int
    {
        $monthIndex = $get('month_index') ?? $record?->month_index;

        if (! $monthIndex) {
            return null;
        }

        return (int) $monthIndex;
    }

    private static function resolveWishMonthIndex(Get $get): ?int
    {
        $monthIndex = $get('../../month_index');

        if (! $monthIndex) {
            return null;
        }

        return (int) $monthIndex;
    }

    private static function resolveSpecializationIdForWish(Get $get): ?int
    {
        $monthIndex = static::resolveWishMonthIndex($get);

        if (! $monthIndex) {
            return null;
        }

        $user = static::resolveFormUser($get);

        if (! $user || ! $user->track) {
            return null;
        }

        if (static::isElectiveMonth($get)) {
            return $get('specialization_id');
        }

        $trackSpecialization = static::findTrackSpecializationForMonth(
            $user->track->trackSpecializations,
            $monthIndex,
        );

        return $trackSpecialization?->specialization_id;
    }

    /**
     * @param  \Illuminate\Support\Collection<int, \App\Models\TrackSpecialization>  $trackSpecializations
     * @return array<int, int>
     */
    private static function getScheduleMonths(Collection $trackSpecializations, array $electiveMonths): array
    {
        $months = [];

        foreach ($trackSpecializations as $trackSpecialization) {
            $durationMonths = static::normalizeDuration($trackSpecialization->specialization?->duration_months);
            $startMonth = (int) $trackSpecialization->month_index;
            $endMonth = min(12, $startMonth + $durationMonths - 1);

            for ($month = $startMonth; $month <= $endMonth; $month++) {
                $months[$month] = true;
            }
        }

        foreach ($electiveMonths as $month) {
            $month = (int) $month;

            if ($month < 1 || $month > 12) {
                continue;
            }

            $months[$month] = true;
        }

        $scheduleMonths = array_keys($months);
        sort($scheduleMonths);

        return $scheduleMonths;
    }

    /**
     * @param  \Illuminate\Support\Collection<int, \App\Models\TrackSpecialization>  $trackSpecializations
     * @return array<int, int>
     */
    private static function getBlockedMonthsForUser(
        User $user,
        Collection $trackSpecializations,
        ?int $ignoreRequestId = null,
    ): array {
        $query = FacilityRegistrationRequest::query()->where('user_id', $user->id);

        if ($ignoreRequestId) {
            $query->whereKeyNot($ignoreRequestId);
        }

        $requests = $query->with('wishes.specialization')->get(['id', 'month_index', 'user_id']);
        $blockedMonths = [];

        foreach ($requests as $request) {
            $monthIndex = (int) $request->month_index;
            $trackSpecialization = static::findTrackSpecializationForMonth($trackSpecializations, $monthIndex);

            if ($trackSpecialization) {
                $startMonth = (int) $trackSpecialization->month_index;
                $durationMonths = static::normalizeDuration($trackSpecialization->specialization?->duration_months);
            } else {
                $startMonth = $monthIndex;
                $durationMonths = static::normalizeDuration(
                    $request->wishes
                        ->pluck('specialization')
                        ->filter()
                        ->first()?->duration_months,
                );
            }

            $endMonth = min(12, $startMonth + $durationMonths - 1);

            for ($month = $startMonth; $month <= $endMonth; $month++) {
                $blockedMonths[$month] = true;
            }
        }

        return array_keys($blockedMonths);
    }

    /**
     * @param  \Illuminate\Support\Collection<int, \App\Models\TrackSpecialization>  $trackSpecializations
     */
    private static function findTrackSpecializationForMonth(
        Collection $trackSpecializations,
        int $monthIndex,
    ): ?TrackSpecialization {
        foreach ($trackSpecializations as $trackSpecialization) {
            $durationMonths = static::normalizeDuration($trackSpecialization->specialization?->duration_months);
            $startMonth = (int) $trackSpecialization->month_index;
            $endMonth = min(12, $startMonth + $durationMonths - 1);

            if ($monthIndex >= $startMonth && $monthIndex <= $endMonth) {
                return $trackSpecialization;
            }
        }

        return null;
    }

    private static function normalizeDuration(?int $durationMonths): int
    {
        return max(1, (int) $durationMonths);
    }

    private static function getMonthTrackInfo(Get $get, ?FacilityRegistrationRequest $record = null): string
    {
        $user = static::resolveFormUser($get, $record);

        return $user?->track?->name ?? '-';
    }

    private static function getMonthSpecializationInfo(Get $get, ?FacilityRegistrationRequest $record = null): string
    {
        $monthIndex = static::resolveMonthIndex($get, $record);

        if (! $monthIndex) {
            return '-';
        }

        $user = static::resolveFormUser($get, $record);

        if (! $user || ! $user->track) {
            return '-';
        }

        $track = $user->track;
        $electiveMonths = $track->elective_months ?? [];

        if (in_array($monthIndex, $electiveMonths, true)) {
            return 'شهر اختياري';
        }

        $trackSpecialization = static::findTrackSpecializationForMonth(
            $track->trackSpecializations,
            $monthIndex,
        );

        return $trackSpecialization?->specialization?->name ?? '-';
    }

    private static function getMonthDurationInfo(Get $get, ?FacilityRegistrationRequest $record = null): string
    {
        $monthIndex = static::resolveMonthIndex($get, $record);

        if (! $monthIndex) {
            return '-';
        }

        $user = static::resolveFormUser($get, $record);

        if (! $user || ! $user->track) {
            return '-';
        }

        $track = $user->track;
        $electiveMonths = $track->elective_months ?? [];

        if (in_array($monthIndex, $electiveMonths, true)) {
            return 'شهر واحد';
        }

        $trackSpecialization = static::findTrackSpecializationForMonth(
            $track->trackSpecializations,
            $monthIndex,
        );

        $duration = static::normalizeDuration($trackSpecialization?->specialization?->duration_months);

        return $duration === 1 ? 'شهر واحد' : "{$duration} أشهر";
    }

    private static function resolveRepeaterItemKey(Select $component): ?string
    {
        $segments = explode('.', $component->getStatePath());

        return $segments[2] ?? null;
    }

    /**
     * @return array<int, int>
     */
    private static function getSelectedFacilityIds(Get $get, ?string $currentItemKey): array
    {
        $wishes = $get('data.wishes', true);

        if (! is_array($wishes)) {
            return [];
        }

        if ($currentItemKey && array_key_exists($currentItemKey, $wishes)) {
            unset($wishes[$currentItemKey]);
        }

        $wishes = array_values($wishes);
        $wishes = array_filter($wishes, fn (array $wish): bool => ! empty($wish['facility_id']));

        return array_values(array_unique(array_map(
            fn (array $wish): int => (int) $wish['facility_id'],
            $wishes,
        )));
    }
}
