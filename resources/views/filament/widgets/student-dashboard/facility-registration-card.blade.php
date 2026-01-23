{{-- Facility Registration Requests (only show when facility registration is open) --}}
@if($settings->facility_registration_open && $user->track_id)
    <div class="sdw-card">
        <div class="sdw-card-title">
            <x-heroicon-o-building-office class="sdw-icon" />
            Facility Registration Requests
        </div>

        @php
            // Calculate required months vs completed months
            $requiredMonthsCount = count($trackSchedule);
            $completedMonthsCount = 0;

            foreach ($trackSchedule as $item) {
                $month = $item['month'];
                if (isset($facilityRequests[$month])) {
                    $completedMonthsCount++;
                }
            }

            $allMonthsCompleted = $requiredMonthsCount > 0 && $completedMonthsCount >= $requiredMonthsCount;
            $hasAnyRequests = $facilityRequests->count() > 0;
        @endphp

        @if($hasAnyRequests)
            @if($allMonthsCompleted)
                <div class="sdw-alert sdw-alert-success" style="margin-bottom: 1rem;">
                    <x-heroicon-o-check-circle class="sdw-icon" />
                    All months completed! You've submitted facility registration requests for all {{ $requiredMonthsCount }} month(s).
                </div>
            @else
                <div class="sdw-alert sdw-alert-info" style="margin-bottom: 1rem;">
                    <x-heroicon-o-information-circle class="sdw-icon" />
                    Progress: {{ $completedMonthsCount }} of {{ $requiredMonthsCount }} month(s) completed.
                    {{ $requiredMonthsCount - $completedMonthsCount }} month(s) remaining.
                </div>
            @endif
        @else
            <p class="sdw-empty">No facility registration requests submitted yet.</p>
        @endif

        <div class="sdw-section-actions">
            @if($canCreateFacilityRequest && !$allMonthsCompleted)
                <a href="{{ route('filament.admin.resources.facility-registration-requests.create') }}" class="sdw-action-btn">
                    <x-heroicon-o-plus class="sdw-icon" />
                    Create Facility Request
                </a>
            @endif
            <a href="{{ route('filament.admin.resources.facility-registration-requests.index') }}" class="sdw-action-btn sdw-action-btn-secondary">
                <x-heroicon-o-eye class="sdw-icon" />
                View All Requests
            </a>
        </div>
    </div>
@endif
