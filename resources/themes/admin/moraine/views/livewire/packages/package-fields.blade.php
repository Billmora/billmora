<div>
    <div class="flex flex-col gap-4">
        <div class="flex flex-col md:flex-row gap-4 justify-between items-center">
            <a href="{{ route('admin.packages.fields.create', ['package' => $package->id]) }}"
                class="flex gap-1 items-center bg-billmora-primary-500 hover:bg-billmora-primary-600 px-3 py-2 ml-auto text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">
                <x-lucide-plus class="w-auto h-5" />
                {{ __('common.create') }}
            </a>
        </div>
        <div class="overflow-x-auto" data-sortable-wrapper>
            <div class="min-w-full inline-block align-middle">
                <div class="border-2 border-billmora-neutral-100 rounded-2xl overflow-hidden">
                    <table class="min-w-full divide-y divide-billmora-neutral-100">
                        <thead class="bg-billmora-neutral-100">
                            <tr>
                                <th scope="col" class="w-10 px-4 py-4"></th>
                                <th scope="col" class="px-6 py-4 text-start text-xs font-semibold text-slate-500 uppercase">{{ __('admin/packages.fields.label') }}</th>
                                <th scope="col" class="px-6 py-4 text-start text-xs font-semibold text-slate-500 uppercase">{{ __('admin/packages.fields.name') }}</th>
                                <th scope="col" class="px-6 py-4 text-start text-xs font-semibold text-slate-500 uppercase">{{ __('admin/packages.fields.type') }}</th>
                                <th scope="col" class="px-6 py-4 text-start text-xs font-semibold text-slate-500 uppercase">{{ __('admin/packages.fields.required') }}</th>
                                <th scope="col" class="px-6 py-4 text-end text-xs font-semibold text-slate-500 uppercase">{{ __('common.action') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y-2 divide-billmora-neutral-100 bg-white" data-sortable="PackageField">
                            @forelse ($fields as $field)
                                <tr data-id="{{ $field['id'] }}">
                                    <td class="px-4 py-4 whitespace-nowrap text-slate-300">
                                        <x-lucide-grip-vertical class="w-5 h-5 drag-handle cursor-move" />
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-800">{{ $field['label'] }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-800">{{ $field['name'] }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-800">{{ ucfirst($field['type']) }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-800">
                                        @if($field['required'])
                                            {{ __('common.enable') }}
                                        @else
                                            {{ __('common.disable') }}
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-end text-sm font-medium space-x-2">
                                        <a href="{{ route('admin.packages.fields.edit', ['package' => $package->id, 'field' => $field['id']]) }}"
                                            class="inline-flex items-center text-sm font-semibold text-billmora-primary-500 hover:text-billmora-primary-600 cursor-pointer">
                                            {{ __('common.edit') }}
                                        </a>
                                        <x-admin::modal.trigger modal="deleteFieldModal-{{ $field['id'] }}" variant="open" class="inline-flex items-center text-sm font-semibold text-red-400 hover:text-red-500 cursor-pointer">{{ __('common.delete') }}</x-admin::modal.trigger>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-8 text-center text-sm text-slate-400">
                                        {{ __('common.no_data') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    @foreach ($fields as $field)
        <x-admin::modal.content
            modal="deleteFieldModal-{{ $field['id'] }}"
            variant="danger"
            size="xl"
            position="centered"
            title="{{ __('common.delete_modal_title') }}"
            description="{{ __('common.delete_modal_description', ['item' => $field['label']]) }}">
            <form action="{{ route('admin.packages.fields.destroy', ['package' => $package->id, 'field' => $field['id']]) }}" method="POST">
                @csrf
                @method('DELETE')
                <div class="flex justify-end gap-2 mt-4">
                    <x-admin::modal.trigger type="button" variant="close" class="bg-billmora-neutral-50 border-2 border-billmora-primary-500 hover:bg-billmora-primary-600 px-3 py-2 text-billmora-primary-500 hover:text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">{{ __('common.cancel') }}</x-admin::modal.trigger>
                    <button type="submit" class="bg-red-500 border-2 border-red-500 hover:bg-red-600 px-3 py-2 text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">{{ __('common.delete') }}</button>
                </div>
            </form>
        </x-admin::modal.content>
    @endforeach


    
    @script
    <script>
        document.addEventListener('livewire:initialized', () => {
            const wrapper = document.querySelector('[data-sortable="PackageField"]');
            if (wrapper && typeof Sortable !== 'undefined') {
                Sortable.create(wrapper, {
                    handle: '.drag-handle',
                    animation: 150,
                    onEnd: function (evt) {
                        const items = Array.from(wrapper.children).map(row => row.getAttribute('data-id'));
                        @this.call('updateSortOrder', items);
                    }
                });
            }
        });
    </script>
    @endscript
</div>
