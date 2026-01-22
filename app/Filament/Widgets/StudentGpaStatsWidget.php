<?php

namespace App\Filament\Widgets;

use App\Models\User;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StudentGpaStatsWidget extends StatsOverviewWidget
{
    public static function canView(): bool
    {
        return auth()->user()?->hasRole('admin') ?? false;
    }

    protected function getStats(): array
    {
        // Combine all 3 queries into 1 for better performance
        $stats = User::query()
            ->whereDoesntHave('roles', function ($query) {
                $query->where('name', 'admin');
            })
            ->whereNotNull('gpa')
            ->selectRaw('MIN(gpa) as min_gpa, MAX(gpa) as max_gpa, AVG(gpa) as avg_gpa')
            ->first();

        return [
            Stat::make('Min GPA', $this->formatGpa($stats?->min_gpa)),
            Stat::make('Average GPA', $this->formatGpa($stats?->avg_gpa)),
            Stat::make('Max GPA', $this->formatGpa($stats?->max_gpa)),
        ];
    }

    protected function formatGpa(?float $value): string
    {
        if ($value === null) {
            return 'N/A';
        }

        return number_format($value, 2);
    }
}
