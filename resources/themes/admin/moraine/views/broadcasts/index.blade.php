@extends('admin::layouts.app')

@section('title', 'Manage Broadcasts')

@section('body')
<div class="flex flex-col gap-4">
    <div class="flex flex-col md:flex-row gap-4 justify-between items-center">
        <div class="w-full md:w-100">
            <form action="{{ route('admin.broadcasts') }}" method="GET" class="relative inline-block max-w-150 w-full group">
                <div class="absolute top-1/2 -translate-y-1/2 left-2.5 pointer-events-none">
                    <x-lucide-search class="w-5 h-auto text-slate-500 group-focus-within:text-billmora-primary-500" />
                </div>
                <input type="text" name="searchBroadcast" id="searchBroadcast" placeholder="{{ __('admin/common.search') }}" value="{{ request('searchBroadcast') }}" class="w-full px-6 py-3 pl-10 bg-white text-slate-700 placeholder:text-slate-500 border-2 border-billmora-2 rounded-xl group-focus-within:outline-2 outline-billmora-primary-500">
                <div class="absolute top-1/2 -translate-y-1/2 right-1.5">
                    <button type="submit" class="bg-billmora-primary-500 hover:bg-billmora-primary-600 px-3 py-1.5 text-white rounded-lg transition duration-300 cursor-pointer">{{ __('common.submit') }}</button>
                </div>
            </form>
        </div>
        @can('broadcasts.create')
            <a href="{{ route('admin.broadcasts.create') }}" class="flex gap-1 items-center bg-billmora-primary-500 hover:bg-billmora-primary-600 px-3 py-2 ml-auto text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">
                <x-lucide-plus class="w-auto h-5" />
                {{ __('common.create') }}
            </a>
        @endcan
    </div>
    <div class="overflow-x-auto">
        <div class="min-w-full inline-block align-middle">
            <div class="border-2 border-billmora-2 rounded-2xl overflow-hidden">
                <table class="min-w-full divide-y divide-billmora-2">
                    <thead class="bg-billmora-2">
                        <tr>
                            <th scope="col" class="px-6 py-4 text-start text-xs font-semibold text-slate-500 uppercase">#</th>
                            <th scope="col" class="px-6 py-4 text-start text-xs font-semibold text-slate-500 uppercase">{{ __('admin/broadcasts.subject_label') }}</th>
                            <th scope="col" class="px-6 py-4 text-start text-xs font-semibold text-slate-500 uppercase">{{ __('admin/broadcasts.recipient_group_label') }}</th>
                            <th scope="col" class="px-6 py-4 text-start text-xs font-semibold text-slate-500 uppercase">{{ __('common.schedule_at') }}</th>
                            <th scope="col" class="px-6 py-4 text-start text-xs font-semibold text-slate-500 uppercase">{{ __('common.created_at') }}</th>
                            <th scope="col" class="px-6 py-4 text-end text-xs font-semibold text-slate-500 uppercase">{{ __('common.action') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y-2 divide-billmora-2 bg-white">
                        @foreach ($broadcasts as $broadcast)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-800">{{ $loop->iteration }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-800">{{ $broadcast->subject }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-800">{{ $broadcast->recipient_group }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-800">{{ $broadcast->schedule_at?->format(Billmora::getGeneral('company_date_format')) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-800">{{ $broadcast->created_at->format(Billmora::getGeneral('company_date_format')) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-end text-sm font-medium space-x-2">
                                @can('broadcasts.update')
                                    <a href="{{ route('admin.broadcasts.edit', ['broadcast' => $broadcast->id]) }}" class="inline-flex items-center text-sm font-semibold text-billmora-primary-500 hover:text-billmora-primary-hover">
                                        {{ __('common.edit') }}
                                    </a>
                                @endcan
                                @can('broadcasts.delete')
                                    <x-admin::modal.trigger modal="deleteModal-{{ $broadcast->id }}" variant="open" class="inline-flex items-center text-sm font-semibold text-red-400 hover:text-red-500 cursor-pointer">{{ __('common.delete') }}</x-admin::modal.trigger>
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
        {{ $broadcasts->links('admin::layouts.partials.pagination') }}
    </div>
    @can('broadcasts.delete')
        @foreach ($broadcasts as $broadcast)
            <x-admin::modal.content
                modal="deleteModal-{{ $broadcast->id }}"
                variant="danger"
                size="xl"
                position="centered"
                title="{{ __('common.delete_modal_title') }}"
                description="{{ __('common.delete_modal_description', ['item' => $broadcast->subject]) }}">
                <form action="{{ route('admin.broadcasts.destroy', ['broadcast' => $broadcast->id]) }}" method="POST">
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
