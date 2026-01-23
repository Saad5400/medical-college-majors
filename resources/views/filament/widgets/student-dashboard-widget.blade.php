<x-filament-widgets::widget>
    {{-- CSS Styles for both Light and Dark modes --}}
    <style>
        .sdw-card {
            background-color: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 0.75rem;
            padding: 1.25rem;
            margin-bottom: 1rem;
        }

        .dark .sdw-card {
            background-color: rgba(255, 255, 255, 0.05);
            border-color: rgba(255, 255, 255, 0.1);
        }

        .sdw-card-title {
            font-size: 1rem;
            font-weight: 600;
            color: #111827;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .dark .sdw-card-title {
            color: #f3f4f6;
        }

        .sdw-info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 1rem;
        }

        .sdw-info-item {
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
        }

        .sdw-info-label {
            font-size: 0.75rem;
            font-weight: 500;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .dark .sdw-info-label {
            color: #9ca3af;
        }

        .sdw-info-value {
            font-size: 1rem;
            font-weight: 600;
            color: #111827;
        }

        .dark .sdw-info-value {
            color: #f3f4f6;
        }

        .sdw-badge {
            display: inline-flex;
            align-items: center;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .sdw-badge-success {
            background-color: #dcfce7;
            color: #15803d;
        }

        .dark .sdw-badge-success {
            background-color: rgba(21, 128, 61, 0.2);
            color: #4ade80;
        }

        .sdw-badge-warning {
            background-color: #fef3c7;
            color: #92400e;
        }

        .dark .sdw-badge-warning {
            background-color: rgba(146, 64, 14, 0.2);
            color: #fbbf24;
        }

        .sdw-badge-info {
            background-color: #e0e7ff;
            color: #3730a3;
        }

        .dark .sdw-badge-info {
            background-color: rgba(55, 48, 163, 0.2);
            color: #a5b4fc;
        }

        /* Track Schedule Horizontal Table */
        .sdw-schedule-container {
            overflow-x: auto;
            margin: 0 -0.25rem;
            padding: 0.25rem;
        }

        .sdw-schedule-table {
            display: flex;
            flex-direction: row;
            min-width: max-content;
            border: 1px solid #e5e7eb;
            border-radius: 0.5rem;
            overflow: hidden;
        }

        .dark .sdw-schedule-table {
            border-color: rgba(255, 255, 255, 0.1);
        }

        .sdw-schedule-cell {
            display: flex;
            flex-direction: column;
            min-width: 80px;
            border-right: 1px solid #e5e7eb;
        }

        .sdw-schedule-cell:last-child {
            border-right: none;
        }

        .dark .sdw-schedule-cell {
            border-right-color: rgba(255, 255, 255, 0.1);
        }

        .sdw-schedule-month {
            padding: 0.5rem;
            font-size: 0.7rem;
            font-weight: 600;
            text-align: center;
            background-color: #f1f5f9;
            color: #475569;
            border-bottom: 1px solid #e5e7eb;
            white-space: nowrap;
        }

        .dark .sdw-schedule-month {
            background-color: rgba(255, 255, 255, 0.05);
            color: #94a3b8;
            border-bottom-color: rgba(255, 255, 255, 0.1);
        }

        .sdw-schedule-spec {
            padding: 0.75rem 0.5rem;
            font-size: 0.75rem;
            font-weight: 600;
            text-align: center;
            min-height: 3.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* Registration Request List */
        .sdw-request-list {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .sdw-request-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 0.75rem;
            background-color: #f8fafc;
            border-radius: 0.375rem;
            font-size: 0.875rem;
        }

        .dark .sdw-request-item {
            background-color: rgba(255, 255, 255, 0.05);
        }

        .sdw-request-num {
            font-weight: 700;
            color: #6b7280;
            min-width: 1.5rem;
        }

        .dark .sdw-request-num {
            color: #9ca3af;
        }

        .sdw-request-name {
            color: #374151;
            font-weight: 500;
        }

        .dark .sdw-request-name {
            color: #e5e7eb;
        }

        /* Assigned Facilities Table */
        .sdw-assigned-container {
            overflow-x: auto;
        }

        .sdw-assigned-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.875rem;
        }

        .sdw-assigned-table th,
        .sdw-assigned-table td {
            padding: 0.75rem 1rem;
            text-align: left;
            border-bottom: 1px solid #e5e7eb;
        }

        .dark .sdw-assigned-table th,
        .dark .sdw-assigned-table td {
            border-bottom-color: rgba(255, 255, 255, 0.1);
        }

        .sdw-assigned-table th {
            background-color: #f8fafc;
            font-weight: 600;
            color: #374151;
        }

        .dark .sdw-assigned-table th {
            background-color: rgba(255, 255, 255, 0.05);
            color: #e5e7eb;
        }

        .sdw-assigned-table td {
            color: #4b5563;
        }

        .dark .sdw-assigned-table td {
            color: #d1d5db;
        }

        .sdw-assigned-table tr:last-child td {
            border-bottom: none;
        }

        .sdw-empty {
            color: #9ca3af;
            font-style: italic;
        }

        .sdw-action-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.375rem;
            padding: 0.5rem 1rem;
            font-size: 0.875rem;
            font-weight: 500;
            color: #ffffff;
            background-color: #7c3aed;
            border-radius: 0.5rem;
            text-decoration: none;
            transition: background-color 0.2s;
        }

        .sdw-action-btn:hover {
            background-color: #6d28d9;
        }

        .sdw-action-btn-secondary {
            background-color: #6b7280;
        }

        .sdw-action-btn-secondary:hover {
            background-color: #4b5563;
        }

        .sdw-section-actions {
            display: flex;
            gap: 0.75rem;
            margin-top: 1rem;
            flex-wrap: wrap;
        }

        .sdw-divider {
            height: 1px;
            background-color: #e5e7eb;
            margin: 1rem 0;
        }

        .dark .sdw-divider {
            background-color: rgba(255, 255, 255, 0.1);
        }

        .sdw-alert {
            padding: 0.75rem 1rem;
            border-radius: 0.5rem;
            font-size: 0.875rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .sdw-alert-info {
            background-color: #e0e7ff;
            color: #3730a3;
        }

        .dark .sdw-alert-info {
            background-color: rgba(55, 48, 163, 0.2);
            color: #a5b4fc;
        }

        .sdw-alert-success {
            background-color: #dcfce7;
            color: #15803d;
        }

        .dark .sdw-alert-success {
            background-color: rgba(21, 128, 61, 0.2);
            color: #4ade80;
        }
    </style>

    <div class="space-y-4">
        {{-- Student Information Card --}}
        <div class="sdw-card">
            <div class="sdw-card-title">
                <x-heroicon-o-user class="w-5 h-5" />
                Student Information
            </div>
            <div class="sdw-info-grid">
                <div class="sdw-info-item">
                    <span class="sdw-info-label">Name</span>
                    <span class="sdw-info-value">{{ $user->name }}</span>
                </div>
                <div class="sdw-info-item">
                    <span class="sdw-info-label">Student ID</span>
                    <span class="sdw-info-value">{{ $user->student_id ?? '-' }}</span>
                </div>
                <div class="sdw-info-item">
                    <span class="sdw-info-label">GPA</span>
                    <span class="sdw-info-value">{{ $user->gpa ?? '-' }}</span>
                </div>
                @if($user->track_id)
                    <div class="sdw-info-item">
                        <span class="sdw-info-label">Assigned Track</span>
                        <span class="sdw-info-value">{{ $user->track?->name ?? '-' }}</span>
                    </div>
                @endif
            </div>
        </div>

        {{-- Track Registration Request Card --}}
        <div class="sdw-card">
            <div class="sdw-card-title">
                <x-heroicon-o-document-arrow-up class="w-5 h-5" />
                Track Registration Request
            </div>

            @if($registrationRequest)
                <div class="sdw-request-list">
                    @foreach($registrationRequest->trackRegistrationRequests as $trackRequest)
                        <div class="sdw-request-item">
                            <span class="sdw-request-num">{{ $loop->iteration }}.</span>
                            <span class="sdw-request-name">{{ $trackRequest->track?->name ?? '-' }}</span>
                        </div>
                    @endforeach
                </div>

                @if($settings->track_registration_open && !$user->hasRole('leader'))
                    <div class="sdw-section-actions">
                        <a href="{{ route('filament.admin.resources.registration-requests.edit', $registrationRequest) }}" class="sdw-action-btn sdw-action-btn-secondary">
                            <x-heroicon-o-pencil class="w-4 h-4" />
                            Edit Request
                        </a>
                    </div>
                @endif
            @else
                <p class="sdw-empty">No registration request submitted.</p>

                @if($canCreateRegistrationRequest)
                    <div class="sdw-section-actions">
                        <a href="{{ route('filament.admin.resources.registration-requests.create') }}" class="sdw-action-btn">
                            <x-heroicon-o-plus class="w-4 h-4" />
                            Create Registration Request
                        </a>
                    </div>
                @elseif(!$settings->track_registration_open)
                    <div class="sdw-alert sdw-alert-info" style="margin-top: 0.75rem;">
                        <x-heroicon-o-information-circle class="w-5 h-5" />
                        Track registration is currently closed.
                    </div>
                @endif
            @endif
        </div>

        {{-- Track Schedule (only if user has an assigned track) --}}
        @if($user->track_id && count($trackSchedule) > 0)
            <div class="sdw-card">
                <div class="sdw-card-title">
                    <x-heroicon-o-calendar-days class="w-5 h-5" />
                    Track Schedule - {{ $user->track?->name }}
                </div>
                <div class="sdw-schedule-container" dir="ltr">
                    <div class="sdw-schedule-table">
                        @foreach($trackSchedule as $item)
                            <div class="sdw-schedule-cell" style="flex: {{ $item['colspan'] }}; min-width: {{ 80 * $item['colspan'] }}px;">
                                <div class="sdw-schedule-month">
                                    @if($item['colspan'] > 1)
                                        @php
                                            $startMonth = $item['month'];
                                            $monthsOrdered = \App\Enums\Month::orderFrom(7);
                                            $startIndex = array_search($startMonth, $monthsOrdered);
                                            $endMonth = $monthsOrdered[$startIndex + $item['colspan'] - 1] ?? $startMonth;
                                        @endphp
                                        {{ \App\Enums\Month::labelFor($startMonth) }} - {{ \App\Enums\Month::labelFor($endMonth) }}
                                    @else
                                        {{ \App\Enums\Month::labelFor($item['month']) }}
                                    @endif
                                </div>
                                <div class="sdw-schedule-spec" style="background-color: {{ $item['color'] }}; color: {{ $item['text_color'] }};">
                                    {{ $item['label'] }}
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

        {{-- Facility Registration Requests (only show when facility registration is open) --}}
        @if($settings->facility_registration_open && $user->track_id)
            <div class="sdw-card">
                <div class="sdw-card-title">
                    <x-heroicon-o-building-office class="w-5 h-5" />
                    Facility Registration Requests
                </div>

                @if($facilityRequests->count() > 0)
                    <div class="sdw-alert sdw-alert-success" style="margin-bottom: 1rem;">
                        <x-heroicon-o-check-circle class="w-5 h-5" />
                        You have {{ $facilityRequests->count() }} facility registration request(s).
                    </div>
                @else
                    <p class="sdw-empty">No facility registration requests submitted yet.</p>
                @endif

                @if($canCreateFacilityRequest)
                    <div class="sdw-section-actions">
                        <a href="{{ route('filament.admin.resources.facility-registration-requests.create') }}" class="sdw-action-btn">
                            <x-heroicon-o-plus class="w-4 h-4" />
                            Create Facility Request
                        </a>
                        <a href="{{ route('filament.admin.resources.facility-registration-requests.index') }}" class="sdw-action-btn sdw-action-btn-secondary">
                            <x-heroicon-o-eye class="w-4 h-4" />
                            View All Requests
                        </a>
                    </div>
                @endif
            </div>
        @endif

        {{-- Assigned Facilities (only if user has any assignments) --}}
        @if(count($assignedFacilities) > 0)
            <div class="sdw-card">
                <div class="sdw-card-title">
                    <x-heroicon-o-check-badge class="w-5 h-5" />
                    Assigned Facilities
                </div>
                <div class="sdw-assigned-container">
                    <table class="sdw-assigned-table">
                        <thead>
                            <tr>
                                <th>Month</th>
                                <th>Facility</th>
                                <th>Specialization</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($months as $month)
                                @if(isset($assignedFacilities[$month]))
                                    <tr>
                                        <td style="font-weight: 600;">{{ \App\Enums\Month::labelFor($month) }}</td>
                                        <td>
                                            <span class="sdw-badge sdw-badge-success">
                                                {{ $assignedFacilities[$month]['facility'] ?? '-' }}
                                            </span>
                                        </td>
                                        <td>{{ $assignedFacilities[$month]['specialization'] ?? '-' }}</td>
                                    </tr>
                                @endif
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>
</x-filament-widgets::widget>
