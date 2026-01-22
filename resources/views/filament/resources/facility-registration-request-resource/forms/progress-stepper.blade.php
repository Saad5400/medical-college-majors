@php
    use App\Enums\Month;
    use App\Models\FacilityRegistrationRequest;

    $user = auth()->user();

    // Only show for students
    if (!$user->hasRole('student')) {
        return;
    }

    // Ensure user has a track
    if (!$user->track_id) {
        return;
    }

    $user->loadMissing(['track.trackSpecializations.specialization']);

    $track = $user->track;
    $trackSpecializations = $track->trackSpecializations ?? collect();
    $electiveMonths = $track->elective_months ?? [];

    // Get all months in order (starting from July)
    $months = Month::orderFrom(7);

    // Get existing requests
    $requests = FacilityRegistrationRequest::where('user_id', $user->id)
        ->get()
        ->keyBy('month_index');

    // Calculate which months are available based on track schedule
    $schedule = [];
    $processedMonths = [];

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
                'specialization' => $trackSpecialization->specialization,
            ];

            // Mark subsequent months as part of this rotation
            $monthIndexInOrder = array_search($month, $months);

            for ($i = 1; $i < $duration; $i++) {
                if ($monthIndexInOrder + $i < count($months)) {
                    $nextMonth = $months[$monthIndexInOrder + $i];
                    $schedule[$nextMonth] = ['type' => 'skip'];
                    $processedMonths[$nextMonth] = true;
                }
            }
        } elseif (in_array($month, $electiveMonths)) {
            $schedule[$month] = [
                'type' => 'start',
                'duration' => 1,
                'is_elective' => true,
            ];
        }

        $processedMonths[$month] = true;
    }

    // Find current month and next available month
    $currentMonth = null;
    $nextAvailableMonth = null;

    foreach ($months as $month) {
        if (!isset($schedule[$month]) || $schedule[$month]['type'] !== 'start') {
            continue;
        }

        if (isset($requests[$month])) {
            $currentMonth = $month;
        } elseif ($nextAvailableMonth === null) {
            $nextAvailableMonth = $month;
            break;
        }
    }
@endphp

