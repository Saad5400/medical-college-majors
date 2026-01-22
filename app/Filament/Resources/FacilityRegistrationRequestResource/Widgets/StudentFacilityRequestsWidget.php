<?php

namespace App\Filament\Resources\FacilityRegistrationRequestResource\Widgets;

use App\Enums\Month;
use App\Models\FacilityRegistrationRequest;
use Filament\Widgets\Widget;
use Illuminate\Support\Collection;

class StudentFacilityRequestsWidget extends Widget
{
    protected string $view = 'filament.resources.facility-registration-request-resource.widgets.student-facility-requests-widget';

    protected int|string|array $columnSpan = 'full';

    public static function canView(): bool
    {
        return auth()->user()->hasRole('student');
    }

    protected function getViewData(): array

    {

        $user = auth()->user();


        $months = Month::orderFrom(7);


        $requests = FacilityRegistrationRequest::where('user_id', $user->id)
            ->with(['assignedFacility', 'assignedSpecialization', 'wishes.facility', 'wishes.specialization'])
            ->get()
            ->keyBy('month_index');


        // Calculate schedule structure for row merging

        $schedule = [];

        $processedMonths = [];


        // Eager load track relations if not already loaded

        $user->loadMissing(['track.trackSpecializations.specialization']);


        $trackSpecializations = $user->track?->trackSpecializations ?? collect();

        $electiveMonths = $user->track?->elective_months ?? [];


        foreach ($months as $month) {

            if (isset($processedMonths[$month])) {

                continue;

            }


            // Check if this month is the start of a track specialization

            $trackSpecialization = $trackSpecializations->first(function ($ts) use ($month) {

                return (int)$ts->month_index === $month;

            });


            if ($trackSpecialization) {

                $duration = max(1, (int)($trackSpecialization->specialization->duration_months ?? 1));


                $schedule[$month] = [

                    'type' => 'start',

                    'duration' => $duration,

                ];


                // Mark subsequent months as skipped

                $startMonth = $month;

                $endMonth = min(12, $startMonth + $duration - 1); // Simple clamp, logic might be more complex if wrapping years, but current system seems 1-12


                // If it wraps around or is just sequential in our ordered list?

                // The month order is July -> ... -> Dec -> Jan -> ... -> June.

                // Duration usually applies to the academic year sequence.


                // Let's use the actual ordered list to find subsequent months

                $monthIndexInOrder = array_search($month, $months);


                for ($i = 1; $i < $duration; $i++) {

                    // Calculate next month in sequence

                    if ($monthIndexInOrder + $i < count($months)) {

                        $nextMonth = $months[$monthIndexInOrder + $i];

                        $schedule[$nextMonth] = ['type' => 'skip'];

                        $processedMonths[$nextMonth] = true;

                    }

                }

            } else {

                // If no track specialization starts here, assume 1 month duration (e.g. elective or empty)

                // UNLESS it was already marked as skip by a previous iteration (handled by processedMonths check above? No, loop order matters)

                // Actually, the loop order matches $months order.


                // Wait, if I'm in August and July was duration 2, I need to know August is skip.

                // My logic above sets future months as skip.

                // So if isset($schedule[$month]) it means it was set as skip.


                if (!isset($schedule[$month])) {

                    $schedule[$month] = [

                        'type' => 'start',

                        'duration' => 1,

                    ];

                }

            }


            $processedMonths[$month] = true;

        }


        return [

            'months' => $months,

            'requests' => $requests,

            'user' => $user,

            'schedule' => $schedule,

        ];

    }

}


