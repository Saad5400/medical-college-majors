<?php

namespace App\Filament\Resources\TrackResource\Pages;

use App\Filament\Exports\UserExporter;
use App\Filament\Resources\TrackResource;
use App\Models\RegistrationRequest;
use App\Models\Track;
use App\Models\User;
use App\Settings\RegistrationSettings;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Actions\ExportAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListTracks extends ListRecords
{
    protected static string $resource = TrackResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
            ExportAction::make()
                ->exporter(UserExporter::class)
                ->label('تصدير الطلاب')
                ->modalHeading('تصدير الطلاب')
                ->modifyQueryUsing(fn () => User::query()->whereDoesntHave('roles')->orderBy('track_id'))
                ->icon('heroicon-o-arrow-down-tray')
                ->color('info'),
            Action::make('distribute')
                ->label('توزيع الطلاب على المسارات')
                ->visible(fn () => auth()->user()->hasRole('admin') && ! app(RegistrationSettings::class)->track_registration_open)
                ->action(function () {
                    // Reset all users' track_id
                    User::query()->update(['track_id' => null]);

                    // Eager load registration requests with tracks to avoid N+1
                    $users = User::query()
                        ->with(['registrationRequests' => function ($query) {
                            $query->latest()->with(['tracks' => function ($tracksQuery) {
                                $tracksQuery->orderByPivot('sort');
                            }]);
                        }])
                        ->addSelect(['latest_request_created_at' => RegistrationRequest::select('created_at')
                            ->whereColumn('user_id', 'users.id')
                            ->latest()
                            ->limit(1),
                        ])
                        ->orderBy('gpa', 'desc')
                        ->orderBy('latest_request_created_at', 'asc')
                        ->get();

                    // Pre-load tracks with their max_users
                    $trackCapacities = Track::query()->pluck('max_users', 'id')->toArray();
                    $trackCurrentCounts = [];

                    /** @var User $user */
                    foreach ($users as $user) {
                        $registrationRequest = $user->registrationRequests->first();
                        if (! $registrationRequest) {
                            continue;
                        }

                        $tracks = $registrationRequest->tracks;

                        foreach ($tracks as $track) {
                            $currentCount = $trackCurrentCounts[$track->id] ?? 0;
                            if ($currentCount < $trackCapacities[$track->id]) {
                                $user->track_id = $track->id;
                                $user->save();
                                $trackCurrentCounts[$track->id] = $currentCount + 1;

                                break;
                            }
                        }
                    }

                    Notification::make()
                        ->title('تم توزيع الطلاب على المسارات')
                        ->success()
                        ->send();
                })
                ->icon('heroicon-o-arrows-right-left')
                ->color('primary'),
        ];
    }
}
