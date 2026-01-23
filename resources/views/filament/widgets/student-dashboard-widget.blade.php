<x-filament-widgets::widget>
    @include('filament.widgets.student-dashboard.styles')

    <div class="space-y-4">
        @include('filament.widgets.student-dashboard.student-info-card')

        @include('filament.widgets.student-dashboard.track-registration-card')

        @include('filament.widgets.student-dashboard.track-schedule-card')

        @include('filament.widgets.student-dashboard.facility-registration-card')

        {{-- My Registration Requests Widget --}}
        @livewire(\App\Filament\Resources\FacilityRegistrationRequestResource\Widgets\StudentFacilityRequestsWidget::class)

        @include('filament.widgets.student-dashboard.assigned-facilities-card')
    </div>
</x-filament-widgets::widget>
