{{-- Student Information Card --}}
<div class="sdw-card">
    <div class="sdw-card-title">
        <x-heroicon-o-user class="sdw-icon" />
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
