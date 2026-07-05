@extends('admin::layouts.app')

@section('title', __('admin/update.title'))

@section('body')
    <div class="grid gap-5">
        {{-- Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <h2 class="text-2xl font-bold text-slate-600">{{ __('admin/update.title') }}</h2>
            <form action="{{ route('admin.update.check') }}" method="POST">
                @csrf
                <button type="submit"
                    class="flex items-center gap-2 bg-billmora-neutral-100 hover:bg-billmora-primary-500 text-billmora-primary-500 hover:text-white px-4 py-2.5 rounded-xl font-semibold text-sm transition-colors duration-300 w-full sm:w-auto justify-center cursor-pointer">
                    <x-lucide-refresh-cw class="w-4 h-4" />
                    {{ __('admin/update.actions.check') }}
                </button>
            </form>
        </div>

        {{-- Version Comparison Card --}}
        <div class="bg-white rounded-2xl p-5 sm:p-6 border-2 border-billmora-neutral-100">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4">
                <div class="flex items-center gap-3">
                    <div class="p-3 rounded-full bg-emerald-100 text-emerald-600 shrink-0">
                        <x-lucide-git-branch class="w-6 h-6" />
                    </div>
                    <h3 class="text-lg font-bold text-slate-600">{{ __('admin/update.title') }}</h3>
                </div>
                @if($isUpdateAvailable)
                    <span class="px-3 py-1 rounded-full text-xs font-bold uppercase bg-blue-100 text-blue-600 self-start sm:self-auto">
                        {{ __('admin/update.version.update_available') }}
                    </span>
                @elseif($release)
                    <span class="px-3 py-1 rounded-full text-xs font-bold uppercase bg-green-100 text-green-600 self-start sm:self-auto">
                        {{ __('admin/update.version.up_to_date') }}
                    </span>
                @else
                    <span class="px-3 py-1 rounded-full text-xs font-bold uppercase bg-slate-100 text-slate-600 self-start sm:self-auto">
                        {{ __('admin/update.version.unknown') }}
                    </span>
                @endif
            </div>
            <div class="flex flex-col sm:flex-row gap-4 sm:gap-8">
                <div class="flex flex-col">
                    <span class="text-xs font-semibold text-slate-400 uppercase">{{ __('admin/update.version.current') }}</span>
                    <span class="text-xl font-bold text-slate-600">{{ $currentVersion }}</span>
                </div>
                <div class="flex flex-col">
                    <span class="text-xs font-semibold text-slate-400 uppercase">{{ __('admin/update.version.latest') }}</span>
                    <span class="text-xl font-bold {{ $release ? 'text-billmora-primary-500' : 'text-slate-400' }}">
                        {{ $release['tag_name'] ?? 'N/A' }}
                    </span>
                </div>
            </div>
        </div>

        {{-- Requirements Card --}}
        <div class="bg-white rounded-2xl p-5 sm:p-6 border-2 border-billmora-neutral-100">
            <div class="flex items-start sm:items-center gap-3 mb-4">
                <div class="p-3 rounded-full bg-blue-100 text-blue-500 shrink-0">
                    <x-lucide-shield class="w-6 h-6" />
                </div>
                <div>
                    <h3 class="text-lg font-bold text-slate-600">{{ __('admin/update.requirements.title') }}</h3>
                    <p class="text-sm text-slate-500">{{ __('admin/update.requirements.description') }}</p>
                </div>
            </div>

            {{-- Unified Requirements List --}}
            <div class="mt-6 flex flex-col gap-3">
                @foreach($requirements as $requirement)
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 p-4 rounded-xl border-2 border-slate-100 bg-slate-50/50">
                        <div class="flex items-start gap-4">
                            @if($requirement['satisfied'])
                                <div class="mt-0.5 p-2 rounded-full bg-green-100 text-green-600 shrink-0">
                                    <x-lucide-check class="w-4 h-4" />
                                </div>
                            @else
                                <div class="mt-0.5 p-2 rounded-full bg-red-100 text-red-600 shrink-0">
                                    <x-lucide-x class="w-4 h-4" />
                                </div>
                            @endif
                            
                            <div class="flex flex-col">
                                <span class="font-bold text-slate-700">{{ $requirement['label'] }}</span>
                                <div class="mt-2 flex flex-wrap gap-2">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-sm bg-white border border-slate-200 rounded-md">
                                        <span class="text-slate-500">{{ __('admin/update.requirements.required') }}:</span>
                                        <span class="font-bold text-slate-700">{{ $requirement['required'] }}</span>
                                    </span>
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-sm bg-white border border-slate-200 rounded-md">
                                        <span class="text-slate-500">{{ __('admin/update.requirements.current') }}:</span>
                                        <span class="font-bold {{ $requirement['satisfied'] ? 'text-green-600' : 'text-red-600' }}">{{ $requirement['current'] }}</span>
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="self-start sm:self-center ml-12 sm:ml-0 shrink-0">
                            @if($requirement['satisfied'])
                                <span class="inline-flex items-center justify-center px-3 py-1 rounded-full text-xs font-bold bg-green-100 text-green-600 border border-green-200">
                                    {{ __('admin/update.requirements.passed') }}
                                </span>
                            @else
                                <span class="inline-flex items-center justify-center px-3 py-1 rounded-full text-xs font-bold bg-red-100 text-red-600 border border-red-200">
                                    {{ __('admin/update.requirements.failed') }}
                                </span>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Release Notes Card --}}
        @if($isUpdateAvailable && $release)
            <div class="bg-white rounded-2xl p-5 sm:p-6 border-2 border-billmora-neutral-100">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4">
                    <div class="flex items-start sm:items-center gap-3">
                        <div class="p-3 rounded-full bg-amber-100 text-amber-600 shrink-0">
                            <x-lucide-file-text class="w-6 h-6" />
                        </div>
                        <div>
                            <h3 class="text-base sm:text-lg font-bold text-slate-600">
                                {{ __('admin/update.release.title', ['version' => $release['tag_name']]) }}
                            </h3>
                            @if($release['published_at'])
                                <p class="text-xs sm:text-sm text-slate-400">
                                    {{ __('admin/update.release.published', ['date' => \Carbon\Carbon::parse($release['published_at'])->format('M d, Y')]) }}
                                </p>
                            @endif
                        </div>
                    </div>
                    @if($release['html_url'])
                        <a href="{{ $release['html_url'] }}" target="_blank"
                            class="flex items-center gap-1 text-sm font-semibold text-billmora-primary-500 hover:underline self-start sm:self-auto shrink-0">
                            <x-lucide-external-link class="w-4 h-4" />
                            {{ __('admin/update.release.view_github') }}
                        </a>
                    @endif
                </div>
                <div class="bg-slate-50 border border-slate-200 rounded-xl p-4 sm:p-5 overflow-x-auto break-words">
                    <div class="text-sm text-slate-600 leading-relaxed [&_p]:mb-3 [&_p:last-child]:mb-0 [&_ul]:list-disc [&_ul]:pl-5 [&_ul]:mb-3 [&_li]:mb-1 [&_h1]:font-bold [&_h1]:text-slate-800 [&_h1]:text-lg [&_h1]:mb-2 [&_h1]:mt-4 [&_h1:first-child]:mt-0 [&_h2]:font-bold [&_h2]:text-slate-800 [&_h2]:text-base [&_h2]:mb-2 [&_h2]:mt-4 [&_h2:first-child]:mt-0 [&_h3]:font-bold [&_h3]:text-slate-800 [&_h3]:mb-2 [&_h3]:mt-4 [&_h3:first-child]:mt-0 [&_a]:text-indigo-600 [&_a]:underline hover:[&_a]:text-indigo-700 [&_code]:bg-slate-200 [&_code]:px-1.5 [&_code]:py-0.5 [&_code]:rounded-md [&_code]:text-xs [&_code]:font-mono [&_strong]:font-bold [&_strong]:text-slate-700">
                        @if($release['body'])
                            {!! Str::markdown($release['body']) !!}
                        @else
                            <p class="text-slate-400 italic">{{ __('admin/update.release.no_notes') }}</p>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Warning --}}
            <x-admin::alert variant="warning" title="{{ __('admin/update.warning.title') }}">
                <p class="text-sm">{{ __('admin/update.warning.backup') }}</p>
            </x-admin::alert>

            {{-- Update Button --}}
            @can('update.execute')
                <div class="flex justify-center sm:justify-end">
                    <button type="button" id="updateBtn"
                        x-data
                        x-on:click="$store.modal.open = 'updateConfirmationModal'"
                        @if(!$allRequirementsMet) disabled @endif
                        class="flex items-center gap-2 bg-billmora-primary-500 hover:bg-billmora-primary-600 disabled:bg-slate-300 disabled:cursor-not-allowed text-white px-6 py-3 rounded-xl font-bold text-sm transition-colors duration-300 w-full sm:w-auto justify-center cursor-pointer">
                        <x-lucide-download class="w-5 h-5" />
                        {{ __('admin/update.actions.update', ['version' => $release['tag_name']]) }}
                    </button>
                </div>
            @endcan
        @endif
    </div>

    {{-- Confirmation Modal --}}
    @if($isUpdateAvailable && $release)
        <x-admin::modal.content 
            modal="updateConfirmationModal" 
            size="lg" 
            variant="warning" 
            position="simple" 
            title="{{ __('admin/update.actions.confirm_title') }}" 
            description="{{ __('admin/update.actions.confirm_message', ['version' => $release['tag_name']]) }}">
            
            <div class="flex flex-col-reverse sm:flex-row gap-3 sm:justify-end mt-4">
                <button type="button" x-on:click="$store.modal.close()"
                    class="px-5 py-2.5 rounded-xl font-semibold text-sm text-slate-600 bg-billmora-neutral-100 hover:bg-slate-200 transition-colors text-center w-full sm:w-auto">
                    {{ __('admin/update.actions.cancel_button') }}
                </button>
                <form action="{{ route('admin.update.execute') }}" method="POST" class="w-full sm:w-auto m-0">
                    @csrf
                    <button type="submit"
                        class="flex items-center gap-2 px-5 py-2.5 rounded-xl font-semibold text-sm text-white bg-billmora-primary-500 hover:bg-billmora-primary-600 transition-colors w-full sm:w-auto justify-center">
                        <x-lucide-zap class="w-4 h-4" />
                        {{ __('admin/update.actions.confirm_button') }}
                    </button>
                </form>
            </div>
        </x-admin::modal.content>
    @endif
@endsection
