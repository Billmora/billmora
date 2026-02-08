@extends('admin::layouts.app')

@section('title', "User Summary - {$user->email}")

@section('body')
<div class="flex flex-col gap-5">
    <x-admin::tabs 
        :tabs="[
            [
                'route' => route('admin.users.summary', ['id' => $user->id]),
                'icon' => 'lucide-contact',
                'label' => __('admin/users.tabs.summary'),
            ],
            [
                'route' => route('admin.users.profile', ['id' => $user->id]),
                'icon' => 'lucide-user-pen',
                'label' => __('admin/users.tabs.profile'),
            ],
            [
                'route' => route('admin.users.activity', ['id' => $user->id]),
                'icon' => 'lucide-activity',
                'label' => __('admin/users.tabs.activity'),
            ],
        ]" 
        active="{{ request()->url() }}" />
    @can('uses.update')
        @if (!$user->isEmailVerified())
            <x-admin::alert variant="warning" title="{{ __('admin/users.email_verification_alert_label') }}">
                {{ __('admin/users.email_verification_alert_helper') }}
                <form action="{{ route('admin.users.verify', ['id' => $user->id]) }}" method="POST" class="ml-auto">
                    @csrf
                    <button type="submit" class="bg-billmora-primary hover:bg-billmora-primary-hover px-3 py-2 text-white font-semibold rounded-lg transition duration-150 cursor-pointer">{{ __('admin/users.marked_as_verified') }}</button>
                </form>
            </x-admin::alert>
        @endif
    @endcan
    <div class="flex flex-col lg:flex-row gap-5">
        <div class="w-full lg:w-1/4 h-fit grid gap-6 items-center bg-white p-8 text-center border-2 border-billmora-2 rounded-2xl">
            <img src="{{ $user->avatar }}?s=128" alt="user avatar" class="rounded-full w-32 h-auto mx-auto">
            <div class="flex flex-col">
                <span class="text-xl text-slate-600 font-bold break-all">{{ $user->fullname }}</span>
                <span class="text-md text-slate-500 font-semibold break-all">{{ $user->email }}</span>
                <span class="text-sm text-slate-500 font-semibold break-all capitalize">{{ $user->status }}</span>
                <span class="text-sm text-slate-500 font-semibold break-all">
                    @if ($user->isRootAdmin())
                        Administrator
                    @elseif ($user->roles->isNotEmpty())
                        {{ $user->roles->pluck('name')->implode(', ') }}
                    @else
                        Client
                    @endif
                </span>
            </div>
            <div class="grid gap-2 bg-billmora-primary p-4 text-xs rounded-xl">
                <div class="flex gap-3 justify-between">
                    <span class="text-slate-100 font-semibold text-start break-all">{{ __('common.phone_number') }}</span>
                    <span class="text-slate-200 font-semibold text-end break-all">{{ $user->billing->phone_number }}</span>
                </div>
                <div class="flex gap-3 justify-between">
                    <span class="text-slate-100 font-semibold text-start break-all">{{ __('common.company_name') }}</span>
                    <span class="text-slate-200 font-semibold text-end break-all">{{ $user->billing->company_name }}</span>
                </div>
                <div class="flex gap-3 justify-between">
                    <span class="text-slate-100 font-semibold text-start break-all">{{ __('common.street_address_1') }}</span>
                    <span class="text-slate-200 font-semibold text-end break-all">{{ $user->billing->street_address_1 }}</span>
                </div>
                <div class="flex gap-3 justify-between">
                    <span class="text-slate-100 font-semibold text-start break-all">{{ __('common.street_address_2') }}</span>
                    <span class="text-slate-200 font-semibold text-end break-all">{{ $user->billing->street_address_2 }}</span>
                </div>
                <div class="flex gap-3 justify-between">
                    <span class="text-slate-100 font-semibold text-start break-all">{{ __('common.city') }}</span>
                    <span class="text-slate-200 font-semibold text-end break-all">{{ $user->billing->city }}</span>
                </div>
                <div class="flex gap-3 justify-between">
                    <span class="text-slate-100 font-semibold text-start break-all">{{ __('common.state') }}</span>
                    <span class="text-slate-200 font-semibold text-end break-all">{{ $user->billing->state }}</span>
                </div>
                <div class="flex gap-3 justify-between">
                    <span class="text-slate-100 font-semibold text-start break-all">{{ __('common.postcode') }}</span>
                    <span class="text-slate-200 font-semibold text-end break-all">{{ $user->billing->postcode }}</span>
                </div>
                <div class="flex gap-3 justify-between">
                    <span class="text-slate-100 font-semibold text-start break-all">{{ __('common.country') }}</span>
                    <span class="text-slate-200 font-semibold text-end break-all">{{ config('utils.countries')[$user->billing->country] ?? $user->billing->country }}</span>
                </div>
            </div>
            @if (Auth::id() !== $user->id)
                @can('users.impersonate')
                    <form action="{{ route('admin.users.impersonate', ['id' => $user->id]) }}" method="POST">
                        @csrf
                        <button type="submit" class="w-full flex gap-2 justify-center items-center bg-billmora-primary hover:bg-billmora-primary-hover px-3 py-3 text-white font-semibold rounded-lg transition-colors duration-300 cursor-pointer">
                            <x-lucide-user class="w-auto h-5" />
                            {{ __('admin/users.login_as_user') }}
                        </button>
                    </form>
                @endcan
                @can('delete', $user)
                    <x-admin::modal.trigger modal="deleteModal-{{ $user->id }}" variant="open" class="w-full flex gap-2 justify-center items-center bg-red-500 hover:bg-red-600 px-3 py-3 text-white font-semibold rounded-lg transition-colors duration-300 cursor-pointer">
                        <x-lucide-trash class="w-auto h-5" />
                        {{ __('common.delete') }}
                    </x-admin::modal.trigger>
                @endcan
            @endif
        </div>
        <div class="w-full lg:w-3/4 h-fit grid gap-5">
            <div class="grid grid-cols-none md:grid-cols-2 lg:grid-cols-3 gap-5">
                <div class="flex items-center gap-4 bg-white p-6 border-2 border-billmora-2 rounded-2xl">
                    <div class="bg-green-200 p-3 text-green-500 rounded-full">
                        <x-lucide-badge-check class="w-auto h-10" />
                    </div>
                    <div>
                        <h4 class="text-lg font-semibold text-slate-500">Active Orders</h4>
                        <span class="text-2xl font-semibold text-slate-600">0</span>
                    </div>
                </div>
                <div class="flex items-center gap-4 bg-white p-6 border-2 border-billmora-2 rounded-2xl">
                    <div class="bg-red-200 p-3 text-red-500 rounded-full">
                        <x-lucide-badge-x class="w-auto h-10" />
                    </div>
                    <div>
                        <h4 class="text-lg font-semibold text-slate-500">Canceled Orders</h4>
                        <span class="text-2xl font-semibold text-slate-600">0</span>
                    </div>
                </div>
                <div class="flex items-center gap-4 bg-white p-6 border-2 border-billmora-2 rounded-2xl">
                    <div class="bg-violet-200 p-3 text-violet-500 rounded-full">
                        <x-lucide-badge-dollar-sign class="w-auto h-10" />
                    </div>
                    <div>
                        <h4 class="text-lg font-semibold text-slate-500">Total Orders</h4>
                        <span class="text-2xl font-semibold text-slate-600">0</span>
                    </div>
                </div>
            </div>
            <div class="overflow-x-auto">
                <div class="min-w-full inline-block align-middle">
                    <div class="border-2 border-billmora-2 rounded-2xl overflow-hidden">
                        <table class="min-w-full divide-y divide-billmora-2">
                            <thead class="bg-billmora-2">
                                <tr>
                                    <th scope="col" class="px-6 py-4 text-start text-xs font-semibold text-slate-500 uppercase">ID</th>
                                    <th scope="col" class="px-6 py-4 text-start text-xs font-semibold text-slate-500 uppercase">Products</th>
                                    <th scope="col" class="px-6 py-4 text-start text-xs font-semibold text-slate-500 uppercase">Status</th>
                                    <th scope="col" class="px-6 py-4 text-start text-xs font-semibold text-slate-500 uppercase">Created At</th>
                                    <th scope="col" class="px-6 py-4 text-end text-xs font-semibold text-slate-500 uppercase">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y-2 divide-billmora-2 bg-white">
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-800">1</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-800">Minecraft</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-800">Active</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-800">2025-09-12 00:17:45</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-end text-sm font-medium space-x-2">
                                        <a href="#" class="inline-flex items-center text-sm font-semibold text-billmora-primary hover:text-billmora-primary-hover">Manage</a>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @if (Auth::id() !== $user->id)
        @can('delete', $user)
            <x-admin::modal.content
                modal="deleteModal-{{ $user->id }}"
                variant="danger"
                size="xl"
                position="centered"
                title="{{ __('common.delete_modal_title') }}"
                description="{{ __('common.delete_modal_description', ['item' => $user->email]) }}">
                <form action="{{ route('admin.users.destroy', ['id' => $user->id]) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <div class="flex justify-end gap-2 mt-4">
                        <x-admin::modal.trigger type="button" variant="close" class="bg-billmora-1 border-2 border-billmora-primary hover:bg-billmora-primary-hover px-3 py-2 text-billmora-primary hover:text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">{{ __('common.cancel') }}</x-admin::modal.trigger>
                        <button type="submit" class="bg-red-500 border-2 border-red-500 hover:bg-red-600 px-3 py-2 text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">{{ __('common.delete') }}</button>
                    </div>
                </form>
            </x-admin::modal.content>
        @endcan
    @endif
</div>
@endsection