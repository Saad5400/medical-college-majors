<?php

namespace App\Filament\Widgets;

use App\Models\RegistrationRequest;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class StudentRegistrationStatsWidget extends StatsOverviewWidget
{
    public static function canView(): bool
    {
        return auth()->user()?->hasRole('admin') ?? false;
    }

    protected function getStats(): array
    {
        $studentsQuery = User::query()->whereDoesntHave('roles', function ($query) {
            $query->where('name', 'admin');
        });

        $studentCount = $studentsQuery->count();

        // Use explicit COUNT(DISTINCT) for PostgreSQL compatibility
        $studentsWithRequests = RegistrationRequest::query()
            ->whereHas('user', function ($query) {
                $query->whereDoesntHave('roles', function ($roleQuery) {
                    $roleQuery->where('name', 'admin');
                });
            })
            ->count(DB::raw('DISTINCT user_id'));

        $studentsWithoutRequests = max($studentCount - $studentsWithRequests, 0);

        return [
            Stat::make('Students', $studentCount),
            Stat::make('Registration requests', $studentsWithRequests),
            Stat::make('Students without requests', $studentsWithoutRequests),
        ];
    }
}
