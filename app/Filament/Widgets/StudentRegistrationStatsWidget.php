<?php

namespace App\Filament\Widgets;

use App\Models\RegistrationRequest;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

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

        $studentsWithRequests = RegistrationRequest::query()
            ->whereHas('user', function ($query) {
                $query->whereDoesntHave('roles', function ($roleQuery) {
                    $roleQuery->where('name', 'admin');
                });
            })
            ->distinct('user_id')
            ->count('user_id');

        $studentsWithoutRequests = max($studentCount - $studentsWithRequests, 0);

        return [
            Stat::make('الطلاب', $studentCount),
            Stat::make('طلبات التسجيل', $studentsWithRequests),
            Stat::make('الطلاب الذين لم يقوموا بإنشاء طلب تسجيل', $studentsWithoutRequests),
        ];
    }
}
