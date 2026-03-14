@extends('admin::layouts.app')

@section('title', 'System Themes')

@section('body')
<div class="flex flex-col gap-4">
    <div class="flex flex-col md:flex-row gap-4 justify-between items-center">
        <div class="w-full md:w-100">
            <form action="{{ route('admin.themes') }}" method="GET" class="relative inline-block max-w-150 w-full group">
                <div class="absolute top-1/2 -translate-y-1/2 left-2.5 pointer-events-none">
                    <x-lucide-search class="w-5 h-auto text-slate-500 group-focus-within:text-billmora-primary-500" />
                </div>
                <input type="text" name="search" id="search" placeholder="{{ __('admin/common.search') }}" value="{{ request('search') }}" class="w-full px-6 py-3 pl-10 bg-white text-slate-700 placeholder:text-slate-500 border-2 border-billmora-2 rounded-xl group-focus-within:outline-2 outline-billmora-primary-500">
                <div class="absolute top-1/2 -translate-y-1/2 right-1.5">
                    <button type="submit" class="bg-billmora-primary-500 hover:bg-billmora-primary-600 px-3 py-1.5 text-white rounded-lg transition duration-300 cursor-pointer">{{ __('common.submit') }}</button>
                </div>
            </form>
        </div>
        <div class="ml-auto flex gap-2">
            @can('themes.install')
                <x-admin::modal.trigger modal="installModal" variant="open" class="flex gap-1 items-center bg-billmora-primary-500 hover:bg-billmora-primary-600 px-3 py-2 text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">
                    <x-lucide-plus class="w-auto h-5" />
                    {{ __('common.install') }}
                </x-admin::modal.trigger>
            @endcan
        </div>
    </div>
    <div class="flex flex-col-reverse md:flex-row gap-5">
        <div class="w-full md:w-2/3 h-fit grid grid-cols-1 lg:grid-cols-2 gap-5">
            @foreach ($themes as $theme)
                <div class="bg-white border-2 border-billmora-2 rounded-2xl flex flex-col overflow-hidden transition-all hover:border-billmora-primary-300">
                    <div class="flex h-48 w-full bg-white relative border-b-2 border-billmora-2">
                        @if ($theme->manifest['icon'] ?? false)
                            <img src="{{ $theme->manifest['icon'] ?? '' }}" 
                                alt="{{ $theme->name }} Preview" 
                                class="object-cover w-full h-full"
                            >
                        @else
                            <span class="m-auto text-5xl text-billmora-primary-500 font-semibold">{{ $theme->name }}</span>
                        @endif
                        <div class="absolute top-3 right-3 flex gap-2">
                            @if($theme->is_core)
                                <span class="bg-billmora-primary-500 text-white border border-billmora-primary-200 text-xs font-bold px-2.5 py-1 rounded-md">CORE</span>
                            @endif
                            @if($theme->is_active)
                                <span class="bg-green-100 text-green-700 border border-green-200 text-xs font-bold px-2.5 py-1 rounded-md">{{ __('common.active') }}</span>
                            @endif
                            @if($theme->is_active == false)
                                <span class="bg-red-100 text-red-700 border border-red-200 text-xs font-bold px-2.5 py-1 rounded-md">{{ __('common.inactive') }}</span>
                            @endif
                        </div>
                    </div>
                    <div class="p-6 flex-1 flex flex-col">
                        <div class="flex items-center justify-between mb-3">
                            <h3 class="text-xl font-semibold text-slate-800">{{ $theme->name }}</h3>
                            <span class="text-xs font-semibold text-billmora-primary-600 bg-billmora-2 px-2 py-1 rounded-md uppercase tracking-wide">
                                {{ $theme->type }}
                            </span>
                        </div>
                        <div class="text-sm text-slate-600 space-y-1.5 mb-6 flex-1">
                            <p class="flex items-center">
                                <span class="font-medium w-16">{{ __('admin/themes.version_label') }}:</span> 
                                <span class="text-slate-800">{{ $theme->manifest['version'] ?? 'N/A' }}</span>
                            </p>
                            <p class="flex items-center">
                                <span class="font-medium w-16">{{ __('admin/themes.author_label') }}:</span> 
                                @if(!empty($theme->manifest['url']))
                                    <a href="{{ $theme->manifest['url'] }}" target="_blank" class="text-billmora-primary-500 hover:text-billmora-primary-600 hover:underline font-medium">
                                        {{ $theme->manifest['author'] ?? 'Unknown' }}
                                    </a>
                                @else
                                    <span class="text-slate-800">{{ $theme->manifest['author'] ?? 'Unknown' }}</span>
                                @endif
                            </p>
                            <p class="flex items-center">
                                <span class="font-medium w-16">{{ __('admin/themes.folder_label') }}:</span> 
                                <code class="bg-slate-100 text-slate-700 px-1.5 py-0.5 rounded text-xs border border-slate-200">
                                    {{ $theme->provider }}
                                </code>
                            </p>
                        </div>
                        <div class="border-t-2 border-dashed border-billmora-2 pt-4 flex items-center justify-between mt-auto">
                            <a href="#" class="bg-billmora-2 hover:bg-billmora-3 px-3 py-2 text-billmora-primary-600 rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">
                                {{ __('common.manage') }}
                            </a>
                            <div class="flex items-center gap-2">
                                @can('themes.update')
                                    <x-admin::modal.trigger modal="updateModal-{{ $theme->id }}" variant="open" class="bg-yellow-500 hover:bg-yellow-600 px-3 py-2 text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">
                                        {{ __('common.update') }}
                                    </x-admin::modal.trigger>
                                @endcan
                                @if (!$theme->is_core && !$theme->is_active)
                                    @can('themes.uninstall')
                                        <x-admin::modal.trigger modal="deleteModal-{{ $theme->id }}" variant="open" class="bg-red-500 hover:bg-red-600 px-3 py-2 text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">
                                            {{ __('common.uninstall') }}
                                        </x-admin::modal.trigger>
                                    @endcan
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        <form action="{{ route('admin.themes.activate') }}" method="POST" class="w-full md:w-1/3 h-fit grid gap-4 bg-white p-8 border-2 border-billmora-2 rounded-2xl md:sticky top-28 right-0 shrink-0">
            @csrf
            <h3 class="text-xl font-semibold text-slate-600">{{ __('admin/themes.active_theme') }}</h3>
            @foreach (['admin', 'client', 'portal', 'email', 'invoice'] as $type)
                <x-admin::select
                    name="active_themes[{{ $type }}]"
                    label="{{ __('admin/themes.theme_label', ['type' => ucfirst($type)]) }}"
                    helper="{{ __('admin/themes.theme_helper', ['type' => $type]) }}"
                >
                    @foreach ($themes->where('type', $type) as $theme)
                        <option 
                            value="{{ $theme->id }}"
                            {{ (string) (old("active_themes.{$type}") ?? $activeThemes->get($type)) === (string) $theme->id ? 'selected' : '' }}
                        >
                            {{ $theme->name }} ({{ $theme->provider }})
                        </option>
                    @endforeach
                </x-admin::select>
            @endforeach
            @can('themes.update')
                <button type="submit" class="w-full bg-billmora-primary-500 hover:bg-billmora-primary-600 px-3 py-2 text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">
                    {{ __('common.save') }}
                </button>
            @endcan
        </form>
    </div>
    @can('themes.uninstall')
        @foreach ($themes as $theme)
            <x-admin::modal.content
                modal="deleteModal-{{ $theme['id'] }}"
                variant="danger"
                size="xl"
                position="centered"
                title="{{ __('common.uninstall_modal_title') }}"
                description="{{ __('common.uninstall_modal_description', ['item' => $theme['name']]) }}">
                <form action="{{ route('admin.themes.uninstall', ['theme' => $theme['id']]) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <div class="flex justify-end gap-2 mt-4">
                        <x-admin::modal.trigger type="button" variant="close" class="bg-billmora-1 border-2 border-billmora-primary-500 hover:bg-billmora-primary-600 px-3 py-2 text-billmora-primary-500 hover:text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">{{ __('common.cancel') }}</x-admin::modal.trigger>
                        <button type="submit" class="bg-red-500 border-2 border-red-500 hover:bg-red-600 px-3 py-2 text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">{{ __('common.uninstall') }}</button>
                    </div>
                </form>
            </x-admin::modal.content>
        @endforeach
    @endcan
    @can('themes.update')
        @foreach ($themes as $theme)
            <x-admin::modal.content
                modal="updateModal-{{ $theme['id'] }}"
                variant="danger"
                size="xl"
                position="centered"
                title="{{ __('common.update_modal_title') }}"
                description="{{ __('common.update_modal_description', ['item' => $theme['name']]) }}">
                <form action="{{ route('admin.themes.update', ['theme' => $theme['id']]) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="flex items-center justify-center w-full">
                        <label 
                            x-data="{ 
                                dragging: false, 
                                fileName: '',
                                handleDrop(e) {
                                    this.dragging = false;
                                    const files = e.dataTransfer.files;
                                    if (files.length > 0) {
                                        this.$refs.fileInput.files = files;
                                        this.fileName = files[0].name;
                                    }
                                },
                                handleInput(e) {
                                    const files = e.target.files;
                                    if (files.length > 0) {
                                        this.fileName = files[0].name;
                                    }
                                }
                            }"
                            x-on:dragover.prevent="dragging = true"
                            x-on:dragleave="dragging = false"
                            x-on:drop.prevent="handleDrop($event)"
                            :class="{ 'border-blue-500 bg-blue-50': dragging, 'border-billmora-2 bg-white': !dragging }"
                            class="flex flex-col items-center justify-center w-full h-64 border-2 border-dashed rounded-lg cursor-pointer hover:bg-billmora-1 transition-colors relative"
                        >
                            <div class="flex flex-col items-center justify-center pt-5 pb-6 px-4 text-center">
                                <x-lucide-upload class="w-10 h-10 mb-3 text-gray-400" />
                                <div x-show="!fileName">
                                    <p class="mb-2 text-sm text-gray-500">
                                        {{ __('admin/themes.upload.instruction') }}
                                    </p>
                                    <p class="text-xs text-gray-500">
                                        {{ __('admin/themes.upload.type_hint') }}
                                    </p>
                                </div>
                                <div x-show="fileName" class="text-blue-600 font-medium">
                                    <p class="text-sm" x-text="'{{ __('admin/themes.upload.selected_prefix') }}' + fileName"></p>
                                    <p class="text-xs text-gray-400 mt-1">
                                        {{ __('admin/themes.upload.replace_hint') }}
                                    </p>
                                </div>
                            </div>
                            <input 
                                x-ref="fileInput" 
                                id="dropzone-file" 
                                type="file" 
                                name="theme_file" 
                                class="hidden" 
                                accept=".zip"
                                x-on:change="handleInput($event)"
                            />
                        </label>
                    </div>
                    <div class="flex justify-end gap-2 mt-4">
                        <x-admin::modal.trigger type="button" variant="close" class="bg-billmora-1 border-2 border-billmora-primary-500 hover:bg-billmora-primary-600 px-3 py-2 text-billmora-primary-500 hover:text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">{{ __('common.cancel') }}</x-admin::modal.trigger>
                        <button type="submit" class="bg-red-500 border-2 border-red-500 hover:bg-red-600 px-3 py-2 text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">{{ __('common.update') }}</button>
                    </div>
                </form>
            </x-admin::modal.content>
        @endforeach
    @endcan
    @can('themes.install')
        <x-admin::modal.content
            modal="installModal"
            size="xl"
            title="{{ __('common.install_modal_title') }}"
            description="{{ __('common.install_modal_description', ['item' => __('admin/navigation.themes')]) }}">
            <form action="{{ route('admin.themes.install') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="flex items-center justify-center w-full">
                    <label 
                        x-data="{ 
                            dragging: false, 
                            fileName: '',
                            handleDrop(e) {
                                this.dragging = false;
                                const files = e.dataTransfer.files;
                                if (files.length > 0) {
                                    this.$refs.fileInput.files = files;
                                    this.fileName = files[0].name;
                                }
                            },
                            handleInput(e) {
                                const files = e.target.files;
                                if (files.length > 0) {
                                    this.fileName = files[0].name;
                                }
                            }
                        }"
                        x-on:dragover.prevent="dragging = true"
                        x-on:dragleave="dragging = false"
                        x-on:drop.prevent="handleDrop($event)"
                        :class="{ 'border-blue-500 bg-blue-50': dragging, 'border-billmora-2 bg-white': !dragging }"
                        class="flex flex-col items-center justify-center w-full h-64 border-2 border-dashed rounded-lg cursor-pointer hover:bg-billmora-1 transition-colors relative"
                    >
                        <div class="flex flex-col items-center justify-center pt-5 pb-6 px-4 text-center">
                            <x-lucide-upload class="w-10 h-10 mb-3 text-gray-400" />
                            <div x-show="!fileName">
                                <p class="mb-2 text-sm text-gray-500">
                                    {{ __('admin/themes.upload.instruction') }}
                                </p>
                                <p class="text-xs text-gray-500">
                                    {{ __('admin/themes.upload.type_hint') }}
                                </p>
                            </div>
                            <div x-show="fileName" class="text-blue-600 font-medium">
                                <p class="text-sm" x-text="'{{ __('admin/themes.upload.selected_prefix') }}' + fileName"></p>
                                <p class="text-xs text-gray-400 mt-1">
                                    {{ __('admin/themes.upload.replace_hint') }}
                                </p>
                            </div>
                        </div>
                        <input 
                            x-ref="fileInput" 
                            id="dropzone-file" 
                            type="file" 
                            name="theme_file" 
                            class="hidden" 
                            accept=".zip"
                            x-on:change="handleInput($event)"
                        />
                    </label>
                </div>
                <div class="flex justify-end gap-2 mt-4">
                    <x-admin::modal.trigger type="button" variant="close" class="bg-billmora-1 border-2 border-billmora-primary-500 hover:bg-billmora-primary-600 px-3 py-2 text-billmora-primary-500 hover:text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">{{ __('common.cancel') }}</x-admin::modal.trigger>
                    <button type="submit" class="bg-billmora-primary-500 border-2 border-billmora-primary-500 hover:bg-billmora-primary-600 px-3 py-2 text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">{{ __('common.install') }}</button>
                </div>
            </form>
        </x-admin::modal.content>
    @endcan
</div>
@endsection