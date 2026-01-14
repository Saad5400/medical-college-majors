<?php

namespace App\Filament\Resources\MajorResource\Pages;

use App\Filament\Exports\UserExporter;
use App\Filament\Resources\MajorResource;
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

                    $users = User::query()
                        ->orderBy('gpa', 'desc')
                        ->get();

                    /** @var User $user */
                    foreach ($users as $user) {
                        if ($user->registrationRequests()->count() === 0) {
                            continue;
                        }

                        $registrationRequest = $user->registrationRequests()->latest()->first();
                        $majors = $registrationRequest->majors()->orderByPivot('sort')->get();

                        foreach ($majors as $major) {
                            if ($major->users()->count() < $major->max_users) {
                                $user->major()->associate($major);
                                $user->save();

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
