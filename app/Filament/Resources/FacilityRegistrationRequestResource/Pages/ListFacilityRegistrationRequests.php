<?php

namespace App\Filament\Resources\FacilityRegistrationRequestResource\Pages;

use App\Filament\Resources\FacilityRegistrationRequestResource;
use App\Models\FacilityRegistrationRequest;
use App\Models\FacilitySeat;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListFacilityRegistrationRequests extends ListRecords
{
    protected static string $resource = FacilityRegistrationRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
            Action::make('distribute')
                ->label('توزيع الطلاب على المنشآت')
                ->visible(fn () => auth()->user()->hasRole('admin'))
                ->form([
                    Select::make('month_index')
                        ->searchable()
                        ->preload()
                        ->label('الشهر')
                        ->options(function () {
                            $options = [];
                            for ($i = 1; $i <= 12; $i++) {
                                $options[$i] = "الشهر {$i}";
                            }

                            return $options;
                        })
                        ->required(),
                ])
                ->action(function (array $data) {
                    $monthIndex = $data['month_index'];

                    // Reset all assigned facilities for this month
                    FacilityRegistrationRequest::where('month_index', $monthIndex)
                        ->update(['assigned_facility_id' => null]);

                    // Get all requests for this month with competitive wishes
                    // Order by user GPA (descending)
                    $requests = FacilityRegistrationRequest::where('month_index', $monthIndex)
                        ->with(['user', 'competitiveWishes.facility'])
                        ->get()
                        ->sortByDesc(fn ($request) => $request->user->gpa);

                    // Get facility capacities for this month
                    $facilityCapacities = FacilitySeat::where('month_index', $monthIndex)
                        ->pluck('max_seats', 'facility_id')
                        ->toArray();

                    $facilityCurrentCounts = [];

                    foreach ($requests as $request) {
                        $wishes = $request->competitiveWishes;

                        foreach ($wishes as $wish) {
                            if (! $wish->facility_id) {
                                continue;
                            }

                            $facilityId = $wish->facility_id;
                            $currentCount = $facilityCurrentCounts[$facilityId] ?? 0;
                            $maxCapacity = $facilityCapacities[$facilityId] ?? 0;

                            if ($currentCount < $maxCapacity) {
                                $request->assigned_facility_id = $facilityId;
                                $request->save();
                                $facilityCurrentCounts[$facilityId] = $currentCount + 1;

                                break;
                            }
                        }
                    }

                    Notification::make()
                        ->title("تم توزيع الطلاب على المنشآت للشهر {$monthIndex}")
                        ->success()
                        ->send();
                })
                ->icon('heroicon-o-arrows-right-left')
                ->color('primary'),
        ];
    }
}
