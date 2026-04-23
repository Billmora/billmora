@extends('admin::layouts.app')

@section('title', 'Manage Registrants')

@section('body')
    <div class="flex flex-col gap-5">
        <div class="flex flex-col md:flex-row gap-4 justify-between items-center">
            <div class="w-full md:w-100">
                <form action="{{ route('admin.registrants') }}" method="GET"
                    class="relative inline-block max-w-150 w-full group">
                    <div class="absolute top-1/2 -translate-y-1/2 left-2.5 pointer-events-none">
                        <x-lucide-search class="w-5 h-auto text-slate-500 group-focus-within:text-billmora-primary-500" />
                    </div>
                    <input type="text" name="search" id="search" placeholder="{{ __('admin/common.search') }}"
                        value="{{ request('search') }}"
                        class="w-full px-6 py-3 pl-10 bg-white text-slate-700 placeholder:text-slate-500 border-2 border-billmora-2 rounded-xl group-focus-within:outline-2 outline-billmora-primary-500">
                    <div class="absolute top-1/2 -translate-y-1/2 right-1.5">
                        <button type="submit"
                            class="bg-billmora-primary-500 hover:bg-billmora-primary-600 px-3 py-1.5 text-white rounded-lg transition duration-300 cursor-pointer">{{ __('common.submit') }}</button>
                    </div>
                </form>
            </div>
            @can('orders.create')
                <a href="{{ route('admin.orders.create') }}" class="flex gap-1 items-center bg-billmora-primary-500 hover:bg-billmora-primary-600 px-3 py-2 ml-auto text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">
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
                                <th scope="col" class="px-6 py-4 text-start text-xs font-semibold text-slate-500 uppercase">
                                    {{ __('admin/registrants.number_label') }}</th>
                                <th scope="col" class="px-6 py-4 text-start text-xs font-semibold text-slate-500 uppercase">
                                    {{ __('admin/registrants.domain_label') }}</th>
                                <th scope="col" class="px-6 py-4 text-start text-xs font-semibold text-slate-500 uppercase">
                                    {{ __('admin/registrants.user_label') }}</th>
                                <th scope="col" class="px-6 py-4 text-start text-xs font-semibold text-slate-500 uppercase">
                                    {{ __('admin/registrants.tld_label') }}</th>
                                <th scope="col" class="px-6 py-4 text-start text-xs font-semibold text-slate-500 uppercase">
                                    {{ __('admin/registrants.expires_label') }}</th>
                                <th scope="col" class="px-6 py-4 text-start text-xs font-semibold text-slate-500 uppercase">
                                    {{ __('admin/registrants.price_label') }}</th>
                                <th scope="col" class="px-6 py-4 text-start text-xs font-semibold text-slate-500 uppercase">
                                    {{ __('common.status') }}</th>
                                <th scope="col" class="px-6 py-4 text-end text-xs font-semibold text-slate-500 uppercase">
                                    {{ __('common.action') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y-2 divide-billmora-2 bg-white">
                            @forelse ($registrants as $registrant)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-800">
                                        <a href="{{ route('admin.registrants.edit', ['registrant' => $registrant->id]) }}"
                                            class="inline-flex items-center text-sm font-semibold text-billmora-primary-500 hover:text-billmora-primary-hover">{{ $registrant->registrant_number }}</a>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-800">{{ $registrant->domain }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-800">
                                        {{ $registrant->user->email }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-800">
                                        {{ $registrant->tld->tld ?? '-' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-800">
                                        {{ $registrant->expires_at?->format(Billmora::getGeneral('company_date_format')) }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-800">
                                        {{ Currency::format($registrant->price, $registrant->currency) }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-800">{{ $registrant->status }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-end text-sm font-medium space-x-2">
                                        @can('registrants.update')
                                            <a href="{{ route('admin.registrants.edit', ['registrant' => $registrant->id]) }}"
                                                class="inline-flex items-center text-sm font-semibold text-billmora-primary-500 hover:text-billmora-primary-hover">{{ __('common.edit') }}</a>
                                        @endcan
                                        @can('registrants.delete')
                                            <x-admin::modal.trigger modal="deleteModal-{{ $registrant->id }}" variant="open"
                                                class="inline-flex items-center text-sm font-semibold text-red-400 hover:text-red-500 cursor-pointer">{{ __('common.delete') }}</x-admin::modal.trigger>
                                        @endcan
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-6 py-8 text-center text-sm text-slate-400">
                                        {{ __('common.no_data') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div>
            {{ $registrants->links('admin::layouts.partials.pagination') }}
        </div>
        @can('registrants.delete')
            @foreach ($registrants as $registrant)
                <x-admin::modal.content modal="deleteModal-{{ $registrant->id }}" variant="danger" size="xl" position="centered"
                    title="{{ __('common.delete_modal_title') }}"
                    description="{{ __('common.delete_modal_description', ['item' => $registrant->domain]) }}">
                    <form action="{{ route('admin.registrants.destroy', ['registrant' => $registrant->id]) }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <div class="flex justify-end gap-2 mt-4">
                            <x-admin::modal.trigger type="button" variant="close"
                                class="bg-billmora-1 border-2 border-billmora-primary-500 hover:bg-billmora-primary-600 px-3 py-2 text-billmora-primary-500 hover:text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">{{ __('common.cancel') }}</x-admin::modal.trigger>
                            <button type="submit"
                                class="bg-red-500 border-2 border-red-500 hover:bg-red-600 px-3 py-2 text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">{{ __('common.delete') }}</button>
                        </div>
                    </form>
                </x-admin::modal.content>
            @endforeach
        @endcan
    </div>
@endsection