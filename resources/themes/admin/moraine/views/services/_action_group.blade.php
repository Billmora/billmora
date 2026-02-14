<div class="grid gap-4 bg-white p-8 border-2 border-billmora-2 rounded-2xl h-fit">
    <h3 class="text-lg font-semibold text-slate-600 border-b-2 border-billmora-2 pb-4 mb-2">Service Actions</h3>
    @if(in_array($service->status, ['pending', 'terminated']))
        <x-admin::modal.trigger modal="serviceCreateModal" class="w-full flex gap-2 items-center justify-center bg-green-600 hover:bg-green-700 px-3 py-2.5 text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer font-medium">
            <x-lucide-badge-plus class="w-5 h-5" />
            Create
        </x-admin::modal.trigger>
    @endif
    @if($service->status === 'active')
        <x-admin::modal.trigger modal="serviceSuspendModal" class="w-full flex gap-2 items-center justify-center bg-amber-500 hover:bg-amber-600 px-3 py-2.5 text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer font-medium">
            <x-lucide-pause-circle class="w-5 h-5" />
            Suspend
        </x-admin::modal.trigger>
    @endif
    @if($service->status === 'suspended')
        <x-admin::modal.trigger modal="serviceUnsuspendModal" class="w-full flex gap-2 items-center justify-center bg-green-600 hover:bg-green-700 px-3 py-2.5 text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer font-medium">
            <x-lucide-play-circle class="w-5 h-5" />
            Unsuspend
        </x-admin::modal.trigger>
    @endif
    @if(in_array($service->status, ['active', 'suspended']))
        <x-admin::modal.trigger modal="serviceTerminateModal" class="w-full flex gap-2 items-center justify-center bg-red-600 hover:bg-red-700 px-3 py-2.5 text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer font-medium">
            <x-lucide-trash-2 class="w-5 h-5" />
            Terminate
        </x-admin::modal.trigger>
    @endif
    @if(in_array($service->status, ['active', 'suspended']))
        <x-admin::modal.trigger modal="serviceRenewModal" class="w-full flex gap-2 items-center justify-center bg-violet-600 hover:bg-violet-700 px-3 py-2.5 text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer font-medium">
            <x-lucide-refresh-ccw class="w-5 h-5" />
            Force Renew
        </x-admin::modal.trigger>
    @endif
    @if ($service->status === 'active')
        <x-admin::modal.trigger modal="serviceScaleModal" class="w-full flex gap-2 items-center justify-center bg-blue-500 hover:bg-blue-600 px-3 py-2.5 text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer font-medium">
            <x-lucide-scaling class="w-5 h-5" />
            Scale
        </x-admin::modal.trigger>
    @endif
</div>
<x-admin::modal.content
    modal="serviceCreateModal"
    variant="danger"
    size="xl"
    position="centered"
    title="{{ __('common.confirm_modal_title')}}"
    description="{{ __('common.confirm_modal_description', ['item' => 'Create']) }}"
>
    <form action="{{ route('admin.services.create', ['service' => $service->id]) }}" method="POST">
        @csrf
        <div class="flex justify-end gap-2 mt-4">
            <x-admin::modal.trigger 
                type="button" 
                variant="close" 
                class="bg-billmora-1 border-2 border-billmora-primary hover:bg-billmora-primary-hover px-3 py-2 text-billmora-primary hover:text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer"
            >
                {{ __('common.cancel') }}
            </x-admin::modal.trigger>
            <button type="submit" class="bg-red-500 border-2 border-red-500 hover:bg-red-600 px-3 py-2 text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">
                {{ __('common.submit') }}
            </button>
        </div>
    </form>
</x-admin::modal.content>
<x-admin::modal.content
    modal="serviceSuspendModal"
    variant="danger"
    size="xl"
    position="centered"
    title="{{ __('common.confirm_modal_title')}}"
    description="{{ __('common.confirm_modal_description', ['item' => 'Suspend']) }}"
>
    <form action="{{ route('admin.services.suspend', ['service' => $service->id]) }}" method="POST">
        @csrf
        <div class="flex justify-end gap-2 mt-4">
            <x-admin::modal.trigger 
                type="button" 
                variant="close" 
                class="bg-billmora-1 border-2 border-billmora-primary hover:bg-billmora-primary-hover px-3 py-2 text-billmora-primary hover:text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer"
            >
                {{ __('common.cancel') }}
            </x-admin::modal.trigger>
            <button type="submit" class="bg-red-500 border-2 border-red-500 hover:bg-red-600 px-3 py-2 text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">
                {{ __('common.submit') }}
            </button>
        </div>
    </form>
