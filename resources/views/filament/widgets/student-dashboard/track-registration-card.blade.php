{{-- Track Registration Request Card - Only show if user doesn't have an assigned track --}}
@if(!$user->track_id)
    <div class="sdw-card">
        <div class="sdw-card-title">
            <x-heroicon-o-document-arrow-up class="sdw-icon" />
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
                        <x-heroicon-o-pencil class="sdw-icon" />
                        Edit Request
                    </a>
                </div>
            @endif
        @else
            <p class="sdw-empty">No registration request submitted.</p>

            @if($canCreateRegistrationRequest)
                <div class="sdw-section-actions">
                    <a href="{{ route('filament.admin.resources.registration-requests.create') }}" class="sdw-action-btn">
                        <x-heroicon-o-plus class="sdw-icon" />
                        Create Registration Request
                    </a>
                </div>
            @elseif(!$settings->track_registration_open)
                <div class="sdw-alert sdw-alert-info" style="margin-top: 0.75rem;">
                    <x-heroicon-o-information-circle class="sdw-icon" />
                    Track registration is currently closed.
                </div>
            @endif
        @endif
    </div>
@endif
