<?php

namespace App\Filament\Resources\TrackResource\RelationManagers;

use App\Models\Specialization;
use App\Models\TrackSpecialization;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TrackSpecializationsRelationManager extends RelationManager
{
    protected static string $relationship = 'trackSpecializations';

    protected static ?string $title = 'التخصصات';

    protected static ?string $pluralModelLabel = 'التخصصات';

    protected static ?string $modelLabel = 'تخصص';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('specialization.name')
            ->columns([
                TextColumn::make('month_index')
                    ->label('الشهر')
                    ->sortable()
                    ->formatStateUsing(fn (int $state): string => "الشهر {$state}"),
                TextColumn::make('specialization.name')
                    ->label('التخصص')
                    ->searchable(),
                TextColumn::make('specialization.duration_months')
                    ->label('المدة (أشهر)'),
                TextColumn::make('specialization.facility_type')
                    ->label('نوع المنشأة')
                    ->formatStateUsing(fn ($state): string => $state->label()),
            ])
            ->defaultSort('month_index')
            ->headerActions([
                CreateAction::make()
                    ->form($this->getFormSchema()),
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
            Select::make('month_index')
                ->label('الشهر')
                ->searchable()
                ->preload()
                ->live()
                ->options(fn (Get $get, ?TrackSpecialization $record): array => $this->getAvailableMonthOptions(
                    $get('specialization_id'),
                    $record?->id,
                ))
                ->rule(function (Get $get): \Closure {
                    return function (string $attribute, mixed $value, \Closure $fail) use ($get): void {
                        if (! $value) {
                            return;
                        }

                        $track = $this->getOwnerRecord();

                        if (! $track) {
                            return;
                        }

                        $specializationId = $get('specialization_id');

                        if (! $specializationId) {
                            return;
                        }

                        $durationMonths = $this->normalizeDuration(
                            Specialization::query()->whereKey($specializationId)->value('duration_months'),
                        );
                        $startMonth = (int) $value;
                        $endMonth = min(12, $startMonth + $durationMonths - 1);
                        $range = range($startMonth, $endMonth);
                        $conflicts = array_intersect($range, $track->getElectiveMonths());

                        if ($conflicts === []) {
                            return;
                        }

                        $conflicts = array_values(array_unique($conflicts));
                        sort($conflicts);

                        $labels = implode('، ', array_map(
                            fn (int $month): string => "الشهر {$month}",
                            $conflicts,
                        ));

                        $fail("لا يمكن اختيار تخصص يغطي أشهر اختيارية: {$labels}.");
                    };
                })
                ->required(),
            Select::make('specialization_id')
                ->label('التخصص')
                ->searchable()
                ->preload()
                ->live()
                ->options(fn (Get $get, ?TrackSpecialization $record): array => $this->getAvailableSpecializationOptions(
                    $get('month_index'),
                    $record?->id,
                ))
                ->required(),
        ];
    }

    /**
     * @return array<int, int>
     */
    private function getBlockedMonths(?int $ignoreRecordId = null): array
    {
        $track = $this->getOwnerRecord();
        $blockedMonths = array_merge(
            $track->getSpecializationMonths($ignoreRecordId),
            $track->getElectiveMonths(),
        );

        $blockedMonths = array_values(array_unique($blockedMonths));
        sort($blockedMonths);

        return $blockedMonths;
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
        $blockedMonths = $this->getBlockedMonths($ignoreRecordId);
        $durationMonths = $specializationId
            ? $this->normalizeDuration(
                Specialization::query()->whereKey($specializationId)->value('duration_months'),
            )
            : 1;
        $options = [];

        for ($month = 1; $month <= 12; $month++) {
            if (! $this->isRangeAvailable($month, $durationMonths, $blockedMonths)) {
                continue;
            }

            $options[$month] = "الشهر {$month}";
        }

        return $options;
    }

    /**
     * @return array<int, string>
     */
    private function getAvailableSpecializationOptions(?int $monthIndex, ?int $ignoreRecordId = null): array
    {
        $query = Specialization::query()->orderBy('name');

        if (! $monthIndex) {
            return $query->pluck('name', 'id')->all();
        }

        $blockedMonths = $this->getBlockedMonths($ignoreRecordId);

        return $query->get()
            ->filter(fn (Specialization $specialization): bool => $this->isRangeAvailable(
                $monthIndex,
                $specialization->duration_months,
                $blockedMonths,
            ))
            ->mapWithKeys(fn (Specialization $specialization): array => [$specialization->id => $specialization->name])
            ->all();
    }
}
