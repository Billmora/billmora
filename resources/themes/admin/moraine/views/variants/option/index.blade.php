@extends('admin::layouts.app')

@section('title', 'Variant Option')

@section('body')
<div class="flex flex-col gap-5">
    <x-admin::tabs 
        :tabs="[
            [
                'route' => route('admin.variants.edit', ['id' => $variant->id]),
                'icon' => 'lucide-boxes',
                'label' => __('admin/variants.tabs.summary'),
            ],
            [
                'route' => route('admin.variants.options', ['id' => $variant->id]),
                'icon' => 'lucide-codesandbox',
                'label' => __('admin/variants.tabs.options'),
            ],
        ]" 
        active="{{ request()->fullUrl() }}" />
    <div class="flex flex-col md:flex-row gap-4 justify-between items-center">
        <a href="{{ route('admin.variants.options.create', ['id' => $variant->id]) }}" class="flex gap-1 items-center bg-billmora-primary hover:bg-billmora-primary-hover px-3 py-2 ml-auto text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">
            <x-lucide-plus class="w-auto h-5" />
            {{ __('common.create') }}
        </a>
    </div>
    <div class="overflow-x-auto">
        <div class="min-w-full inline-block align-middle">
            <div class="border-2 border-billmora-2 rounded-2xl overflow-hidden">
                <table class="min-w-full divide-y divide-billmora-2">
                    <thead class="bg-billmora-2">
                        <tr>
                            <th scope="col" class="px-6 py-4 text-start text-xs font-semibold text-slate-500 uppercase">#</th>
                            <th scope="col" class="px-6 py-4 text-start text-xs font-semibold text-slate-500 uppercase">{{ __('admin/variants.options.name_label') }}</th>
                            <th scope="col" class="px-6 py-4 text-start text-xs font-semibold text-slate-500 uppercase">{{ __('admin/variants.options.value_label') }}</th>
                            <th scope="col" class="px-6 py-4 text-start text-xs font-semibold text-slate-500 uppercase">{{ __('common.created_at') }}</th>
                            <th scope="col" class="px-6 py-4 text-end text-xs font-semibold text-slate-500 uppercase">{{ __('common.action') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y-2 divide-billmora-2 bg-white">
                        @foreach ($options as $option)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-800">{{ $loop->iteration }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-800">{{ $option->name }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-800">{{ $option->value }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-800">{{ $option->created_at }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-end text-sm font-medium space-x-2">
                                    <a href="#" class="inline-flex items-center text-sm font-semibold text-billmora-primary hover:text-billmora-primary-hover">
                                        {{ __('common.edit') }}
                                    </a>
                                    <x-admin::modal.trigger modal="deleteModal-{{ $option->id }}" variant="open" class="inline-flex items-center text-sm font-semibold text-red-400 hover:text-red-500 cursor-pointer">{{ __('common.delete') }}</x-admin::modal.trigger>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div>
        {{ $options->links('admin::layouts.partials.pagination') }}
    </div>
    @foreach ($options as $option)
        <x-admin::modal.content
            modal="deleteModal-{{ $option->id }}"
            variant="danger"
            size="xl"
            position="centered"
            title="{{ __('common.delete_modal_title') }}"
            description="{{ __('common.delete_modal_description', ['item' => $option->name]) }}">
            <form action="#" method="POST">
                @csrf
                @method('DELETE')
                <div class="flex justify-end gap-2 mt-4">
                    <x-admin::modal.trigger type="button" variant="close" class="bg-billmora-1 border-2 border-billmora-primary hover:bg-billmora-primary-hover px-3 py-2 text-billmora-primary hover:text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">{{ __('common.cancel') }}</x-admin::modal.trigger>
                    <button type="submit" class="bg-red-500 border-2 border-red-500 hover:bg-red-600 px-3 py-2 text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">{{ __('common.delete') }}</button>
                </div>
            </form>
        </x-admin::modal.content>
    @endforeach
</div>
@endsection