</x-admin::modal.content>
<x-admin::modal.content
    modal="serviceUnsuspendModal"
    variant="danger"
    size="xl"
    position="centered"
    title="{{ __('common.confirm_modal_title')}}"
    description="{{ __('common.confirm_modal_description', ['item' => 'Unsuspend']) }}"
>
    <form action="{{ route('admin.services.unsuspend', ['service' => $service->id]) }}" method="POST">
        @csrf
        <div class="flex justify-end gap-2 mt-4">
            <x-admin::modal.trigger 
                type="button" 
                variant="close" 
                class="bg-billmora-1 border-2 border-billmora-primary hover:bg-billmora-primary-hover px-3 py-2 text-billmora-primary hover:text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer"
            >
                {{ __('common.cancel') }}
            </x-admin::modal.trigger>
            <button type="submit" class="bg-red-500 border-2 border-red-500 hover:bg-red-600 px-3 py-2 text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">
                {{ __('common.submit') }}
            </button>
        </div>
    </form>
</x-admin::modal.content>
<x-admin::modal.content
    modal="serviceTerminateModal"
    variant="danger"
    size="xl"
    position="centered"
    title="{{ __('common.confirm_modal_title')}}"
    description="{{ __('common.confirm_modal_description', ['item' => 'Terminate']) }}"
>
    <form action="{{ route('admin.services.terminate', ['service' => $service->id]) }}" method="POST">
        @csrf
        <div class="flex justify-end gap-2 mt-4">
            <x-admin::modal.trigger 
                type="button" 
                variant="close" 
                class="bg-billmora-1 border-2 border-billmora-primary hover:bg-billmora-primary-hover px-3 py-2 text-billmora-primary hover:text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer"
            >
                {{ __('common.cancel') }}
            </x-admin::modal.trigger>
            <button type="submit" class="bg-red-500 border-2 border-red-500 hover:bg-red-600 px-3 py-2 text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">
                {{ __('common.submit') }}
            </button>
        </div>
    </form>
</x-admin::modal.content>
<x-admin::modal.content
    modal="serviceRenewModal"
    variant="danger"
    size="xl"
    position="centered"
    title="{{ __('common.confirm_modal_title')}}"
    description="{{ __('common.confirm_modal_description', ['item' => 'Force Renew']) }}"
>
    <form action="{{ route('admin.services.renew', ['service' => $service->id]) }}#" method="POST">
        @csrf
        <div class="flex justify-end gap-2 mt-4">
            <x-admin::modal.trigger 
                type="button" 
                variant="close" 
                class="bg-billmora-1 border-2 border-billmora-primary hover:bg-billmora-primary-hover px-3 py-2 text-billmora-primary hover:text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer"
            >
                {{ __('common.cancel') }}
            </x-admin::modal.trigger>
            <button type="submit" class="bg-red-500 border-2 border-red-500 hover:bg-red-600 px-3 py-2 text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">
                {{ __('common.submit') }}
            </button>
        </div>
    </form>
</x-admin::modal.content>
<x-admin::modal.content
    modal="serviceScaleModal"
    variant="danger"
    size="xl"
    position="centered"
    title="{{ __('common.confirm_modal_title')}}"
    description="{{ __('common.confirm_modal_description', ['item' => 'Scale']) }}"
>
    <form action="{{ route('admin.services.scale', ['service' => $service->id]) }}" method="POST">
        @csrf
        <div class="flex justify-end gap-2 mt-4">
            <x-admin::modal.trigger 
                type="button" 
                variant="close" 
                class="bg-billmora-1 border-2 border-billmora-primary hover:bg-billmora-primary-hover px-3 py-2 text-billmora-primary hover:text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer"
            >
                {{ __('common.cancel') }}
            </x-admin::modal.trigger>
            <button type="submit" class="bg-red-500 border-2 border-red-500 hover:bg-red-600 px-3 py-2 text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">
                {{ __('common.submit') }}
            </button>
        </div>
    </form>
</x-admin::modal.content>