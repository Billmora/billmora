<x-filament-panels::page>
    <x-filament::grid default="1" sm="2" class="gap-4">
        <x-filament::card>
            <div class="flex items-center justify-between gap-2">
                <div class="flex items-center gap-2">
                    <x-filament::icon icon="tabler-heart-filled" class="w-16 h-full text-danger-600"/>
                    <div class="flex flex-col">
                        <span class="text-lg font-semibold">Support Billmora :3</span>
                        <span class="text-sm">Whether big or small, every contribution helps us improve and continue providing value to everyone.</span>
                    </div>
                </div>
                <x-filament::button size="lg" color="danger" href="https://billmora.com/donate" tag="a" target="_blank">
                    <div class="flex items-center gap-2">
                        <x-filament::icon icon="tabler-cash" class="w-5 h-5"/>
                        <span>Donate</span>
                    </div>
                </x-filament::button>
            </div>
        </x-filament::card>
        <x-filament::card>
            <div class="flex items-center justify-between gap-2">
                <div class="flex items-center gap-2">
                    <x-filament::icon icon="tabler-book-2" class="w-16 h-full text-primary-600"/>
                    <div class="flex flex-col">
                        <span class="text-lg font-semibold">Documentation</span>
                        <span class="text-sm">Make sure to read the documentation to understand and utilize the available features.</span>
                    </div>
                </div>
                <x-filament::button size="lg" color="primary" href="https://docs.billmora.com/" tag="a" target="_blank">
                    <div class="flex items-center gap-2">
                        <x-filament::icon icon="tabler-book" class="w-5 h-5"/>
                        <span>Read</span>
                    </div>
                </x-filament::button>
            </div>
        </x-filament::card>
    </x-filament::grid>
</x-filament-panels::page>
