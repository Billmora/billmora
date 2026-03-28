@extends('admin::layouts.app')

@section('title', __('admin/health.title'))

@section('body')
    <div class="grid gap-5">
        <h2 class="text-2xl font-bold text-slate-600">{{ __('admin/health.title') }}</h2>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
            {{-- Database Health --}}
            <div class="bg-white rounded-2xl p-6 border-2 border-billmora-2 flex flex-col gap-4">
                <div class="flex items-center justify-between">
                    <div class="p-3 rounded-full bg-blue-100 text-blue-500">
                        <x-lucide-database class="w-6 h-6" />
                    </div>
                    <span
                        class="px-3 py-1 rounded-full text-xs font-bold uppercase {{ $health['database'] ? 'bg-green-100 text-green-600' : 'bg-red-100 text-red-600' }}">
                        {{ $health['database'] ? __('admin/health.status.ok') : __('admin/health.status.issue') }}
                    </span>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-slate-600">{{ __('admin/health.database') }}</h3>
                    <p class="text-sm text-slate-500">{{ __('admin/health.cards.database_desc') }}</p>
                </div>
            </div>

            {{-- Cache Health --}}
            <div class="bg-white rounded-2xl p-6 border-2 border-billmora-2 flex flex-col gap-4">
                <div class="flex items-center justify-between">
                    <div class="p-3 rounded-full bg-purple-100 text-purple-500">
                        <x-lucide-zap class="w-6 h-6" />
                    </div>
                    <span
                        class="px-3 py-1 rounded-full text-xs font-bold uppercase {{ $health['cache'] ? 'bg-green-100 text-green-600' : 'bg-red-100 text-red-600' }}">
                        {{ $health['cache'] ? __('admin/health.status.ok') : __('admin/health.status.issue') }}
                    </span>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-slate-600">{{ __('admin/health.cache') }}</h3>
                    <p class="text-sm text-slate-500">{{ __('admin/health.cards.cache_desc') }}</p>
                </div>
            </div>

            {{-- Environment --}}
            <div class="bg-white rounded-2xl p-6 border-2 border-billmora-2 flex flex-col gap-4">
                <div class="flex items-center justify-between">
                    <div class="p-3 rounded-full bg-orange-100 text-orange-500">
                        <x-lucide-server class="w-6 h-6" />
                    </div>
                    <span class="px-3 py-1 rounded-full text-xs font-bold uppercase bg-slate-100 text-slate-600">
                        {{ ucfirst($health['environment']) }}
                    </span>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-slate-600">{{ __('admin/health.environment') }}</h3>
                    <p class="text-sm text-slate-500">{{ __('admin/health.cards.env_desc') }}</p>
                </div>
            </div>

            {{-- Debug Mode --}}
            <div class="bg-white rounded-2xl p-6 border-2 border-billmora-2 flex flex-col gap-4">
                <div class="flex items-center justify-between">
                    <div class="p-3 rounded-full bg-yellow-100 text-yellow-600">
                        <x-lucide-bug class="w-6 h-6" />
                    </div>
                    <span
                        class="px-3 py-1 rounded-full text-xs font-bold uppercase {{ $health['debug'] ? 'bg-yellow-100 text-yellow-700' : 'bg-slate-100 text-slate-600' }}">
                        {{ $health['debug'] ? __('admin/health.status.enabled') : __('admin/health.status.disabled') }}
                    </span>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-slate-600">{{ __('admin/health.debug_mode') }}</h3>
                    <p class="text-sm text-slate-500">{{ __('admin/health.cards.debug_desc') }}</p>
                </div>
            </div>

            {{-- PHP Version --}}
            <div class="bg-white rounded-2xl p-6 border-2 border-billmora-2 flex flex-col gap-4">
                <div class="flex items-center justify-between">
                    <div class="p-3 rounded-full bg-blue-50 text-blue-400">
                        <x-lucide-code-2 class="w-6 h-6" />
                    </div>
                    <span class="px-3 py-1 rounded-full text-xs font-bold uppercase bg-slate-100 text-slate-600">
                        {{ $health['php_version'] }}
                    </span>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-slate-600">{{ __('admin/health.php_version') }}</h3>
                    <p class="text-sm text-slate-500">{{ __('admin/health.cards.php_desc') }}</p>
                </div>
            </div>

            {{-- Laravel Version --}}
            <div class="bg-white rounded-2xl p-6 border-2 border-billmora-2 flex flex-col gap-4">
                <div class="flex items-center justify-between">
                    <div class="p-3 rounded-full bg-red-50 text-red-400">
                        <x-lucide-layers class="w-6 h-6" />
                    </div>
                    <span class="px-3 py-1 rounded-full text-xs font-bold uppercase bg-slate-100 text-slate-600">
                        {{ $health['laravel_version'] }}
                    </span>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-slate-600">{{ __('admin/health.laravel_version') }}</h3>
                    <p class="text-sm text-slate-500">{{ __('admin/health.cards.laravel_desc') }}</p>
                </div>
            </div>

            {{-- Version Comparison --}}
            <div
                class="bg-white rounded-2xl p-6 border-2 border-billmora-2 flex flex-col gap-4 md:col-span-2 lg:col-span-3">
                <div class="flex items-center justify-between">
                    <div class="p-3 rounded-full bg-emerald-100 text-emerald-600">
                        <x-lucide-git-branch class="w-6 h-6" />
                    </div>
                    @if($health['version']['latest'])
                        @php
                            $isUpToDate = $health['version']['current'] === $health['version']['latest'] ||
                                str_contains($health['version']['latest'], $health['version']['current']);
                        @endphp
                        <span
                            class="px-3 py-1 rounded-full text-xs font-bold uppercase {{ $isUpToDate ? 'bg-green-100 text-green-600' : 'bg-blue-100 text-blue-600' }}">
                            {{ $isUpToDate ? __('admin/health.status.up_to_date') : __('admin/health.status.update_available') }}
                        </span>
                    @else
                        <span class="px-3 py-1 rounded-full text-xs font-bold uppercase bg-slate-100 text-slate-600">
                            {{ __('admin/health.status.unknown') }}
                        </span>
                    @endif
                </div>
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div class="flex-1">
                        <h3 class="text-lg font-bold text-slate-600">{{ __('admin/health.version') }}</h3>
                        <p class="text-sm text-slate-500">{{ __('admin/health.cards.version_desc') }}</p>
                    </div>
                    <div class="flex gap-8">
                        <div class="flex flex-col">
                            <span
                                class="text-xs font-semibold text-slate-400 uppercase">{{ __('admin/health.current_version') }}</span>
                            <span class="text-xl font-bold text-slate-600">{{ $health['version']['current'] }}</span>
                        </div>
                        <div class="flex flex-col">
                            <span
                                class="text-xs font-semibold text-slate-400 uppercase">{{ __('admin/health.latest_version') }}</span>
                            <span
                                class="text-xl font-bold {{ $health['version']['latest'] ? 'text-billmora-primary-500' : 'text-slate-400' }}">
                                {{ $health['version']['latest'] ?? 'N/A' }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection