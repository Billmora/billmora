@extends('admin::layouts.app')

@section('title', 'Plugin Provisinings')

@section('body')
<div class="flex flex-col gap-4">
    <div class="flex flex-col md:flex-row gap-4 justify-between items-center">
        <div class="w-full md:w-100">
            <form action="{{ route('admin.provisionings') }}" method="GET" class="relative inline-block max-w-150 w-full group">
                <div class="absolute top-1/2 -translate-y-1/2 left-2.5 pointer-events-none">
                    <x-lucide-search class="w-5 h-auto text-slate-500 group-focus-within:text-billmora-primary" />
                </div>
                <input type="text" name="search" id="search" placeholder="{{ __('admin/common.search') }}" value="{{ request('search') }}" class="w-full px-6 py-3 pl-10 bg-white placeholder:text-gray-400 border-2 border-billmora-2 rounded-xl group-focus-within:outline-2 outline-billmora-primary">
                <div class="absolute top-1/2 -translate-y-1/2 right-1.5">
                    <button type="submit" class="bg-billmora-primary hover:bg-billmora-primary-hover px-3 py-1.5 text-white rounded-lg transition duration-300 cursor-pointer">{{ __('common.submit') }}</button>
                </div>
            </form>
        </div>
        @can('provisionings.install')
            <x-admin::modal.trigger modal="installModal" variant="open" class="flex gap-1 items-center bg-billmora-primary hover:bg-billmora-primary-hover px-3 py-2 ml-auto text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">
                <x-lucide-plus class="w-auto h-5" />
                {{ __('common.install') }}
            </x-admin::modal.trigger>
        @endcan
    </div>
    <div class="overflow-x-auto">
        <div class="min-w-full inline-block align-middle">
            <div class="border-2 border-billmora-2 rounded-2xl overflow-hidden">
                <table class="min-w-full divide-y divide-billmora-2">
                    <thead class="bg-billmora-2">
                        <tr>
                            <th scope="col" class="px-6 py-4 text-start text-xs font-semibold text-slate-500 uppercase">{{ __('admin/provisionings.name_label') }}</th>
                            <th scope="col" class="px-6 py-4 text-start text-xs font-semibold text-slate-500 uppercase">{{ __('admin/provisionings.instance_count_label') }}</th>
                            <th scope="col" class="px-6 py-4 text-start text-xs font-semibold text-slate-500 uppercase">{{ __('admin/provisionings.version_label') }}</th>
                            <th scope="col" class="px-6 py-4 text-start text-xs font-semibold text-slate-500 uppercase">{{ __('admin/provisionings.author_label') }}</th>
                            <th scope="col" class="px-6 py-4 text-end text-xs font-semibold text-slate-500 uppercase">{{ __('common.action') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y-2 divide-billmora-2 bg-white">
                        @foreach ($provisionings as $provisioning)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-800">{{ $provisioning->name }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-800">{{ $provisioning->instance_count }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-800">{{ $provisioning->version }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-800">{{ $provisioning->author }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-end text-sm font-medium space-x-2">
                                    @can('provisionings.instance.view')
                                        <a href="{{ route('admin.provisionings.instance', ['driver' => $provisioning->driver]) }}" class="inline-flex items-center text-sm font-semibold text-billmora-primary hover:text-billmora-primary-hover">{{ __('common.manage') }}</a>                               
                                    @endcan
                                    @can('provisionings.uninstall')
                                        <x-admin::modal.trigger modal="deleteModal-{{ $provisioning->driver }}" variant="open" class="inline-flex items-center text-sm font-semibold text-red-400 hover:text-red-500 cursor-pointer">{{ __('common.uninstall') }}</x-admin::modal.trigger>
                                    @endcan
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @can('provisionings.uninstall')
        @foreach ($provisionings as $provisioning)
            <x-admin::modal.content
                modal="deleteModal-{{ $provisioning->driver }}"
                variant="danger"
                size="xl"
                position="centered"
                title="{{ __('common.uninstall_modal_title') }}"
                description="{{ __('common.uninstall_modal_description', ['item' => $provisioning->name]) }}">
                <form action="{{ route('admin.provisionings.uninstall', ['driver' => $provisioning->driver]) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <div class="flex justify-end gap-2 mt-4">
                        <x-admin::modal.trigger type="button" variant="close" class="bg-billmora-1 border-2 border-billmora-primary hover:bg-billmora-primary-hover px-3 py-2 text-billmora-primary hover:text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">{{ __('common.cancel') }}</x-admin::modal.trigger>
                        <button type="submit" class="bg-red-500 border-2 border-red-500 hover:bg-red-600 px-3 py-2 text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">{{ __('common.uninstall') }}</button>
                    </div>
                </form>
            </x-admin::modal.content>
        @endforeach
    @endcan
    @can('provisionings.install')
        <x-admin::modal.content
            modal="installModal"
            size="xl"
            title="{{ __('common.install_modal_title') }}"
            description="{{ __('common.install_modal_description', ['item' => 'Provisining']) }}">
            <form action="{{ route('admin.provisionings.install') }}" method="POST" enctype="multipart/form-data">
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
                                    {{ __('admin/provisionings.upload.instruction') }}
                                </p>
                                <p class="text-xs text-gray-500">
                                    {{ __('admin/provisionings.upload.type_hint') }}
                                </p>
                            </div>
                            <div x-show="fileName" class="text-blue-600 font-medium">
                                <p class="text-sm" x-text="'{{ __('admin/provisionings.upload.selected_prefix') }}' + fileName"></p>
                                <p class="text-xs text-gray-400 mt-1">
                                    {{ __('admin/provisionings.upload.replace_hint') }}
                                </p>
                            </div>
                        </div>
                        <input 
                            x-ref="fileInput" 
                            id="dropzone-file" 
                            type="file" 
                            name="plugin_file" 
                            class="hidden" 
                            accept=".zip"
                            x-on:change="handleInput($event)"
                        />
                    </label>
                </div>
                <div class="flex justify-end gap-2 mt-4">
                    <x-admin::modal.trigger type="button" variant="close" class="bg-billmora-1 border-2 border-billmora-primary hover:bg-billmora-primary-hover px-3 py-2 text-billmora-primary hover:text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">{{ __('common.cancel') }}</x-admin::modal.trigger>
                    <button type="submit" class="bg-billmora-primary border-2 border-billmora-primary hover:bg-billmora-primary-hover px-3 py-2 text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">{{ __('common.install') }}</button>
                </div>
            </form>
        </x-admin::modal.content>
    @endcan
</div>
@endsection