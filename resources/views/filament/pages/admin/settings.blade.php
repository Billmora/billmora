<x-filament-panels::page>
    <x-filament::grid default="1" sm="2" md="3" class="gap-4">
            <x-filament::card >
                <div class="flex flex-col gap-4">
                    <div class="flex items-center gap-2">
                        <x-filament::icon icon="tabler-nut" class="w-16 h-full text-primary-600"/>
                        <div class="flex flex-col">
                            <span class="text-lg font-semibold">General</span>
                            <span class="text-sm">Configure a general settings.</span>
                        </div>
                    </div>
                    <x-filament::button href="{{ request()->url() }}/general" size="lg" color="primary" tag="a" class="w-full">
                        <div class="flex items-center gap-1">
                            <span>Configure</span>
                        </div>
                    </x-filament::button>
                </div>
            </x-filament::card>
            <x-filament::card >
                <div class="flex flex-col gap-4">
                    <div class="flex items-center gap-2">
                        <x-filament::icon icon="tabler-mail" class="w-16 h-full text-primary-600"/>
                        <div class="flex flex-col">
                            <span class="text-lg font-semibold">Mail</span>
                            <span class="text-sm">Configure a mail settings.</span>
                        </div>
                    </div>
                    <x-filament::button href="{{ request()->url() }}/mail" size="lg" color="primary" tag="a" class="w-full">
                        <div class="flex items-center gap-1">
                            <span>Configure</span>
                        </div>
                    </x-filament::button>
                </div>
            </x-filament::card>
    </x-filament::grid>
</x-filament-panels::page>
