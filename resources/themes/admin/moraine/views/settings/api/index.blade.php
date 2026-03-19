@extends('admin::layouts.app')

@section('title', 'API Settings')

@section('body')
<div class="flex flex-col gap-5">
    @if (session('api_token'))
        <div class="bg-emerald-50 border-2 border-emerald-200 rounded-2xl p-6">
            <p class="text-sm font-medium text-emerald-700 mb-2">{{ __('admin/settings/api.token_generated') }}</p>
            <div class="flex items-center gap-2">
                <code class="flex-1 bg-white text-sm text-slate-800 px-3 py-2 rounded-lg border border-emerald-200 break-all select-all">{{ session('api_token') }}</code>
            </div>
        </div>
    @endif
    <div class="flex flex-col gap-4">
        <div class="flex flex-col md:flex-row gap-4 justify-between items-center">
            <div class="w-full md:w-100">
                <form action="{{ route('admin.settings.api') }}" method="GET" class="relative inline-block max-w-150 w-full group">
                    <div class="absolute top-1/2 -translate-y-1/2 left-2.5 pointer-events-none">
                        <x-lucide-search class="w-5 h-auto text-slate-500 group-focus-within:text-billmora-primary-500" />
                    </div>
                    <input type="text" name="searchToken" id="searchToken" placeholder="{{ __('admin/common.search') }}" value="{{ request('searchToken') }}" class="w-full px-6 py-3 pl-10 bg-white text-slate-700 placeholder:text-slate-500 border-2 border-billmora-2 rounded-xl group-focus-within:outline-2 outline-billmora-primary-500">
                    <div class="absolute top-1/2 -translate-y-1/2 right-1.5">
                        <button type="submit" class="bg-billmora-primary-500 hover:bg-billmora-primary-600 px-3 py-1.5 text-white rounded-lg transition duration-300 cursor-pointer">{{ __('common.submit') }}</button>
                    </div>
                </form>
            </div>
            @can('settings.api.create')
                <a href="{{ route('admin.settings.api.create') }}" class="flex gap-1 items-center bg-billmora-primary-500 hover:bg-billmora-primary-600 px-3 py-2 ml-auto text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">
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
                                <th scope="col" class="px-6 py-4 text-start text-xs font-semibold text-slate-500 uppercase">{{ __('common.name') }}</th>
                                <th scope="col" class="px-6 py-4 text-start text-xs font-semibold text-slate-500 uppercase">{{ __('admin/settings/api.permissions_label') }}</th>
                                <th scope="col" class="px-6 py-4 text-start text-xs font-semibold text-slate-500 uppercase">{{ __('admin/settings/api.rate_limit_label') }}</th>
                                <th scope="col" class="px-6 py-4 text-start text-xs font-semibold text-slate-500 uppercase">{{ __('admin/settings/api.whitelist_ips_label') }}</th>
                                <th scope="col" class="px-6 py-4 text-start text-xs font-semibold text-slate-500 uppercase">{{ __('admin/settings/api.expires_at_label') }}</th>
                                <th scope="col" class="px-6 py-4 text-start text-xs font-semibold text-slate-500 uppercase">{{ __('admin/settings/api.last_used_label') }}</th>
                                <th scope="col" class="px-6 py-4 text-end text-xs font-semibold text-slate-500 uppercase">{{ __('common.action') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y-2 divide-billmora-2 bg-white">
                            @forelse ($tokens as $token)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-800">{{ $loop->iteration }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-800 font-medium">{{ str_replace('billmora-api:', '', $token->name) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-800">
                                    @if (in_array('*', $token->abilities ?? []))
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-emerald-100 text-emerald-800">{{ __('admin/settings/api.all_permissions') }}</span>
                                    @else
                                        {{ count($token->abilities ?? []) }}
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-800">{{ $token->rate_limit ?? 60 }}/min</td>
                                <td class="px-6 py-4 text-sm text-slate-800">
                                    @if ($token->whitelist_ips)
                                        <span class="text-xs text-slate-800">{{ Str::limit($token->whitelist_ips, 30) }}</span>
                                    @else
                                        <span class="text-slate-800">{{ __('admin/settings/api.all_ips') }}</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-800">
                                    @if ($token->expires_at)
                                        {{ $token->expires_at->format(Billmora::getGeneral('company_date_format')) }}
                                    @else
                                        <span class="text-slate-800">{{ __('admin/settings/api.never') }}</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-800">
                                    {{ $token->last_used_at ? $token->last_used_at->format(Billmora::getGeneral('company_date_format') . ' H:i:s') : __('admin/settings/api.never') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-end text-sm font-medium space-x-2">
                                    @can('settings.api.update')
                                        <x-admin::modal.trigger modal="regenerateModal-{{ $token->id }}" variant="open" class="inline-flex items-center text-sm font-semibold text-amber-500 hover:text-amber-600 cursor-pointer">{{ __('admin/settings/api.regenerate') }}</x-admin::modal.trigger>
                                    @endcan
                                    @can('settings.api.delete')
                                        <x-admin::modal.trigger modal="deleteModal-{{ $token->id }}" variant="open" class="inline-flex items-center text-sm font-semibold text-red-400 hover:text-red-500 cursor-pointer">{{ __('common.delete') }}</x-admin::modal.trigger>
                                    @endcan
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="px-6 py-8 text-center text-sm text-slate-400">{{ __('admin/settings/api.no_tokens') }}</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div>
            {{ $tokens->links('admin::layouts.partials.pagination') }}
        </div>
        @foreach ($tokens as $token)
            @can('settings.api.update')
            <x-admin::modal.content
                modal="regenerateModal-{{ $token->id }}"
                variant="warning"
                size="xl"
                position="centered"
                title="{{ __('admin/settings/api.regenerate_title') }}"
                description="{{ __('admin/settings/api.regenerate_description', ['name' => str_replace('billmora-api:', '', $token->name)]) }}">
                <form action="{{ route('admin.settings.api.regenerate', ['token' => $token->id]) }}" method="POST">
                    @csrf
                    <div class="flex justify-end gap-2 mt-4">
                        <x-admin::modal.trigger type="button" variant="close" class="bg-billmora-1 border-2 border-billmora-primary-500 hover:bg-billmora-primary-600 px-3 py-2 text-billmora-primary-500 hover:text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">{{ __('common.cancel') }}</x-admin::modal.trigger>
                        <button type="submit" class="bg-amber-500 border-2 border-amber-500 hover:bg-amber-600 px-3 py-2 text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">{{ __('admin/settings/api.regenerate') }}</button>
                    </div>
                </form>
            </x-admin::modal.content>
            @endcan
            @can('settings.api.delete')
            <x-admin::modal.content
                modal="deleteModal-{{ $token->id }}"
                variant="danger"
                size="xl"
                position="centered"
                title="{{ __('common.delete_modal_title') }}"
                description="{{ __('common.delete_modal_description', ['item' => str_replace('billmora-api:', '', $token->name)]) }}">
                <form action="{{ route('admin.settings.api.destroy', ['token' => $token->id]) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <div class="flex justify-end gap-2 mt-4">
                        <x-admin::modal.trigger type="button" variant="close" class="bg-billmora-1 border-2 border-billmora-primary-500 hover:bg-billmora-primary-600 px-3 py-2 text-billmora-primary-500 hover:text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">{{ __('common.cancel') }}</x-admin::modal.trigger>
                        <button type="submit" class="bg-red-500 border-2 border-red-500 hover:bg-red-600 px-3 py-2 text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">{{ __('common.delete') }}</button>
                    </div>
                </form>
            </x-admin::modal.content>
            @endcan
        @endforeach
    </div>
</div>
@endsection
