{{-- Track Schedule (only if user has an assigned track) --}}
@if($user->track_id && count($trackSchedule) > 0)
    <div class="sdw-card">
        <div class="sdw-card-title">
            <x-heroicon-o-calendar-days class="sdw-icon" />
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