<div class="ps-wrapper">
    {{-- Custom CSS to ensure visibility without requiring a build step, supporting both Light and Dark modes --}}
    <style>
        .ps-wrapper {
            margin-bottom: 1.5rem;
        }

        .ps-container {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 1.5rem;
            background-color: #ffffff;
            border-radius: 0.75rem;
            border: 1px solid #e5e7eb;
            overflow-x: auto;
        }

        .ps-step {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.5rem;
            min-width: 80px;
            flex-shrink: 0;
            cursor: default;
        }

        .ps-step-clickable {
            cursor: pointer;
            transition: transform 0.2s;
        }

        .ps-step-clickable:hover {
            transform: scale(1.05);
        }

        .ps-circle {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 0.875rem;
            border: 2px solid #e5e7eb;
            background-color: #f9fafb;
            color: #9ca3af;
            transition: all 0.2s;
        }

        .ps-step-completed .ps-circle {
            background-color: #dcfce7;
            border-color: #86efac;
            color: #15803d;
        }

        .ps-step-current .ps-circle {
            background-color: #dbeafe;
            border-color: #60a5fa;
            color: #1e40af;
            box-shadow: 0 0 0 4px rgba(96, 165, 250, 0.1);
        }

        .ps-step-next .ps-circle {
            background-color: #fef3c7;
            border-color: #fbbf24;
            color: #92400e;
        }

        .ps-step-clickable:hover .ps-circle {
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }

        .ps-label {
            font-size: 0.75rem;
            font-weight: 500;
            color: #6b7280;
            text-align: center;
            max-width: 100px;
            word-wrap: break-word;
        }

        .ps-step-completed .ps-label {
            color: #15803d;
        }

        .ps-step-current .ps-label {
            color: #1e40af;
            font-weight: 600;
        }

        .ps-step-next .ps-label {
            color: #92400e;
        }

        .ps-connector {
            height: 2px;
            flex: 1;
            min-width: 24px;
            background-color: #e5e7eb;
            margin: 0 -0.5rem;
            margin-bottom: 2.5rem;
        }

        .ps-connector-completed {
            background-color: #86efac;
        }

        .ps-icon {
            width: 20px;
            height: 20px;
        }

        /* Dark Mode Overrides */
        .dark .ps-container {
            background-color: rgba(255, 255, 255, 0.05);
            border-color: rgba(255, 255, 255, 0.1);
        }

        .dark .ps-circle {
            background-color: rgba(255, 255, 255, 0.05);
            border-color: rgba(255, 255, 255, 0.1);
            color: #9ca3af;
        }

        .dark .ps-step-completed .ps-circle {
            background-color: rgba(21, 128, 61, 0.2);
            border-color: rgba(134, 239, 172, 0.3);
            color: #4ade80;
        }

        .dark .ps-step-current .ps-circle {
            background-color: rgba(30, 64, 175, 0.2);
            border-color: rgba(96, 165, 250, 0.4);
            color: #60a5fa;
            box-shadow: 0 0 0 4px rgba(96, 165, 250, 0.1);
        }

        .dark .ps-step-next .ps-circle {
            background-color: rgba(146, 64, 14, 0.2);
            border-color: rgba(251, 191, 36, 0.4);
            color: #fbbf24;
        }

        .dark .ps-label {
            color: #9ca3af;
        }

        .dark .ps-step-completed .ps-label {
            color: #4ade80;
        }

        .dark .ps-step-current .ps-label {
            color: #60a5fa;
        }

        .dark .ps-step-next .ps-label {
            color: #fbbf24;
        }

        .dark .ps-connector {
            background-color: rgba(255, 255, 255, 0.1);
        }

        .dark .ps-connector-completed {
            background-color: rgba(134, 239, 172, 0.3);
        }

        /* Responsive adjustments */
        @media (max-width: 640px) {
            .ps-container {
                padding: 1rem;
                gap: 0.25rem;
            }

            .ps-step {
                min-width: 60px;
            }

            .ps-circle {
                width: 40px;
                height: 40px;
                font-size: 0.75rem;
            }

            .ps-label {
                font-size: 0.625rem;
                max-width: 70px;
            }

            .ps-connector {
                min-width: 16px;
                margin-bottom: 2rem;
            }
        }
    </style>

    <div class="ps-container">
        @php
            $displaySteps = [];
            foreach ($months as $month) {
                if (!isset($schedule[$month]) || $schedule[$month]['type'] !== 'start') {
                    continue;
                }

                $displaySteps[] = [
                    'month' => $month,
                    'schedule' => $schedule[$month],
                    'request' => $requests[$month] ?? null,
                ];
            }
        @endphp

        @foreach ($displaySteps as $index => $step)
            @php
                $month = $step['month'];
                $scheduleItem = $step['schedule'];
                $request = $step['request'];

                $isCompleted = $request !== null;
                $isCurrent = $currentMonth === $month;
                $isNext = $nextAvailableMonth === $month;
                $isClickable = $isCompleted || $isNext;

                $url = null;
                if ($isCompleted) {
                    $url = route('filament.admin.resources.facility-registration-requests.edit', ['record' => $request->id]);
                } elseif ($isNext) {
                    $url = route('filament.admin.resources.facility-registration-requests.create');
                }

                $monthLabel = Month::labelFor($month);
                $specializationName = $scheduleItem['is_elective'] ?? false
                    ? 'Elective'
                    : ($scheduleItem['specialization']->name ?? '');
            @endphp

            @if ($index > 0)
                <div class="ps-connector {{ $displaySteps[$index - 1]['request'] !== null ? 'ps-connector-completed' : '' }}"></div>
            @endif

            <div
                class="ps-step {{ $isClickable ? 'ps-step-clickable' : '' }} {{ $isCompleted ? 'ps-step-completed' : '' }} {{ $isCurrent ? 'ps-step-current' : '' }} {{ $isNext ? 'ps-step-next' : '' }}"
                @if($isClickable && $url) onclick="window.location.href='{{ $url }}'" @endif
            >
                <div class="ps-circle">
                    @if ($isCompleted)
                        <svg class="ps-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                    @else
                        {{ $month }}
                    @endif
                </div>
                <div class="ps-label">
                    {{ $monthLabel }}
                    @if ($specializationName)
                        <br><span style="font-size: 0.625rem; opacity: 0.8;">{{ $specializationName }}</span>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
</div>
