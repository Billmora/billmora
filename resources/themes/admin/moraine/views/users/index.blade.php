@extends('admin::layouts.app')

@section('title', 'Manage Users')

@section('body')
<div class="flex flex-col gap-5">
    <div class="flex flex-col gap-4">
        <div class="flex flex-col md:flex-row gap-4 justify-between items-center">
            <div class="w-full md:w-auto flex gap-2 flex-1 max-w-150">
                <form action="{{ route('admin.users') }}" method="GET" class="relative inline-block w-full group m-0">
                    <div class="absolute top-1/2 -translate-y-1/2 left-2.5 pointer-events-none">
                        <x-lucide-search class="w-5 h-auto text-slate-500 group-focus-within:text-billmora-primary-500" />
                    </div>
                @foreach(request()->only(['sort', 'direction']) as $key => $value)
                    <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                @endforeach
                @foreach(request()->query() as $key => $value)
                    @if(str_starts_with($key, 'filter_') && !is_null($value) && $value !== '')
                        <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                    @endif
                @endforeach
                    <input type="text" name="searchUser" id="searchUser" placeholder="{{ __('admin/common.search') }}" value="{{ request('searchUser') }}" class="w-full px-6 py-3 pl-10 bg-white text-slate-700 placeholder:text-slate-500 border-2 border-billmora-neutral-100 rounded-xl group-focus-within:outline-2 outline-billmora-primary-500">
                    <div class="absolute top-1/2 -translate-y-1/2 right-1.5">
                        <button type="submit" class="bg-billmora-primary-500 hover:bg-billmora-primary-600 px-3 py-1.5 text-white rounded-lg transition duration-300 cursor-pointer">{{ __('common.submit') }}</button>
                    </div>
                </form>
                
                <x-admin::drawer.trigger drawer="usersFilter" type="button" class="relative flex items-center justify-center bg-white border-2 border-billmora-neutral-100 hover:border-billmora-primary-500 text-slate-600 px-3 py-2 rounded-xl transition duration-300 cursor-pointer shrink-0">
                    <x-lucide-filter class="w-5 h-auto" />
                    @if(collect($filters ?? [])->filter()->isNotEmpty())
                        <span class="absolute top-1.5 right-1.5 w-2.5 h-2.5 bg-red-500 rounded-full border-2 border-white"></span>
                    @endif
                </x-admin::drawer.trigger>
            </div>
            @can('users.create')
                <a href="{{ route('admin.users.create') }}" class="flex gap-1 items-center bg-billmora-primary-500 hover:bg-billmora-primary-600 px-3 py-2 ml-auto text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">
                    <x-lucide-plus class="w-auto h-5" />
                    {{ __('common.create') }}
                </a>
            @endcan
        </div>
        
        {{-- Active Filters --}}
        @if(collect($filters ?? [])->filter()->isNotEmpty())
            <div class="flex flex-wrap gap-2 items-center">
                <span class="text-sm text-slate-500 font-medium mr-1">{{ __('common.filter') ?? 'Filters' }}:</span>
                
                @if(!empty($filters['country']))
                    <a href="{{ request()->fullUrlWithoutQuery('filter_country') }}" class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg bg-billmora-neutral-100 text-sm font-medium text-slate-700 hover:bg-billmora-neutral-200 transition-colors">
                        {{ __('common.country') }}: {{ strtoupper($filters['country']) }}
                        <x-lucide-x class="w-3.5 h-3.5" />
                    </a>
                @endif

                @if(!empty($filters['role']))
                    <a href="{{ request()->fullUrlWithoutQuery('filter_role') }}" class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg bg-billmora-neutral-100 text-sm font-medium text-slate-700 hover:bg-billmora-neutral-200 transition-colors">
                        {{ __('common.role') }}: {{ ucfirst($filters['role']) }}
                        <x-lucide-x class="w-3.5 h-3.5" />
                    </a>
                @endif

                @if(!empty($filters['status']))
                    <a href="{{ request()->fullUrlWithoutQuery('filter_status') }}" class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg bg-billmora-neutral-100 text-sm font-medium text-slate-700 hover:bg-billmora-neutral-200 transition-colors">
                        {{ __('common.status') }}: {{ ucfirst($filters['status']) }}
                        <x-lucide-x class="w-3.5 h-3.5" />
                    </a>
                @endif
                
                @if(!empty($filters['date_from']))
                    <a href="{{ request()->fullUrlWithoutQuery('filter_date_from') }}" class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg bg-billmora-neutral-100 text-sm font-medium text-slate-700 hover:bg-billmora-neutral-200 transition-colors">
                        {{ __('common.created_at') }} ({{ __('common.date_from') ?? 'From' }}): {{ $filters['date_from'] }}
                        <x-lucide-x class="w-3.5 h-3.5" />
                    </a>
                @endif
                
                @if(!empty($filters['date_to']))
                    <a href="{{ request()->fullUrlWithoutQuery('filter_date_to') }}" class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg bg-billmora-neutral-100 text-sm font-medium text-slate-700 hover:bg-billmora-neutral-200 transition-colors">
                        {{ __('common.created_at') }} ({{ __('common.date_to') ?? 'To' }}): {{ $filters['date_to'] }}
                        <x-lucide-x class="w-3.5 h-3.5" />
                    </a>
                @endif

                @if(!empty($filters['updated_at_from']))
                    <a href="{{ request()->fullUrlWithoutQuery('filter_updated_at_from') }}" class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg bg-billmora-neutral-100 text-sm font-medium text-slate-700 hover:bg-billmora-neutral-200 transition-colors">
                        {{ __('common.updated_at') }} ({{ __('common.date_from') ?? 'From' }}): {{ $filters['updated_at_from'] }}
                        <x-lucide-x class="w-3.5 h-3.5" />
                    </a>
                @endif
                
                @if(!empty($filters['updated_at_to']))
                    <a href="{{ request()->fullUrlWithoutQuery('filter_updated_at_to') }}" class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg bg-billmora-neutral-100 text-sm font-medium text-slate-700 hover:bg-billmora-neutral-200 transition-colors">
                        {{ __('common.updated_at') }} ({{ __('common.date_to') ?? 'To' }}): {{ $filters['updated_at_to'] }}
                        <x-lucide-x class="w-3.5 h-3.5" />
                    </a>
                @endif
                
                <a href="{{ request()->url() }}" class="text-sm font-medium text-red-500 hover:text-red-600 ml-2">{{ __('common.clear_all') ?? 'Clear All' }}</a>
            </div>
        @endif
        <div class="overflow-x-auto">
            <div class="min-w-full inline-block align-middle">
                <div class="border-2 border-billmora-neutral-100 rounded-2xl overflow-hidden">
                    <table class="min-w-full divide-y divide-billmora-neutral-100">
                        <thead class="bg-billmora-neutral-100">
                            <tr>
                                <th scope="col" class="px-6 py-4 text-start text-xs font-semibold text-slate-500 uppercase">ID</th>
                                <th scope="col" class="px-6 py-4 text-start text-xs font-semibold text-slate-500 uppercase">
                                    <x-admin::table.sorthead 
                                        column="fullname" 
                                        label="{{ __('common.fullname') }}" 
                                        :sort="$sort" 
                                        :direction="$direction" 
                                    />
                                </th>
                                <th scope="col" class="px-6 py-4 text-start text-xs font-semibold text-slate-500 uppercase">
                                    <x-admin::table.sorthead 
                                        column="email" 
                                        label="{{ __('common.email') }}" 
                                        :sort="$sort" 
                                        :direction="$direction" 
                                    />
                                </th>
                                <th scope="col" class="px-6 py-4 text-start text-xs font-semibold text-slate-500 uppercase">{{ __('common.role') }}</th>
                                <th scope="col" class="px-6 py-4 text-start text-xs font-semibold text-slate-500 uppercase">
                                    <x-admin::table.sorthead 
                                        column="created_at" 
                                        label="{{ __('common.created_at') }}" 
                                        :sort="$sort" 
                                        :direction="$direction" 
                                    />
                                </th>
                                <th scope="col" class="px-6 py-4 text-end text-xs font-semibold text-slate-500 uppercase">{{ __('common.action') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y-2 divide-billmora-neutral-100 bg-white">
                            @forelse ($users as $user)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-800">{{ $user->id }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-800">
                                    <a href="{{ route('admin.users.summary', ['user' => $user->id]) }}" class="inline-flex items-center text-sm font-semibold text-billmora-primary-500 hover:text-billmora-primary-hover">{{ $user->fullname }}</a>
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
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-800">{{ $user->created_at->format(Billmora::getGeneral('company_date_format')) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-end text-sm font-medium space-x-2">
                                    <a href="{{ route('admin.users.summary', ['user' => $user->id]) }}" class="inline-flex items-center text-sm font-semibold text-billmora-primary-500 hover:text-billmora-primary-hover">
                                        {{ __('common.edit') }}
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="px-6 py-8 text-center text-sm text-slate-400">{{ __('common.no_data') }}</td>
                            </tr>
                            @endforelse
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

<x-admin::drawer.content drawer="usersFilter" title="{{ __('common.filter_title', ['resource' => __('admin/navigation.users')]) }}" action="{{ request()->url() }}">
    @if(request()->has('searchUser'))
        <input type="hidden" name="searchUser" value="{{ request('searchUser') }}">
    @endif
    @if(request()->has('sort'))
        <input type="hidden" name="sort" value="{{ request('sort') }}">
        <input type="hidden" name="direction" value="{{ request('direction') }}">
    @endif

    <x-admin::select name="filter_role" label="{{ __('admin/users.filter.role_label') }}">
        <option value="root" @selected(request('filter_role') === 'root')>{{ __('admin/users.filter.role_administrator') }}</option>
        @foreach(\Spatie\Permission\Models\Role::pluck('name') as $role)
            <option value="{{ $role }}" @selected(request('filter_role') === $role)>{{ ucfirst($role) }}</option>
        @endforeach
    </x-admin::select>

    <x-admin::select name="filter_status" label="{{ __('admin/users.filter.status_label') }}">
        <option value="active" @selected(request('filter_status') === 'active')>{{ __('admin/users.filter.status_active') }}</option>
        <option value="inactive" @selected(request('filter_status') === 'inactive')>{{ __('admin/users.filter.status_inactive') }}</option>
        <option value="suspended" @selected(request('filter_status') === 'suspended')>{{ __('admin/users.filter.status_suspended') }}</option>
        <option value="closed" @selected(request('filter_status') === 'closed')>{{ __('admin/users.filter.status_closed') }}</option>
    </x-admin::select>

    <x-admin::select name="filter_country" label="{{ __('admin/users.filter.country_label') }}">
        @foreach(config('utils.countries') as $code => $country)
            <option value="{{ $code }}" @selected(request('filter_country') === $code)>{{ $country }}</option>
        @endforeach
    </x-admin::select>

    <div class="grid grid-cols-2 gap-4">
        <x-admin::input name="filter_date_from" type="date"
            label="{{ __('common.created_at') }} ({{ __('common.date_from') }})"
            value="{{ request('filter_date_from') }}" />
        <x-admin::input name="filter_date_to" type="date"
            label="{{ __('common.created_at') }} ({{ __('common.date_to') }})"
            value="{{ request('filter_date_to') }}" />
    </div>

    <div class="grid grid-cols-2 gap-4">
        <x-admin::input name="filter_updated_at_from" type="date"
            label="{{ __('common.updated_at') }} ({{ __('common.date_from') }})"
            value="{{ request('filter_updated_at_from') }}" />
        <x-admin::input name="filter_updated_at_to" type="date"
            label="{{ __('common.updated_at') }} ({{ __('common.date_to') }})"
            value="{{ request('filter_updated_at_to') }}" />
    </div>
</x-admin::drawer.content>


@endsection