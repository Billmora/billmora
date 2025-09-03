@extends('admin::layouts.app')

@section('title', 'Broadcast Mail')

@section('body')
<div class="flex flex-col gap-5">
    @if (session('success'))
        <x-admin::alert variant="success" title="{{ session('success') }}" />
    @endif
    <x-admin::tabs 
        :tabs="[
            [
                'route' => route('admin.settings.mail.mailer'),
                'icon' => 'lucide-send',
                'label' => __('admin/settings/mail.tabs.mailer'),
            ],
            [
                'route' => route('admin.settings.mail.template'),
                'icon' => 'lucide-mailbox',
                'label' => __('admin/settings/mail.tabs.template'),
            ],
            [
                'route' => route('admin.settings.mail.broadcast'),
                'icon' => 'lucide-megaphone',
                'label' => __('admin/settings/mail.tabs.broadcast'),
            ],
        ]" 
        active="{{ request()->fullUrl() }}" />
    <div class="flex flex-col gap-4">
        <a href="{{ route('admin.settings.mail.broadcast.create') }}" class="flex gap-1 items-center bg-billmora-primary hover:bg-billmora-primary-hover px-3 py-2 ml-auto text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">
            <x-lucide-plus class="w-auto h-5" />
            {{ __('admin/common.create') }}
        </a>
        <div class="-m-1.5 overflow-x-auto">
            <div class="p-1.5 min-w-full inline-block align-middle">
                <div class="border-2 border-billmora-2 rounded-2xl overflow-hidden">
                    <table class="min-w-full divide-y divide-billmora-2">
                        <thead class="bg-billmora-2">
                            <tr>
                                <th scope="col" class="px-6 py-4 text-start text-xs font-semibold text-slate-500 uppercase">#</th>
                                <th scope="col" class="px-6 py-4 text-start text-xs font-semibold text-slate-500 uppercase">Subject</th>
                                <th scope="col" class="px-6 py-4 text-start text-xs font-semibold text-slate-500 uppercase">Recipient Group</th>
                                <th scope="col" class="px-6 py-4 text-start text-xs font-semibold text-slate-500 uppercase">Schedule At</th>
                                <th scope="col" class="px-6 py-4 text-start text-xs font-semibold text-slate-500 uppercase">Created At</th>
                                <th scope="col" class="px-6 py-4 text-end text-xs font-semibold text-slate-500 uppercase">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y-2 divide-billmora-2 bg-white">
                            @foreach ($broadcasts as $broadcast)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-800">{{ $loop->iteration }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-800">{{ $broadcast->subject }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-800">{{ $broadcast->recipient_group }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-800">{{ $broadcast->schedule_at ?? 'N/A' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-800">{{ $broadcast->created_at }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-end text-sm font-medium space-x-2">
                                    <a href="{{ route('admin.settings.mail.broadcast.edit', ['id' => $broadcast->id]) }}" class="inline-flex items-center text-sm font-semibold text-billmora-primary hover:text-billmora-primary-hover">Edit</a>
                                    <x-admin::modal.trigger modal="deleteModal-{{ $broadcast->id }}" variant="open" class="inline-flex items-center text-sm font-semibold text-red-400 hover:text-red-500 cursor-pointer">{{ __('admin/common.delete') }}</x-admin::modal.trigger>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        @foreach ($broadcasts as $broadcast)
        <x-admin::modal.content
            modal="deleteModal-{{ $broadcast->id }}"
            variant="danger"
            size="xl"
            position="centered"
            title="{{ __('admin/common.delete_modal_title', ['item' => $broadcast->subject]) }}"
            description="{{ __('admin/common.delete_modal_desc', ['item' => $broadcast->subject]) }}">
            <form action="{{ route('admin.settings.mail.broadcast.destroy', ['id' => $broadcast->id]) }}" method="POST">
                @csrf
                @method('DELETE')
                <div class="flex justify-end gap-2 mt-4">
                    <x-admin::modal.trigger type="button" variant="close" class="bg-billmora-1 border-2 border-billmora-primary hover:bg-billmora-primary-hover px-3 py-2 text-billmora-primary hover:text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">{{ __('admin/common.cancel') }}</x-admin::modal.trigger>
                    <button type="submit" class="bg-red-500 border-2 border-red-500 hover:bg-red-600 px-3 py-2 text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">{{ __('admin/common.delete') }}</button>
                </div>
            </form>
        </x-admin::modal.content>
        @endforeach
    </div>
</div>
@endsection
