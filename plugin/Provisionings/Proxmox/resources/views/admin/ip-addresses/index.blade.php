@extends('admin::layouts.app')

@section('title', 'Manage IPs: ' . $pool->name)

@section('body')
<div class="flex flex-col gap-4">
    <div class="flex flex-col md:flex-row gap-4 justify-between items-center">
        <div class="w-full md:w-100 flex items-center gap-4">
            <form action="{{ route('admin.provisionings.proxmox.ip-pools.ips.index', $pool) }}" method="GET" class="relative inline-block max-w-150 w-full group">
                <div class="absolute top-1/2 -translate-y-1/2 left-2.5 pointer-events-none">
                    <x-lucide-search class="w-5 h-auto text-slate-500 group-focus-within:text-billmora-primary-500" />
                </div>
                <input type="text" name="search" id="search" placeholder="Search IP..." value="{{ request('search') }}" class="w-full px-6 py-3 pl-10 bg-white text-slate-700 placeholder:text-slate-500 border-2 border-billmora-neutral-100 rounded-xl group-focus-within:outline-2 outline-billmora-primary-500">
                <div class="absolute top-1/2 -translate-y-1/2 right-1.5">
                    <button type="submit" class="bg-billmora-primary-500 hover:bg-billmora-primary-600 px-3 py-1.5 text-white rounded-lg transition duration-300 cursor-pointer">{{ __('common.submit') }}</button>
                </div>
            </form>
        </div>
        <div class="flex gap-3 items-center ml-auto">
            <a href="{{ route('admin.provisionings.proxmox.ip-pools.index') }}" class="bg-billmora-neutral-50 border-2 border-billmora-primary-500 hover:bg-billmora-primary-600 px-3 py-2 text-billmora-primary-500 hover:text-white rounded-lg transition-colors cursor-pointer text-sm font-semibold">Cancel</a>
            @can('provisionings.proxmox.manage')
                <a href="{{ route('admin.provisionings.proxmox.ip-pools.ips.create', $pool) }}" class="flex gap-1 items-center bg-billmora-primary-500 hover:bg-billmora-primary-600 px-3 py-2 text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer text-sm font-semibold">
                    <x-lucide-plus class="w-auto h-4" />
                    Add IPs
                </a>
            @endcan
        </div>
    </div>
    <div class="overflow-x-auto">
        <div class="min-w-full inline-block align-middle">
            <div class="border-2 border-billmora-neutral-100 rounded-2xl overflow-hidden">
                <table class="min-w-full divide-y divide-billmora-neutral-100">
                    <thead class="bg-billmora-neutral-100">
                        <tr>
                            <th scope="col" class="px-6 py-4 text-start text-xs font-semibold text-slate-500 uppercase">IP Address</th>
                            <th scope="col" class="px-6 py-4 text-start text-xs font-semibold text-slate-500 uppercase">Status</th>
                            <th scope="col" class="px-6 py-4 text-start text-xs font-semibold text-slate-500 uppercase">Associated Service</th>
                            <th scope="col" class="px-6 py-4 text-start text-xs font-semibold text-slate-500 uppercase">Added At</th>
                            <th scope="col" class="px-6 py-4 text-end text-xs font-semibold text-slate-500 uppercase">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y-2 divide-billmora-neutral-100 bg-white">
                        @forelse ($ips as $ip)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-800 font-mono">{{ $ip->ip_address }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                @if($ip->status === 'free')
                                    <span class="inline-flex items-center gap-1.5 py-1 px-2 rounded-md text-xs font-medium bg-green-100 text-green-800">Free</span>
                                @elseif($ip->status === 'used')
                                    <span class="inline-flex items-center gap-1.5 py-1 px-2 rounded-md text-xs font-medium bg-red-100 text-red-800">Used</span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 py-1 px-2 rounded-md text-xs font-medium bg-orange-100 text-orange-800">Reserved</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-800">
                                @if($ip->service_id)
                                    <a href="{{ route('admin.services.edit', $ip->service_id) }}" class="text-billmora-primary-500 hover:text-billmora-primary-600 font-medium hover:underline">
                                        #{{ $ip->service->service_number }}
                                    </a>
                                @else
                                    -
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">
                                {{ $ip->created_at->format(Billmora::getGeneral('company_date_format')) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-end text-sm font-medium space-x-2">
                                @can('provisionings.proxmox.manage')
                                    @if($ip->status !== 'used')
                                        <x-admin::modal.trigger modal="deleteModal-{{ $ip->id }}" variant="open" class="inline-flex items-center text-sm font-semibold text-red-400 hover:text-red-500 cursor-pointer">{{ __('common.delete') }}</x-admin::modal.trigger>
                                    @else
                                        <span class="text-slate-400 text-sm cursor-not-allowed">Cannot delete</span>
                                    @endif
                                @endcan
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="px-6 py-4 whitespace-nowrap text-sm text-slate-500 text-center">No IP addresses in this pool.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div>
        {{ $ips->links('admin::layouts.partials.pagination') }}
    </div>
    @can('provisionings.proxmox.manage')
        @foreach ($ips as $ip)
            @if($ip->status !== 'used')
            <x-admin::modal.content
                modal="deleteModal-{{ $ip->id }}"
                variant="danger"
                size="xl"
                position="centered"
                title="{{ __('common.delete_modal_title') }}"
                description="{{ __('common.delete_modal_description', ['item' => $ip->ip_address]) }}">
                <form action="{{ route('admin.provisionings.proxmox.ip-pools.ips.destroy', [$pool, $ip]) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <div class="flex justify-end gap-2 mt-4">
                        <x-admin::modal.trigger type="button" variant="close" class="bg-billmora-neutral-50 border-2 border-billmora-primary-500 hover:bg-billmora-primary-600 px-3 py-2 text-billmora-primary-500 hover:text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">{{ __('common.cancel') }}</x-admin::modal.trigger>
                        <button type="submit" class="bg-red-500 border-2 border-red-500 hover:bg-red-600 px-3 py-2 text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">{{ __('common.delete') }}</button>
                    </div>
                </form>
            </x-admin::modal.content>
            @endif
        @endforeach
    @endcan
</div>
@endsection
