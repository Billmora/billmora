@extends('admin::layouts.app')

@section('title', "Domain Edit - {$registrant->domain}")

@section('body')
<div class="flex flex-col-reverse lg:flex-row gap-5">
    @livewire('admin.registrants.registrant-edit', ['registrant' => $registrant])
    <div class="w-full lg:w-2/7 h-fit grid gap-5">
        <div class="grid gap-4 bg-white p-8 border-2 border-billmora-2 rounded-2xl h-fit">
            <h3 class="text-lg font-semibold text-slate-600 border-b-2 border-billmora-2 pb-4 mb-2">
                {{ __('admin/registrants.registrar_actions_label') }}
            </h3>
            @if(in_array($registrant->status, ['pending', 'terminated', 'cancelled']))
                <x-admin::modal.trigger modal="regCreateModal" type="button"
                    class="w-full flex items-center justify-center gap-2 bg-green-600 hover:bg-green-700 px-3 py-2.5 text-white rounded-lg transition-colors cursor-pointer font-medium">
                    <x-lucide-globe class="w-5 h-5" />
                    {{ __('admin/registrants.registrar_create_label') }}
                </x-admin::modal.trigger>
            @endif
            @if(in_array($registrant->status, ['pending', 'pending_transfer']))
                <x-admin::modal.trigger modal="regTransferModal" type="button"
                    class="w-full flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700 px-3 py-2.5 text-white rounded-lg transition-colors cursor-pointer font-medium">
                    <x-lucide-arrow-right-left class="w-5 h-5" />
                    {{ __('admin/registrants.registrar_transfer_label') }}
                </x-admin::modal.trigger>
            @endif
            @if($registrant->status === 'active')
                <x-admin::modal.trigger modal="regSuspendModal" type="button"
                    class="w-full flex items-center justify-center gap-2 bg-amber-500 hover:bg-amber-600 px-3 py-2.5 text-white rounded-lg transition-colors cursor-pointer font-medium">
                    <x-lucide-pause-circle class="w-5 h-5" />
                    {{ __('admin/registrants.registrar_suspend_label') }}
                </x-admin::modal.trigger>
            @endif
            @if($registrant->status === 'suspended')
                <x-admin::modal.trigger modal="regUnsuspendModal" type="button"
                    class="w-full flex items-center justify-center gap-2 bg-green-600 hover:bg-green-700 px-3 py-2.5 text-white rounded-lg transition-colors cursor-pointer font-medium">
                    <x-lucide-play-circle class="w-5 h-5" />
                    {{ __('admin/registrants.registrar_unsuspend_label') }}
                </x-admin::modal.trigger>
            @endif
            @if(in_array($registrant->status, ['active', 'expired', 'suspended', 'redemption']))
                <x-admin::modal.trigger modal="regRenewModal" type="button"
                    class="w-full flex items-center justify-center gap-2 bg-violet-600 hover:bg-violet-700 px-3 py-2.5 text-white rounded-lg transition-colors cursor-pointer font-medium">
                    <x-lucide-refresh-cw class="w-5 h-5" />
                    {{ __('admin/registrants.registrar_renew_label') }}
                </x-admin::modal.trigger>
            @endif
            @if(!in_array($registrant->status, ['transferred_away', 'pending']))
                <x-admin::modal.trigger modal="regSyncModal" type="button"
                    class="w-full flex items-center justify-center gap-2 bg-slate-600 hover:bg-slate-700 px-3 py-2.5 text-white rounded-lg transition-colors cursor-pointer font-medium">
                    <x-lucide-refresh-ccw class="w-5 h-5" />
                    {{ __('admin/registrants.registrar_sync_label') }}
                </x-admin::modal.trigger>
            @endif
        </div>

    </div>
</div>
@foreach([
    'create'    => ['route' => 'admin.registrants.registrar.create',    'color' => 'bg-green-500 border-green-500 hover:bg-green-600'],
    'transfer'  => ['route' => 'admin.registrants.registrar.transfer',  'color' => 'bg-blue-500 border-blue-500 hover:bg-blue-600'],
    'suspend'   => ['route' => 'admin.registrants.registrar.suspend',   'color' => 'bg-amber-500 border-amber-500 hover:bg-amber-600'],
    'unsuspend' => ['route' => 'admin.registrants.registrar.unsuspend', 'color' => 'bg-green-500 border-green-500 hover:bg-green-600'],
    'renew'     => ['route' => 'admin.registrants.registrar.renew',     'color' => 'bg-violet-500 border-violet-500 hover:bg-violet-600'],
    'sync'      => ['route' => 'admin.registrants.registrar.sync',      'color' => 'bg-slate-500 border-slate-500 hover:bg-slate-600'],
] as $action => $cfg)
    <x-admin::modal.content
        modal="reg{{ ucfirst($action) }}Modal"
        variant="info"
        size="xl"
        position="centered"
        title="{{ __('common.confirm_modal_title') }}"
        description="{{ __('common.confirm_modal_description', ['item' => __('admin/registrants.registrar_' . $action . '_label')]) }}"
    >
        <form action="{{ route($cfg['route'], ['registrant' => $registrant->id]) }}" method="POST">
            @csrf
            <div class="flex justify-end gap-2 mt-4">
                <x-admin::modal.trigger type="button" variant="close"
                    class="bg-billmora-1 border-2 border-billmora-primary-500 hover:bg-billmora-primary-600 px-3 py-2 text-billmora-primary-500 hover:text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">
                    {{ __('common.cancel') }}
                </x-admin::modal.trigger>
                <button type="submit"
                    class="border-2 {{ $cfg['color'] }} px-3 py-2 text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">
                    {{ __('common.submit') }}
                </button>
            </div>
        </form>
    </x-admin::modal.content>
@endforeach
@endsection