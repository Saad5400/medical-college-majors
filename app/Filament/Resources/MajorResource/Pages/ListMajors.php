<?php

namespace App\Filament\Resources\MajorResource\Pages;

use App\Filament\Exports\UserExporter;
use App\Filament\Resources\MajorResource;
use App\Models\Major;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Actions\ExportAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListMajors extends ListRecords
{
    protected static string $resource = MajorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
            ExportAction::make()
                ->exporter(UserExporter::class)
                ->label('تصدير الطلاب'),
            Action::make('distribute')
                ->label('توزيع الطلاب على المسارات')
                ->action(function () {
                    // Reset all users' major_id
                    User::query()->update(['major_id' => null]);

                    // Eager load registration requests with majors to avoid N+1
                    $users = User::query()
                        ->with(['registrationRequests' => function ($query) {
                            $query->latest()->with(['majors' => function ($majorsQuery) {
                                $majorsQuery->orderByPivot('sort');
                            }]);
                        }])
                        ->orderBy('gpa', 'desc')
                        ->get();

                    // Pre-load majors with their max_users
                    $majorCapacities = Major::query()->pluck('max_users', 'id')->toArray();
                    $majorCurrentCounts = [];

                    /** @var User $user */
                    foreach ($users as $user) {
                        $registrationRequest = $user->registrationRequests->first();
                        if (! $registrationRequest) {
                            continue;
                        }

                        $majors = $registrationRequest->majors;

                        foreach ($majors as $major) {
                            $currentCount = $majorCurrentCounts[$major->id] ?? 0;
                            if ($currentCount < $majorCapacities[$major->id]) {
                                $user->major_id = $major->id;
                                $user->save();
                                $majorCurrentCounts[$major->id] = $currentCount + 1;

                                break;
                            }
                        }
                    }

                    Notification::make()
                        ->title('تم توزيع الطلاب على المسارات')
                        ->success()
                        ->send();
                }),
        ];
    }
}
