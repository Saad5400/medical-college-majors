{{-- Assigned Facilities (only if user has any assignments) --}}
@if(count($assignedFacilities) > 0)
    <div class="sdw-card">
        <div class="sdw-card-title">
            <x-heroicon-o-check-badge class="sdw-icon" />
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
