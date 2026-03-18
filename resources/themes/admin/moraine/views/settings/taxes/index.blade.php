@extends('admin::layouts.app')

@section('title', 'Tax Settings - Tax')

@section('body')
<div class="flex flex-col gap-4">
    @can('settings.taxes.create')
        <a href="{{ route('admin.settings.taxes.create') }}" class="flex gap-1 items-center bg-billmora-primary-500 hover:bg-billmora-primary-600 px-3 py-2 ml-auto text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">
            <x-lucide-plus class="w-auto h-5" />
            {{ __('common.create') }}
        </a>
    @endcan
    <div class="overflow-x-auto">
        <div class="min-w-full inline-block align-middle">
            <div class="border-2 border-billmora-2 rounded-2xl overflow-hidden">
                <table class="min-w-full divide-y divide-billmora-2">
                    <thead class="bg-billmora-2">
                        <tr>
                            <th scope="col" class="px-6 py-4 text-start text-xs font-semibold text-slate-500 uppercase">{{ __('admin/settings/tax.tax_table.name') }}</th>
                            <th scope="col" class="px-6 py-4 text-start text-xs font-semibold text-slate-500 uppercase">{{ __('admin/settings/tax.tax_table.value') }}</th>
                            <th scope="col" class="px-6 py-4 text-start text-xs font-semibold text-slate-500 uppercase">{{ __('admin/settings/tax.tax_table.country') }}</th>
                            <th scope="col" class="px-6 py-4 text-start text-xs font-semibold text-slate-500 uppercase">{{ __('common.created_at') }}</th>
                            <th scope="col" class="px-6 py-4 text-end text-xs font-semibold text-slate-500 uppercase">{{ __('common.action') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y-2 divide-billmora-2 bg-white">
                        @foreach ($taxes as $tax)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-800">{{ $tax->name }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-800">{{ $tax->value }}%</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-800">{{ $tax->country ? config("utils.countries.{$tax->country}", $tax->country) : 'Global' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-800">{{ $tax->created_at->format(Billmora::getGeneral('company_date_format')) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-end text-sm font-medium space-x-2">
                                @can('settings.taxes.update')
                                    <a href="{{ route('admin.settings.taxes.edit', ['tax' => $tax->id]) }}" class="inline-flex items-center text-sm font-semibold text-billmora-primary-500 hover:text-billmora-primary-600">
                                        {{ __('common.edit') }}
                                    </a>
                                @endcan
                                @can('settings.taxes.delete')
                                    <x-admin::modal.trigger modal="deleteModal-{{ $tax->id }}" variant="open" class="inline-flex items-center text-sm font-semibold text-red-400 hover:text-red-500 cursor-pointer">{{ __('common.delete') }}</x-admin::modal.trigger>
                                @endcan
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div>
        {{ $taxes->links('admin::layouts.partials.pagination') }}
    </div>
    @can('settings.taxes.delete')
        @foreach ($taxes as $tax)
        <x-admin::modal.content
            modal="deleteModal-{{ $tax->id }}"
            variant="danger"
            size="xl"
            position="centered"
            title="{{ __('common.delete_modal_title') }}"
            description="{{ __('common.delete_modal_description', ['item' => $tax->name]) }}">
            <form action="{{ route('admin.settings.taxes.destroy', ['tax' => $tax->id]) }}" method="POST">
                @csrf
                @method('DELETE')
                <div class="flex justify-end gap-2 mt-4">
                    <x-admin::modal.trigger type="button" variant="close" class="bg-billmora-1 border-2 border-billmora-primary-500 hover:bg-billmora-primary-600 px-3 py-2 text-billmora-primary-500 hover:text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">{{ __('common.cancel') }}</x-admin::modal.trigger>
                    <button type="submit" class="bg-red-500 border-2 border-red-500 hover:bg-red-600 px-3 py-2 text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">{{ __('common.delete') }}</button>
                </div>
            </form>
        </x-admin::modal.content>
        @endforeach
    @endcan
</div>
@endsection
