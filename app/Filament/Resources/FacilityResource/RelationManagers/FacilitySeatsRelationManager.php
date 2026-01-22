<?php

namespace App\Filament\Resources\FacilityResource\RelationManagers;

use App\Enums\Month;
use App\Models\FacilitySeat;
use App\Models\Specialization;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class FacilitySeatsRelationManager extends RelationManager
{
    protected static string $relationship = 'facilitySeats';

    protected static ?string $title = 'المقاعد المتاحة';

    protected static ?string $pluralModelLabel = 'المقاعد';

    protected static ?string $modelLabel = 'مقعد';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('specialization.name')
            ->columns([
                TextColumn::make('month_index')
                    ->label('الشهر')
                    ->sortable()
                    ->formatStateUsing(fn (int $state): string => Month::labelFor($state))
                    ->searchable(
                        query: function ($query, $search) {
                            $monthIndexes = collect(Month::cases())
                                ->filter(fn ($month) => str_contains(mb_strtolower(Month::labelFor($month->value)), mb_strtolower($search))
                                )
                                ->pluck('value')
                                ->toArray();

                            if (! empty($monthIndexes)) {
                                $query->whereIn('month_index', $monthIndexes);
                            } else {
                                // Ensures no results if no matches found.
                                $query->whereRaw('0 = 1');
                            }
                        },
                    ),
                TextColumn::make('specialization.name')
                    ->sortable()
                    ->label('التخصص')
                    ->searchable(),
                TextColumn::make('max_seats')
                    ->label('الحد الأقصى للمقاعد')
                    ->sortable(),
            ])
            ->defaultSort('month_index')
            ->headerActions([
                CreateAction::make()
                    ->form($this->getFormSchema()),
                Action::make('createForAllMonths')
                    ->label('إضافة مقعد لكل الشهور')
                    ->form($this->getBulkCreateFormSchema())
                    ->action(function (array $data): void {
                        $createdCount = $this->createSeatsForAllMonths(
                            (int) $data['specialization_id'],
                            (int) $data['max_seats'],
                        );

                        if ($createdCount === 0) {
                            Notification::make()
                                ->title('لا توجد أشهر متاحة لهذا التخصص.')
                                ->warning()
                                ->send();

                            return;
                        }

                        Notification::make()
                            ->title("تمت إضافة المقاعد لعدد {$createdCount} شهرًا.")
                            ->success()
                            ->send();
                    }),
                Action::make('createForAllSpecializations')
                    ->label('إضافة مقعد لكل الشهور ولكل التخصصات')
                    ->form($this->getBulkCreateAllSpecializationsFormSchema())
                    ->action(function (array $data): void {
                        $createdCount = $this->createSeatsForAllSpecializations((int) $data['max_seats']);

                        if ($createdCount === 0) {
                            Notification::make()
                                ->title('لا توجد أشهر متاحة للتخصصات المحددة.')
                                ->warning()
                                ->send();

                            return;
                        }

                        Notification::make()
                            ->title("تمت إضافة المقاعد لعدد {$createdCount} شهرًا.")
                            ->success()
                            ->send();
                    }),
            ])
            ->recordActions([
                EditAction::make()
                    ->form($this->getFormSchema()),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                DeleteBulkAction::make(),
            ])
            ->defaultPaginationPageOption(25)
            ->paginationPageOptions([25, 50, 100]);
    }

    /**
     * @return array<int, \Filament\Forms\Components\Component>
     */
    private function getFormSchema(): array
    {
        return [
            Select::make('specialization_id')
                ->label('التخصص')
                ->searchable()
                ->preload()
                ->live()
                ->options(fn (Get $get, ?FacilitySeat $record): array => $this->getAvailableSpecializationOptions(
                    $get('month_index'),
                    $record?->id,
                ))
                ->required(),
            Select::make('month_index')
                ->label('الشهر')
                ->searchable()
                ->preload()
                ->live()
                ->options(fn (Get $get, ?FacilitySeat $record): array => $this->getAvailableMonthOptions(
                    $get('specialization_id'),
                    $record?->id,
                ))
                ->required(),
            TextInput::make('max_seats')
                ->label('الحد الأقصى للمقاعد')
                ->required()
                ->integer()
                ->minValue(0),
        ];
    }

    /**
     * @return array<int, \Filament\Forms\Components\Component>
     */
    private function getBulkCreateFormSchema(): array
    {
        return [
            Select::make('specialization_id')
                ->label('التخصص')
                ->searchable()
                ->preload()
                ->options(fn (): array => $this->getAvailableSpecializationOptions(null))
                ->required(),
            TextInput::make('max_seats')
                ->label('الحد الأقصى للمقاعد')
                ->required()
                ->integer()
                ->minValue(0),
        ];
    }

    /**
     * @return array<int, \Filament\Forms\Components\Component>
     */
    private function getBulkCreateAllSpecializationsFormSchema(): array
    {
        return [
            TextInput::make('max_seats')
                ->label('الحد الأقصى للمقاعد')
                ->required()
                ->integer()
                ->minValue(0),
        ];
    }

    /**
     * @return array<int, int>
     */
    private function getBlockedMonths(?int $specializationId, ?int $ignoreRecordId = null): array
    {
        if (! $specializationId) {
            return [];
        }

        $query = $this->getOwnerRecord()
            ->facilitySeats()
            ->with('specialization');

        if ($ignoreRecordId) {
            $query->whereKeyNot($ignoreRecordId);
        }

        $query->where('specialization_id', $specializationId);

        $blockedMonths = [];

        foreach ($query->get() as $seat) {
            $durationMonths = $this->normalizeDuration($seat->specialization?->duration_months);
            $startMonth = (int) $seat->month_index;
            $endMonth = min(12, $startMonth + $durationMonths - 1);

            for ($month = $startMonth; $month <= $endMonth; $month++) {
                $blockedMonths[$month] = true;
            }
        }

        return array_keys($blockedMonths);
    }

    private function normalizeDuration(?int $durationMonths): int
    {
        return max(1, (int) $durationMonths);
    }

    /**
     * @param  array<int, int>  $blockedMonths
     */
    private function isRangeAvailable(int $startMonth, int $durationMonths, array $blockedMonths): bool
    {
        $durationMonths = $this->normalizeDuration($durationMonths);
        $endMonth = $startMonth + $durationMonths - 1;

        if ($endMonth > 12) {
            return false;
        }

        $range = range($startMonth, $endMonth);

        return array_intersect($range, $blockedMonths) === [];
    }

    /**
     * @return array<int, string>
     */
    private function getAvailableMonthOptions(?int $specializationId, ?int $ignoreRecordId = null): array
    {
        $blockedMonths = $this->getBlockedMonths($specializationId, $ignoreRecordId);
        $durationMonths = $specializationId
            ? $this->normalizeDuration(
                Specialization::query()->whereKey($specializationId)->value('duration_months'),
            )
            : 1;
        $options = [];

        foreach (Month::orderFrom() as $month) {
            if (! $this->isRangeAvailable($month, $durationMonths, $blockedMonths)) {
                continue;
            }

            $options[$month] = Month::labelFor($month);
        }

        return $options;
    }

    /**
     * @return array<int, int>
     */
    private function getAvailableMonthsForSpecialization(int $specializationId, ?int $durationMonths = null): array
    {
        $blockedMonths = $this->getBlockedMonths($specializationId);
        $durationMonths = $this->normalizeDuration(
            $durationMonths ?? Specialization::query()->whereKey($specializationId)->value('duration_months'),
        );
        $availableMonths = [];

        foreach (Month::orderFrom() as $month) {
            if (! $this->isRangeAvailable($month, $durationMonths, $blockedMonths)) {
                continue;
            }

            $availableMonths[] = $month;
            $endMonth = min(12, $month + $durationMonths - 1);

            for ($blockedMonth = $month; $blockedMonth <= $endMonth; $blockedMonth++) {
                $blockedMonths[] = $blockedMonth;
            }

            $blockedMonths = array_values(array_unique($blockedMonths));
            sort($blockedMonths);
        }

        return $availableMonths;
    }

    private function createSeatsForAllMonths(int $specializationId, int $maxSeats): int
    {
        $months = $this->getAvailableMonthsForSpecialization($specializationId);

        if ($months === []) {
            return 0;
        }

        $payload = array_map(
            fn (int $month): array => [
                'specialization_id' => $specializationId,
                'month_index' => $month,
                'max_seats' => $maxSeats,
            ],
            $months,
        );

        $this->getOwnerRecord()->facilitySeats()->createMany($payload);

        return count($months);
    }

    private function createSeatsForAllSpecializations(int $maxSeats): int
    {
        $specializations = Specialization::query()
            ->where('facility_type', $this->getOwnerRecord()->type)
            ->orderBy('name')
            ->get(['id', 'duration_months']);
        $createdCount = 0;

        foreach ($specializations as $specialization) {
            $months = $this->getAvailableMonthsForSpecialization(
                $specialization->id,
                $specialization->duration_months,
            );

            if ($months === []) {
                continue;
            }

            $payload = array_map(
                fn (int $month): array => [
                    'specialization_id' => $specialization->id,
                    'month_index' => $month,
                    'max_seats' => $maxSeats,
                ],
                $months,
            );

            $this->getOwnerRecord()->facilitySeats()->createMany($payload);
            $createdCount += count($months);
        }

        return $createdCount;
    }

    /**
     * @return array<int, string>
     */
    private function getAvailableSpecializationOptions(?int $monthIndex, ?int $ignoreRecordId = null): array
    {
        $query = Specialization::query()
            ->where('facility_type', $this->getOwnerRecord()->type)
            ->orderBy('name');

        if (! $monthIndex) {
            return $query->pluck('name', 'id')->all();
        }

        return $query->get()
            ->filter(function (Specialization $specialization) use ($monthIndex, $ignoreRecordId): bool {
                $blockedMonths = $this->getBlockedMonths($specialization->id, $ignoreRecordId);

                return $this->isRangeAvailable(
                    $monthIndex,
                    $specialization->duration_months,
                    $blockedMonths,
                );
            })
            ->mapWithKeys(fn (Specialization $specialization): array => [$specialization->id => $specialization->name])
            ->all();
    }
}
