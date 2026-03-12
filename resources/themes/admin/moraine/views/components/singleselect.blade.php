@props([
    'name',
    'label' => null,
    'options' => [],
    'selected' => null,
    'error' => $errors->first($name),
    'required' => null,
    'helper' => null,
])

<div 
    x-data="{
        open: false,
        search: '',
        selected: @js($selected) !== null && @js($selected) !== undefined 
            ? String(@js($selected)) 
            : null,
        errorVisible: {{ $error ? 'true' : 'false' }},
        select(value) {
            this.selected = value;
            this.open = false;
            this.search = '';
            this.errorVisible = false;
            $dispatch('picked', { name: '{{ $name }}', value: value });
        },
        clear() {
            this.selected = null;
            this.errorVisible = false;
            $dispatch('picked', { name: '{{ $name }}', value: value });
        },
        isSelected(value) {
            return this.selected === value;
        },
        getSelectedTitle() {
            const option = this.options.find(o => o.value === this.selected);
            return option ? option.title : '';
        },
        filteredOptions() {
            if (this.search === '') return this.options;
            const q = this.search.toLowerCase();
            return this.options.filter(o =>
                (o.title ?? '').toLowerCase().includes(q) ||
                (o.subtitle ?? '').toLowerCase().includes(q)
            );
        },
        options: @js($options).map(o => ({ ...o, value: String(o.value) }))
    }"
    class="w-full"
>
    @if ($label)
        <div class="flex gap-1">
            <label for="{{ $name }}" class="block text-slate-600 font-semibold mb-0.5">
                {{ $label }}
            </label>
            <span class="text-slate-600">
                {{ $required ? __('common.symbol_required') : __('common.symbol_optional') }}
            </span>
        </div>
    @endif
    <div class="relative my-1">
        <div 
            tabindex="0"
            x-on:click="open = !open"
            @class([
                'cursor-pointer border-2 px-3 py-2 bg-white text-slate-700 rounded-lg focus-within:ring-2 ring-billmora-primary',
                'border-red-400' => $error,
                'border-billmora-2' => !$error,
            ])
        >
            <template x-if="!selected">
                <span class="text-slate-500">{{ __('common.choose_option') }}</span>
            </template>
            <template x-if="selected">
                <div class="flex items-center justify-between gap-2">
                    <span class="inline-flex items-center gap-1 bg-billmora-2 px-2 py-1 text-sm text-slate-600 font-semibold rounded-md break-all max-w-full">
                        <span x-text="getSelectedTitle()"></span>
                    </span>
                    <button 
                        type="button" 
                        x-on:click.stop="clear()" 
                        class="hover:text-red-400 cursor-pointer flex-shrink-0"
                    >
                        <x-lucide-x class="w-auto h-4" />
                    </button>
                </div>
            </template>
        </div>
        <div 
            x-show="open"
            x-on:click.away="open = false"
            class="absolute z-10 mt-1 w-full bg-white border-2 border-billmora-2 rounded-xl p-3"
        >
            <input 
                type="text"
                placeholder="{{ __('admin/common.search') }}"
                x-model="search"
                x-on:input="errorVisible = false"
                class="w-full mb-2 text-slate-700 rounded-lg px-3 py-2 border-2 border-billmora-2 outline-none focus:ring-2 ring-billmora-primary placeholder:text-slate-500"
            />
            <ul class="max-h-50 overflow-y-auto">
                <template x-for="option in filteredOptions()" :key="option.value">
                    <li 
                        x-on:click="select(option.value)"
                        class="flex items-center justify-between px-3 py-2 rounded-lg cursor-pointer transition ease-in-out duration-150"
                        :class="isSelected(option.value) 
                            ? 'bg-billmora-primary text-white' 
                            : 'text-slate-700 hover:bg-billmora-primary hover:text-white'"
                    >
                        <div class="grid gap-1">
                            <span x-text="option.title" class="font-semibold"></span>
                            <span x-text="option.subtitle"></span>
                        </div>
                        <template x-if="isSelected(option.value)">
                            <x-lucide-check class="w-auto h-5" />
                        </template>
                    </li>
                </template>
            </ul>
        </div>
    </div>
    <input type="hidden" name="{{ $name }}" x-model="selected">
    @if ($error)
        <p class="mt-1 text-sm text-red-400 font-semibold" x-show="errorVisible">
            {{ $error }}
        </p>
    @elseif ($helper)
        <p class="mt-1 text-sm text-slate-500">{{ $helper }}</p>
    @endif
</div>