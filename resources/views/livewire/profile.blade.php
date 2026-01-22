<div>
    <form wire:submit="update" class="flex flex-col gap-8">
        {{ $this->form }}

        <div class="flex flex-row w-full gap-6">
            <x-filament::button
                class="w-full"
                type="submit"
            >
                Save
            </x-filament::button>
            <x-filament::button
                class="w-full !bg-transparent !text-red-500 hover:!bg-red-500 hover:!text-white !transition-colors"
                wire:click="logout"
            >
                Log Out
            </x-filament::button>
        </div>
    </form>

    <x-filament-actions::modals/>
</div>
