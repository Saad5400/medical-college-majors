<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            Database backup
        </x-slot>

        <div class="flex flex-col gap-4">
            <x-filament::button
                wire:click="download"
                wire:loading.attr="disabled"
                wire:target="download"
                icon="heroicon-o-arrow-down-tray"
            >
                Download backup
            </x-filament::button>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
