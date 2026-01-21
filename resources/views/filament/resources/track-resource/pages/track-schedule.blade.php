<x-filament-panels::page>
    <div class="overflow-x-auto">
        <div class="min-w-full rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-950">
            <table class="min-w-full border-separate border-spacing-0 text-xs sm:text-sm">
                <thead>
                    <tr class="bg-gray-50 text-gray-700 dark:bg-gray-900 dark:text-gray-200">
                        <th class="sticky left-0 z-10 border-b border-gray-200 bg-gray-50 px-3 py-2 text-right font-semibold dark:border-gray-800 dark:bg-gray-900">
                            الشهر
                        </th>
                        @foreach ($tracks as $track)
                            <th class="border-b border-gray-200 px-3 py-2 text-center font-semibold dark:border-gray-800">
                                {{ $track['name'] }}
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach ($months as $month)
                        <tr class="odd:bg-white even:bg-gray-50/60 dark:odd:bg-gray-950 dark:even:bg-gray-900/40">
                            <th class="sticky left-0 z-10 border-b border-gray-200 bg-gray-50 px-3 py-2 text-right font-semibold text-gray-700 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-200">
                                الشهر {{ $month }}
                            </th>
                            @foreach ($tracks as $track)
                                @php
                                    $cell = $cells[$month][$track['id']] ?? null;
                                @endphp
                                @if ($cell && ($cell['is_placeholder'] ?? false))
                                    @continue
                                @endif
                                <td
                                    @if ($cell && ($cell['rowspan'] ?? 1) > 1) rowspan="{{ $cell['rowspan'] }}" @endif
                                    class="border-b border-gray-200 px-3 py-3 text-center align-middle font-semibold text-gray-700 dark:border-gray-800 dark:text-gray-200"
                                    @if ($cell && isset($cell['color']))
                                        style="background-color: {{ $cell['color'] }}; color: {{ $cell['text_color'] ?? '#111827' }};"
                                    @endif
                                >
                                    {{ $cell['label'] ?? '' }}
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</x-filament-panels::page>
