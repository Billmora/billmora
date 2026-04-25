@extends('admin::layouts.app')

@section('title', 'Product Catalogs')

@section('body')
    <div class="flex flex-col gap-4">
        @can('catalogs.create')
            <a href="{{ route('admin.catalogs.create') }}"
                class="flex gap-1 items-center bg-billmora-primary-500 hover:bg-billmora-primary-600 px-3 py-2 ml-auto text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">
                <x-lucide-plus class="w-auto h-5" />
                {{ __('common.create') }}
            </a>
        @endcan
        <div class="overflow-x-auto" data-sortable-wrapper>
            <div class="min-w-full inline-block align-middle">
                <div class="border-2 border-billmora-2 rounded-2xl overflow-hidden">
                    <table class="min-w-full divide-y divide-billmora-2">
                        <thead class="bg-billmora-2">
                            <tr>
                                <th scope="col" class="w-10 px-4 py-4"></th>
                                <th scope="col" class="px-6 py-4 text-start text-xs font-semibold text-slate-500 uppercase">
                                    {{ __('admin/catalogs.name_label') }}</th>
                                <th scope="col" class="px-6 py-4 text-start text-xs font-semibold text-slate-500 uppercase">
                                    {{ __('admin/catalogs.slug_label') }}</th>
                                <th scope="col" class="px-6 py-4 text-start text-xs font-semibold text-slate-500 uppercase">
                                    {{ __('admin/catalogs.status_label') }}</th>
                                <th scope="col" class="px-6 py-4 text-start text-xs font-semibold text-slate-500 uppercase">
                                    {{ __('common.created_at') }}</th>
                                <th scope="col" class="px-6 py-4 text-end text-xs font-semibold text-slate-500 uppercase">
                                    {{ __('common.action') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y-2 divide-billmora-2 bg-white" data-sortable="Catalog">
                            @forelse ($catalogs as $catalog)
                                <tr data-id="{{ $catalog->id }}">
                                    <td class="px-4 py-4 whitespace-nowrap text-slate-300">
                                        <x-lucide-grip-vertical class="w-5 h-5 drag-handle" />
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-800">{{ $catalog->name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-800">{{ $catalog->slug }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-800">{{ $catalog->status }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-800">
                                        {{ $catalog->created_at->format(Billmora::getGeneral('company_date_format')) }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-end text-sm font-medium space-x-2">
                                        @can('catalogs.update')
                                            <a href="{{ route('admin.catalogs.edit', ['catalog' => $catalog->id]) }}"
                                                class="inline-flex items-center text-sm font-semibold text-billmora-primary-500 hover:text-billmora-primary-600">
                                                {{ __('common.edit') }}
                                            </a>
                                        @endcan
                                        @can('catalogs.delete')
                                            <x-admin::modal.trigger modal="deleteModal-{{ $catalog->id }}" variant="open"
                                                class="inline-flex items-center text-sm font-semibold text-red-400 hover:text-red-500 cursor-pointer">{{ __('common.delete') }}</x-admin::modal.trigger>
                                        @endcan
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-8 text-center text-sm text-slate-400">
                                        {{ __('common.no_data') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div>
            {{ $catalogs->links('admin::layouts.partials.pagination') }}
        </div>
        @can('catalogs.delete')
            @foreach ($catalogs as $catalog)
                <x-admin::modal.content modal="deleteModal-{{ $catalog->id }}" variant="danger" size="xl" position="centered"
                    title="{{ __('common.delete_modal_title') }}"
                    description="{{ __('common.delete_modal_description', ['item' => $catalog->name]) }}">
                    <form action="{{ route('admin.catalogs.destroy', $catalog->id) }}" method="POST">
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