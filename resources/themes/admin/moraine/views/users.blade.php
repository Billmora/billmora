@extends('admin::layouts.app')

@section('title', 'Manage Users')

@section('body')
<div class="flex flex-col gap-5">
    @if (session('success'))
        <x-admin::alert variant="success" title="{{ session('success') }}" />
    @endif
    <div class="flex flex-col gap-4">
        <div class="flex flex-col md:flex-row gap-4 justify-between items-center">
            <div class="w-full md:w-100">
                <form action="{{ route('admin.users') }}" method="GET" class="relative inline-block max-w-150 w-full group">
                    <div class="absolute top-1/2 -translate-y-1/2 left-2.5 pointer-events-none">
                        <x-lucide-search class="w-5 h-auto text-slate-500 group-focus-within:text-billmora-primary" />
                    </div>
                    <input type="text" name="searchUser" id="searchUser" placeholder="{{ __('admin/common.search') }}" value="{{ request('searchUser') }}" class="w-full px-6 py-3 pl-10 bg-white placeholder:text-gray-400 border-2 border-billmora-2 rounded-xl group-focus-within:outline-2 outline-billmora-primary">
                    <div class="absolute top-1/2 -translate-y-1/2 right-1.5">
                        <button type="submit" class="bg-billmora-primary hover:bg-billmora-primary-hover px-3 py-1.5 text-white rounded-lg transition duration-300 cursor-pointer">{{ __('common.submit') }}</button>
                    </div>
                </form>
            </div>
            @can('users.create')
                <a href="{{ route('admin.users.create') }}" class="flex gap-1 items-center bg-billmora-primary hover:bg-billmora-primary-hover px-3 py-2 ml-auto text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">
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
                                <th scope="col" class="px-6 py-4 text-start text-xs font-semibold text-slate-500 uppercase">ID</th>
                                <th scope="col" class="px-6 py-4 text-start text-xs font-semibold text-slate-500 uppercase">
                                    <x-admin::table.sorthead 
                                        column="fullname" 
                                        label="Fullname" 
                                        :sort="$sort" 
                                        :direction="$direction" 
                                    />
                                </th>
                                <th scope="col" class="px-6 py-4 text-start text-xs font-semibold text-slate-500 uppercase">
                                    <x-admin::table.sorthead 
                                        column="email" 
                                        label="Email" 
                                        :sort="$sort" 
                                        :direction="$direction" 
                                    />
                                </th>
                                <th scope="col" class="px-6 py-4 text-start text-xs font-semibold text-slate-500 uppercase">Role</th>
                                <th scope="col" class="px-6 py-4 text-start text-xs font-semibold text-slate-500 uppercase">
                                    <x-admin::table.sorthead 
                                        column="created_at" 
                                        label="Created At" 
                                        :sort="$sort" 
                                        :direction="$direction" 
                                    />
                                </th>
                                <th scope="col" class="px-6 py-4 text-end text-xs font-semibold text-slate-500 uppercase">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y-2 divide-billmora-2 bg-white">
                            @foreach ($users as $user)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-800">{{ $user->id }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-800">
                                    <a href="{{ route('admin.users.summary', ['id' => $user->id]) }}" class="inline-flex items-center text-sm font-semibold text-billmora-primary hover:text-billmora-primary-hover">{{ $user->fullname }}</a>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-800">{{ $user->email }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-800">
                                    @if ($user->isRootAdmin())
                                        Administrator
                                    @elseif ($user->roles->isNotEmpty())
                                        {{ $user->roles->pluck('name')->implode(', ') }}
                                    @else
                                        Client
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-800">{{ $user->created_at }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-end text-sm font-medium space-x-2">
                                    <a href="{{ route('admin.users.summary', ['id' => $user->id]) }}" class="inline-flex items-center text-sm font-semibold text-billmora-primary hover:text-billmora-primary-hover">Edit</a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div>
            {{ $users->links('admin::layouts.partials.pagination') }}
        </div>
    </div>
</div>
@endsection