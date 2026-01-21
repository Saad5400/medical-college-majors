<?php

namespace App\Filament\Resources\TrackResource\Pages;

use App\Enums\Month;
use App\Filament\Resources\TrackResource;
use App\Models\Specialization;
use App\Models\Track;
use Filament\Resources\Pages\Page;

class TrackSchedule extends Page
{
    protected static string $resource = TrackResource::class;

    protected string $view = 'filament.resources.track-resource.pages.track-schedule';

    protected static ?string $title = 'عرض مسارات التدريب';

    /**
     * @var array<int, int>
     */
    public array $months = [];

    /**
     * @var array<int, array{id: int, name: string}>
     */
    public array $tracks = [];

    /**
     * @var array<int, array<int, array<string, mixed>|null>>
     */
    public array $cells = [];

    /**
     * @param  array<string, mixed>  $parameters
     */
    public static function canAccess(array $parameters = []): bool
    {
        return auth()->user()?->can('viewAny', Track::class) ?? false;
    }

    public function mount(): void
    {
        $this->buildSchedule();
    }

    private function buildSchedule(): void
    {
        $tracks = Track::query()
            ->with(['trackSpecializations.specialization'])
            ->orderBy('name')
            ->get();

        $this->tracks = $tracks->map(fn (Track $track): array => [
            'id' => $track->id,
            'name' => $track->name,
        ])->all();

        $this->months = Month::orderFrom();
        $this->cells = [];

        foreach ($tracks as $track) {
            $electiveMonths = $this->normalizeMonths($track->elective_months);
            $electiveLookup = array_fill_keys($electiveMonths, true);
            $trackCells = [];

            foreach ($track->trackSpecializations as $trackSpecialization) {
                $specialization = $trackSpecialization->specialization;

                if (! $specialization) {
                    continue;
                }

                $startMonth = (int) $trackSpecialization->month_index;
                $durationMonths = $this->normalizeDuration($specialization->duration_months);
                $endMonth = min(12, $startMonth + $durationMonths - 1);
                $segmentStart = null;

                for ($month = $startMonth; $month <= $endMonth; $month++) {
                    if (isset($electiveLookup[$month])) {
                        if ($segmentStart !== null) {
                            $this->addSpecializationSegment($trackCells, $segmentStart, $month - 1, $specialization);
                            $segmentStart = null;
                        }

                        continue;
                    }

                    if ($segmentStart === null) {
                        $segmentStart = $month;
                    }
                }

                if ($segmentStart !== null) {
                    $this->addSpecializationSegment($trackCells, $segmentStart, $endMonth, $specialization);
                }
            }

            foreach ($electiveMonths as $month) {
                if (isset($trackCells[$month])) {
                    continue;
                }

                $trackCells[$month] = [
                    'label' => 'اختياري',
                    'color' => '#cc66ff',
                    'text_color' => $this->getReadableTextColor('#cc66ff'),
                    'rowspan' => 1,
                    'is_placeholder' => false,
                ];
            }

            for ($month = 1; $month <= 12; $month++) {
                $this->cells[$month][$track->id] = $trackCells[$month] ?? null;
            }
        }
    }

    /**
     * @param  array<int, int|string>|null  $months
     * @return array<int, int>
     */
    private function normalizeMonths(?array $months): array
    {
        $months = is_array($months) ? $months : [];
        $normalized = [];

        foreach ($months as $month) {
            $month = (int) $month;

            if ($month < 1 || $month > 12) {
                continue;
            }

            $normalized[$month] = $month;
        }

        $months = array_values($normalized);
        sort($months);

        return $months;
    }

    private function normalizeDuration(?int $durationMonths): int
    {
        return max(1, (int) $durationMonths);
    }

    /**
     * @param  array<int, array<string, mixed>>  $trackCells
     */
    private function addSpecializationSegment(
        array &$trackCells,
        int $startMonth,
        int $endMonth,
        Specialization $specialization
    ): void {
        if ($startMonth > $endMonth || isset($trackCells[$startMonth])) {
            return;
        }

        $rowspan = $endMonth - $startMonth + 1;
        $color = $specialization->color ?? '#94a3b8';

        $trackCells[$startMonth] = [
            'label' => $specialization->name,
            'color' => $color,
            'text_color' => $this->getReadableTextColor($color),
            'rowspan' => $rowspan,
            'is_placeholder' => false,
        ];

        for ($month = $startMonth + 1; $month <= $endMonth; $month++) {
            $trackCells[$month] = [
                'is_placeholder' => true,
            ];
        }
    }

    private function getReadableTextColor(?string $color): string
    {
        if (! is_string($color)) {
            return '#111827';
        }

        if (! preg_match('/^#([a-fA-F0-9]{6})$/', $color, $matches)) {
            return '#111827';
        }

        $hex = $matches[1];
        $red = hexdec(substr($hex, 0, 2));
        $green = hexdec(substr($hex, 2, 2));
        $blue = hexdec(substr($hex, 4, 2));
        $luminance = ($red * 0.299) + ($green * 0.587) + ($blue * 0.114);

        return $luminance < 140 ? '#ffffff' : '#111827';
    }
}
