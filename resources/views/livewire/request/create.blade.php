<div>
    <form wire:submit="upsertRequest" class="flex flex-col gap-8">
        {{ $this->form }}

        <x-filament::button type="submit">
            Save
        </x-filament::button>
    </form>

    <x-filament-actions::modals/>
</div>
