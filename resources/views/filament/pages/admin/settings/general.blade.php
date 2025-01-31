<x-filament-panels::page>
    <form wire:submit.prevent="save" class="space-y-6">
        {{ $this->form }}

        <div class="text-right">
            <x-filament::button type="submit" color="primary">
                <div class="flex items-center gap-1">
                    <x-filament::icon icon="tabler-device-floppy" class="w-5 h-5" wire:loading.remove/>
                    <x-filament::icon icon="tabler-loader" class="animate-spin w-5 h-5" wire:loading/>
                    <span>Save</span>
                </div>
            </x-filament::button>
        </div>
    </form>
</x-filament-panels::page>
