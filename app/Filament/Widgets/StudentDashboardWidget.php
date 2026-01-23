<?php

namespace App\Filament\Widgets;

use App\Enums\Month;
use App\Settings\RegistrationSettings;
use Filament\Widgets\Widget;

class StudentDashboardWidget extends Widget
{
    protected string $view = 'filament.widgets.student-dashboard-widget';

    protected int|string|array $columnSpan = 'full';

    public static function canView(): bool
    {
        return auth()->user()?->hasRole('student') ?? false;
    }

    protected function getViewData(): array
    {
        $user = auth()->user();
        $user->loadMissing([
            'registrationRequests.trackRegistrationRequests.track',
            'track.trackSpecializations.specialization',
            'facilityRegistrationRequests.assignedFacility',
            'facilityRegistrationRequests.assignedSpecialization',
            'facilityRegistrationRequests.wishes.facility',
            'facilityRegistrationRequests.wishes.specialization',
        ]);

        $settings = app(RegistrationSettings::class);

        // Get registration request
        $registrationRequest = $user->registrationRequests->first();

        // Get facility registration requests keyed by month
        $facilityRequests = $user->facilityRegistrationRequests->keyBy('month_index');

        // Build track specialization schedule for horizontal display
        $trackSchedule = $this->buildTrackSchedule($user);

        // Build assigned facilities per month
        $assignedFacilities = $this->buildAssignedFacilities($user, $facilityRequests);

        // Get months ordered from July
        $months = Month::orderFrom(7);

        return [
            'user' => $user,
            'settings' => $settings,
            'registrationRequest' => $registrationRequest,
            'facilityRequests' => $facilityRequests,
            'trackSchedule' => $trackSchedule,
            'assignedFacilities' => $assignedFacilities,
            'months' => $months,
            'canCreateRegistrationRequest' => $this->canCreateRegistrationRequest($user, $settings, $registrationRequest),
            'canCreateFacilityRequest' => $this->canCreateFacilityRequest($user, $settings),
        ];
    }

    /**
     * @return array<int, array{label: string, color: string, text_color: string, colspan: int, is_elective: bool}>
     */
    private function buildTrackSchedule($user): array
    {
        $track = $user->track;

        if (! $track) {
            return [];
        }

        $trackSpecializations = $track->trackSpecializations;
        $electiveMonths = $this->normalizeMonths($track->elective_months ?? []);
        $electiveLookup = array_fill_keys($electiveMonths, true);

        $months = Month::orderFrom(7);
        $schedule = [];
        $processedMonths = [];

        foreach ($months as $month) {
            if (isset($processedMonths[$month])) {
                continue;
            }

            // Check if elective month
            if (isset($electiveLookup[$month])) {
                $schedule[] = [
                    'month' => $month,
                    'label' => 'Elective',
                    'color' => '#cc66ff',
                    'text_color' => $this->getReadableTextColor('#cc66ff'),
                    'colspan' => 1,
                    'is_elective' => true,
                ];
                $processedMonths[$month] = true;

                continue;
            }

            // Find track specialization for this month
            $trackSpecialization = $trackSpecializations->first(function ($ts) use ($month) {
                return (int) $ts->month_index === $month;
            });

            if ($trackSpecialization && $trackSpecialization->specialization) {
                $specialization = $trackSpecialization->specialization;
                $duration = max(1, (int) $specialization->duration_months);
                $color = $specialization->color ?? '#94a3b8';

                // Calculate actual colspan by counting months in our ordered sequence
                $monthIndexInOrder = array_search($month, $months);
                $colspan = 1;

                for ($i = 1; $i < $duration; $i++) {
                    if ($monthIndexInOrder + $i < count($months)) {
                        $nextMonth = $months[$monthIndexInOrder + $i];
                        // Skip if next month is elective
                        if (isset($electiveLookup[$nextMonth])) {
                            break;
                        }
                        $colspan++;
                        $processedMonths[$nextMonth] = true;
                    }
                }

                $schedule[] = [
                    'month' => $month,
                    'label' => $specialization->name,
                    'color' => $color,
                    'text_color' => $this->getReadableTextColor($color),
                    'colspan' => $colspan,
                    'is_elective' => false,
                ];
                $processedMonths[$month] = true;
            } else {
                // Empty month (no specialization assigned)
                $schedule[] = [
                    'month' => $month,
                    'label' => '-',
                    'color' => '#f1f5f9',
                    'text_color' => '#64748b',
                    'colspan' => 1,
                    'is_elective' => false,
                ];
                $processedMonths[$month] = true;
            }
        }

        return $schedule;
    }

    /**
     * @return array<int, array{facility: string|null, specialization: string|null}>
     */
    private function buildAssignedFacilities($user, $facilityRequests): array
    {
        $assigned = [];
        $months = Month::orderFrom(7);

        foreach ($months as $month) {
            $request = $facilityRequests->get($month);

            if ($request && $request->assigned_facility_id) {
                $assigned[$month] = [
                    'facility' => $request->assignedFacility?->name,
                    'specialization' => $request->assignedSpecialization?->name,
                ];
            }
        }

        return $assigned;
    }

    private function canCreateRegistrationRequest($user, RegistrationSettings $settings, $registrationRequest): bool
    {
        // Leaders cannot create registration requests
        if ($user->hasRole('leader')) {
            return false;
        }

        // Check if track registration is open
        if (! $settings->track_registration_open) {
            return false;
        }

        // Only allow if no request exists yet
        return $registrationRequest === null;
    }

    private function canCreateFacilityRequest($user, RegistrationSettings $settings): bool
    {
        // Check if facility registration is open
        if (! $settings->facility_registration_open) {
            return false;
        }

        // User must have an assigned track
        return $user->track_id !== null;
    }

    /**
     * @param  array<int, int|string>|null  $months
     * @return array<int, int>
     */
    private function normalizeMonths(?array $months): array
    {
        $months = is_array($months) ? $months : [];
        $normalized = [];

        foreach ($months as $month) {
            $month = (int) $month;

            if ($month < 1 || $month > 12) {
                continue;
            }

            $normalized[$month] = $month;
        }

        return array_values($normalized);
    }

    private function getReadableTextColor(?string $color): string
    {
        if (! is_string($color)) {
            return '#111827';
        }

        if (! preg_match('/^#([a-fA-F0-9]{6})$/', $color, $matches)) {
            return '#111827';
        }

        $hex = $matches[1];
        $red = hexdec(substr($hex, 0, 2));
        $green = hexdec(substr($hex, 2, 2));
        $blue = hexdec(substr($hex, 4, 2));
        $luminance = ($red * 0.299) + ($green * 0.587) + ($blue * 0.114);

        return $luminance < 140 ? '#ffffff' : '#111827';
    }
}
