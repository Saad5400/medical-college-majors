<x-filament-panels::page>
    <div dir="ltr" style="overflow-x: auto; padding: 16px 12px;">
        <div style="min-width: 100%; border: 1px solid #e5e7eb; border-radius: 16px; background-color: #ffffff; box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04);">
            <table style="width: 100%; min-width: 100%; border-collapse: separate; border-spacing: 0; text-align: center; font-size: 14px;">
                <thead>
                    <tr style="background-color: #f8fafc; color: #374151;">
                        <th style="position: sticky; left: 0; z-index: 10; border-bottom: 1px solid #e5e7eb; background-color: #f8fafc; padding: 18px 24px; text-align: center; font-weight: 600;">
                            الشهر
                        </th>
                        @foreach ($tracks as $track)
                            <th style="border-bottom: 1px solid #e5e7eb; padding: 18px 24px; text-align: center; font-weight: 600;">
                                {{ $track['name'] }}
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach ($months as $month)
                        <tr style="background-color: #ffffff;">
                            <th style="position: sticky; left: 0; z-index: 10; border-bottom: 1px solid #e5e7eb; background-color: #f8fafc; padding: 18px 24px; text-align: center; font-weight: 600; color: #374151;">
                                {{ \App\Enums\Month::labelFor($month) }}
                            </th>
                            @foreach ($tracks as $track)
                                @php
                                    $cell = $cells[$month][$track['id']] ?? null;
                                    $cellStyle = 'border-bottom: 1px solid #e5e7eb; padding: 20px 24px; text-align: center; vertical-align: middle; font-weight: 600; color: #374151;';

                                    if ($cell && isset($cell['color'])) {
                                        $cellStyle .= ' background-color: '.$cell['color'].'; color: '.($cell['text_color'] ?? '#111827').';';
                                    }

                                    if ($cell && isset($cell['dir'])) {
                                        $cellStyle .= ' direction: '.$cell['dir'].'; unicode-bidi: isolate;';
                                    }
                                @endphp
                                @if ($cell && ($cell['is_placeholder'] ?? false))
                                    @continue
                                @endif
                                <td
                                    @if ($cell && ($cell['rowspan'] ?? 1) > 1) rowspan="{{ $cell['rowspan'] }}" @endif
                                    @if ($cell && isset($cell['dir'])) dir="{{ $cell['dir'] }}" @endif
                                    @if ($cell && isset($cell['lang'])) lang="{{ $cell['lang'] }}" @endif
                                    style="{{ $cellStyle }}"
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
