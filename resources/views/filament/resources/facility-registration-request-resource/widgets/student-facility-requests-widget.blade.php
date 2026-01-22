<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            My Registration Requests
        </x-slot>

        {{-- Custom CSS to ensure visibility without requiring a build step, supporting both Light and Dark modes --}}
        <style>
            .sfr-container {
                overflow-x: auto;
                margin: -0.75rem -1rem;
                border-radius: 0.5rem;
            }

            .sfr-table {
                width: 100%;
                border-collapse: collapse;
                text-align: left;
                font-size: 0.875rem;
            }

            /* Light Mode Styles */
            .sfr-thead-tr {
                background-color: #e0e7ff;
                color: #1e293b;
            }

            /* Indigo-100 / Slate-800 */
            .sfr-th {
                padding: 0.875rem 1.5rem;
                font-weight: 600;
                border-bottom: 2px solid #c7d2fe;
                text-align: left;
            }

            .sfr-tbody {
                background-color: #ffffff;
            }

            .sfr-tbody-tr {
                border-bottom: 1px solid #e5e7eb;
                transition: background-color 0.2s;
            }

            .sfr-tbody-tr:hover {
                background-color: #f8fafc;
            }

            .sfr-tbody-tr:last-child {
                border-bottom: none;
            }

            .sfr-td-month {
                background-color: #f1f5f9;
                color: #0f172a;
                font-weight: 600;
                padding: 1rem 1.5rem;
                border-right: 1px solid #e2e8f0;
                white-space: nowrap;
                width: 1%;
            }

            .sfr-td-content {
                padding: 1rem 1.5rem;
                color: #4b5563;
                vertical-align: middle;
            }

            .sfr-badge {
                display: inline-flex;
                align-items: center;
                padding: 0.25rem 0.625rem;
                border-radius: 0.375rem;
                font-size: 0.75rem;
                font-weight: 600;
                line-height: 1;
                gap: 0.5rem;
            }

            .sfr-badge-assigned {
                background-color: #dcfce7;
                color: #15803d;
                border: 1px solid #bbf7d0;
            }

            /* Green-100 / Green-700 */

            .sfr-meta {
                font-size: 0.75rem;
                color: #64748b;
                font-weight: 400;
                font-style: italic;
            }

            .sfr-list {
                display: flex;
                flex-wrap: wrap;
                gap: 1rem;
                row-gap: 0.25rem;
            }

            .sfr-list-item {
                display: flex;
                align-items: center;
                gap: 0.375rem;
            }

            .sfr-num {
                font-size: 0.75rem;
                font-weight: 600;
                color: #94a3b8;
            }

            .sfr-val {
                font-size: 0.875rem;
                color: #374151;
            }

            .sfr-val-active {
                font-weight: 500;
                color: #111827;
            }

            .sfr-empty {
                color: #9ca3af;
                font-style: italic;
            }

            /* Dark Mode Overrides */
            .dark .sfr-thead-tr {
                background-color: #312e81;
                color: #e0e7ff;
            }

            /* Indigo-900 / Indigo-100 */
            .dark .sfr-th {
                border-bottom-color: #4338ca;
            }

            .dark .sfr-tbody {
                background-color: transparent;
            }

            .dark .sfr-tbody-tr {
                border-bottom-color: rgba(255, 255, 255, 0.1);
            }

            .dark .sfr-tbody-tr:hover {
                background-color: rgba(255, 255, 255, 0.05);
            }

            .dark .sfr-td-month {
                background-color: rgba(255, 255, 255, 0.05);
                color: #f1f5f9;
                border-right-color: rgba(255, 255, 255, 0.1);
            }

            .dark .sfr-td-content {
                color: #9ca3af;
            }

            .dark .sfr-badge-assigned {
                background-color: rgba(21, 128, 61, 0.2);
                color: #4ade80;
                border-color: rgba(74, 222, 128, 0.2);
            }

            .dark .sfr-meta {
                color: #94a3b8;
            }

            .dark .sfr-num {
                color: #64748b;
            }

            .dark .sfr-val {
                color: #d1d5db;
            }

            .dark .sfr-val-active {
                color: #ffffff;
            }
        </style>

        <div class="sfr-container">
            <table class="sfr-table">
                <thead>
                <tr class="sfr-thead-tr">
                    <th class="sfr-th">Month</th>
                    <th class="sfr-th">Choices / Assignment</th>
                </tr>
                </thead>
                <tbody class="sfr-tbody">
                @foreach ($months as $month)
                    @php
                        $scheduleItem = $schedule[$month] ?? ['type' => 'start', 'duration' => 1];
                        $request = $requests[$month] ?? null;
                    @endphp

                    <tr class="sfr-tbody-tr">
                        <td class="sfr-td-month">
                            {{ \App\Enums\Month::labelFor($month) }}
                        </td>

                        @if($scheduleItem['type'] === 'start')
                            <td class="sfr-td-content" rowspan="{{ $scheduleItem['duration'] }}">
                                @if ($request)
                                    <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                                        @if ($request->assigned_facility_id)
                                            <div>
                                                    <span class="sfr-badge sfr-badge-assigned">
                                                        {{ $request->assignedFacility->name }}
                                                        @if ($request->assignedSpecialization)
                                                            <span class="sfr-meta">
                                                                {{ $request->assignedSpecialization->name }}
                                                            </span>
                                                        @endif
                                                    </span>
                                            </div>
                                        @endif

                                        @if($request->wishes->count() > 0)
                                            <div class="sfr-list">
                                                @foreach($request->wishes->sortBy('priority') as $wish)
                                                    <div class="sfr-list-item">
                                                        <span class="sfr-num">{{ $loop->iteration }}.</span>
                                                        <span
                                                            class="sfr-val {{ $request->assigned_facility_id == $wish->facility_id ? 'sfr-val-active' : '' }}">
                                                                @if($wish->is_custom)
                                                                {{ $wish->custom_specialization_name ?? 'Custom Spec' }}
                                                                @ {{ $wish->custom_facility_name ?? 'Custom Facility' }}
                                                            @else
                                                                @php
                                                                    $specName = $wish->specialization?->name;
                                                                @endphp
                                                                {{ $specName ? $specName . ' @ ' : '' }}{{ $wish->facility->name ?? '-' }}
                                                            @endif
                                                            </span>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                @else
                                    <span class="sfr-empty">No request made</span>
                                @endif
                            </td>
                        @endif
                        {{-- If type is 'skip', we do not render the <td>, so the previous rowspan covers it --}}
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
