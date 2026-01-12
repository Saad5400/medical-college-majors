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
        $studentsQuery = User::query()
            ->whereDoesntHave('roles', function ($query) {
                $query->where('name', 'admin');
            })
            ->whereNotNull('gpa');

        $minGpa = $studentsQuery->min('gpa');
        $maxGpa = $studentsQuery->max('gpa');
        $avgGpa = $studentsQuery->avg('gpa');

        return [
            Stat::make('Min GPA', $this->formatGpa($minGpa)),
            Stat::make('Avg GPA', $this->formatGpa($avgGpa)),
            Stat::make('Max GPA', $this->formatGpa($maxGpa)),
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
