@extends('admin::layouts.app')

@section('title', 'Punishments Settings - Punishments')

@section('body')
<div class="flex flex-col gap-4">
    @can('settings.punishments.create')
        <a href="{{ route('admin.settings.punishments.create') }}" class="flex gap-1 items-center bg-billmora-primary-500 hover:bg-billmora-primary-600 px-3 py-2 ml-auto text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">
            <x-lucide-plus class="w-auto h-5" />
            {{ __('common.create') }}
        </a>
    @endcan
    <div class="overflow-x-auto">
        <div class="min-w-full inline-block align-middle">
            <div class="border-2 border-billmora-2 rounded-2xl overflow-hidden">
                <table class="min-w-full divide-y divide-billmora-2">
                    <thead class="bg-billmora-2">
                        <tr>
                            <th scope="col" class="px-6 py-4 text-start text-xs font-semibold text-slate-500 uppercase">{{ __('admin/settings/punishment.punishment_user_label') }}</th>
                            <th scope="col" class="px-6 py-4 text-start text-xs font-semibold text-slate-500 uppercase">{{ __('admin/settings/punishment.punishment_status_label') }}</th>
                            <th scope="col" class="px-6 py-4 text-start text-xs font-semibold text-slate-500 uppercase">{{ __('admin/settings/punishment.punishment_reason_label') }}</th>
                            <th scope="col" class="px-6 py-4 text-start text-xs font-semibold text-slate-500 uppercase">{{ __('admin/settings/punishment.punishment_expires_at_label') }}</th>
                            <th scope="col" class="px-6 py-4 text-start text-xs font-semibold text-slate-500 uppercase">{{ __('common.created_at') }}</th>
                            <th scope="col" class="px-6 py-4 text-end text-xs font-semibold text-slate-500 uppercase">{{ __('common.action') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y-2 divide-billmora-2 bg-white">
                        @foreach ($punishments as $punishment)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-800">{{ $punishment->user->email }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-800">{{ $punishment->status }}</td>
                            <td class="px-6 py-4 text-sm text-slate-800">
                                <p class="max-w-[200px] truncate" title="{{ $punishment->reason }}">{{ $punishment->reason }}</p>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-800">{{ $punishment->expires_at ? $punishment->expires_at->format(Billmora::getGeneral('company_date_format')) : '-' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-800">{{ $punishment->created_at->format(Billmora::getGeneral('company_date_format')) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-end text-sm font-medium space-x-2">
                                @can('settings.punishments.delete')
                                    <x-admin::modal.trigger modal="deleteModal-{{ $punishment->id }}" variant="open" class="inline-flex items-center text-sm font-semibold text-red-400 hover:text-red-500 cursor-pointer">{{ __('common.delete') }}</x-admin::modal.trigger>
                                @endcan
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div>
        {{ $punishments->links('admin::layouts.partials.pagination') }}
    </div>
    @can('settings.punishments.delete')
        @foreach ($punishments as $punishment)
        <x-admin::modal.content
            modal="deleteModal-{{ $punishment->id }}"
            variant="danger"
            size="xl"
            position="centered"
            title="{{ __('common.delete_modal_title') }}"
            description="{{ __('common.delete_modal_description', ['item' => $punishment->user->email]) }}">
            <form action="{{ route('admin.settings.punishments.destroy', ['punishment' => $punishment->id]) }}" method="POST">
                @csrf
                @method('DELETE')
                <div class="flex justify-end gap-2 mt-4">
                    <x-admin::modal.trigger type="button" variant="close" class="bg-billmora-1 border-2 border-billmora-primary-500 hover:bg-billmora-primary-600 px-3 py-2 text-billmora-primary-500 hover:text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">{{ __('common.cancel') }}</x-admin::modal.trigger>
                    <button type="submit" class="bg-red-500 border-2 border-red-500 hover:bg-red-600 px-3 py-2 text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">{{ __('common.delete') }}</button>
                </div>
            </form>
        </x-admin::modal.content>
        @endforeach
    @endcan
</div>
@endsection
