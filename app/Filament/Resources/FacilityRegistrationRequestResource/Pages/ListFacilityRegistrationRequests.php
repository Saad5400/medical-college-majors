<?php

namespace App\Filament\Resources\FacilityRegistrationRequestResource\Pages;

use App\Enums\Month;
use App\Filament\Resources\FacilityRegistrationRequestResource;
use App\Models\FacilityRegistrationRequest;
use App\Models\FacilitySeat;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\DB;

use App\Filament\Resources\FacilityRegistrationRequestResource\Widgets\StudentFacilityRequestsWidget;

class ListFacilityRegistrationRequests extends ListRecords
{
    protected static string $resource = FacilityRegistrationRequestResource::class;

    protected function getFooterWidgets(): array
    {
        return [
            StudentFacilityRequestsWidget::class,
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
            Action::make('distribute')
                ->label('Distribute students to facilities')
                ->visible(fn () => auth()->user()->hasRole('admin'))
                ->form([
                    Select::make('month_index')
                        ->searchable()
                        ->preload()
                        ->label('Month')
                        ->options(Month::options())
                        ->required(),
                ])
                ->action(function (array $data) {
                    $monthIndex = $data['month_index'];

                    $monthLabel = DB::transaction(function () use ($monthIndex): string {
                        // Reset all assigned facilities for this month
                        FacilityRegistrationRequest::where('month_index', $monthIndex)
                            ->update([
                                'assigned_facility_id' => null,
                                'assigned_specialization_id' => null,
                            ]);

                        // Get all requests for this month with competitive wishes
                        // Order by user GPA (descending)
                        $requests = FacilityRegistrationRequest::where('month_index', $monthIndex)
                            ->with(['user.track.trackSpecializations.specialization', 'wishes.facility'])
                            ->orderByDesc(User::select('gpa')
                                ->whereColumn('users.id', 'facility_registration_requests.user_id'))
                            ->orderBy('facility_registration_requests.created_at')
                            ->orderBy('facility_registration_requests.id')
                            ->lockForUpdate()
                            ->get();

                        // Get facility capacities for this month
                        $facilityCapacities = FacilitySeat::where('month_index', $monthIndex)
                            ->lockForUpdate()
                            ->get(['facility_id', 'specialization_id', 'max_seats'])
                            ->mapWithKeys(function (FacilitySeat $seat): array {
                                return [
                                    static::buildCapacityKey($seat->facility_id, $seat->specialization_id) => $seat->max_seats,
                                ];
                            })
                            ->toArray();

                        $facilityCurrentCounts = [];

                        foreach ($requests as $request) {
                            $wishes = $request->competitiveWishes;

                            foreach ($wishes as $wish) {
                                if (! $wish->facility_id) {
                                    continue;
                                }

                                $facilityId = $wish->facility_id;
                                $specializationId = static::resolveSpecializationIdForWish(
                                    $request,
                                    $monthIndex,
                                    $wish->specialization_id,
                                );

                                if (! $specializationId) {
                                    continue;
                                }

                                $capacityKey = static::buildCapacityKey($facilityId, $specializationId);
                                $currentCount = $facilityCurrentCounts[$capacityKey] ?? 0;
                                $maxCapacity = $facilityCapacities[$capacityKey] ?? 0;

                                if ($currentCount < $maxCapacity) {
                                    $request->assigned_facility_id = $facilityId;
                                    $request->assigned_specialization_id = $specializationId;
                                    $request->save();
                                    $facilityCurrentCounts[$capacityKey] = $currentCount + 1;

                                    break;
                                }
                            }
                        }

                        return Month::labelFor($monthIndex);
                    });

                    Notification::make()
                        ->title("Students distributed to facilities for {$monthLabel}")
                        ->success()
                        ->send();
                })
                ->icon('heroicon-o-arrows-right-left')
                ->color('primary'),
        ];
    }

    private static function buildCapacityKey(int $facilityId, int $specializationId): string
    {
        return "{$facilityId}:{$specializationId}";
    }

    private static function resolveSpecializationIdForWish(
        FacilityRegistrationRequest $request,
        int $monthIndex,
        ?int $wishSpecializationId,
    ): ?int {
        if ($wishSpecializationId) {
            return $wishSpecializationId;
        }

        $trackSpecializations = $request->user?->track?->trackSpecializations;

        if (! $trackSpecializations) {
            return null;
        }

        foreach ($trackSpecializations as $trackSpecialization) {
            $durationMonths = max(1, (int) $trackSpecialization->specialization?->duration_months);
            $startMonth = (int) $trackSpecialization->month_index;
            $endMonth = min(12, $startMonth + $durationMonths - 1);

            if ($monthIndex >= $startMonth && $monthIndex <= $endMonth) {
                return $trackSpecialization->specialization_id;
            }
        }

        return null;
    }
}
