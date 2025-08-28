@props([
    'name',
    'label' => null,
    'options' => [],
    'selected' => [],
    'error' => $errors->first($name),
    'required' => null,
    'helper' => null,
])

<div x-data="{
    open: false,
    search: '',
    selected: @js($selected),
    errorVisible: {{ $error ? 'true' : 'false' }},
    toggle(id) {
        if (this.selected.includes(id)) {
            this.selected = this.selected.filter(i => i !== id);
        } else {
            this.selected.push(id);
        }
        this.errorVisible = false
    },
    isSelected(id) {
        return this.selected.includes(id);
    },
    filteredOptions() {
        return this.search === ''
            ? this.options
            : this.options.filter(o =>
                o.label.toLowerCase().includes(this.search.toLowerCase())
              );
    },
    options: @js($options)
}" class="w-full">
    @if ($label)
        <div class="flex gap-1">
            <label for="{{ $name }}" class="block text-slate-600 font-semibold mb-0.5">
                {{ $label }}
            </label>
            <span class="text-slate-600">{{ $required ? __('admin/common.symbol_required') : __('admin/common.symbol_optional') }}</span>
        </div>
    @endif

    <div class="relative my-1">
        <div @click="open = !open" tabindex="0"
             class="cursor-pointer border-2 px-3 py-2 bg-white text-slate-700 rounded-lg focus-within:ring-2 ring-billmora-primary"
             :class="errorVisible ? 'border-red-400' : 'border-billmora-2'">
             <template x-if="selected.length === 0">
                <span class="text-slate-500">{{ __('admin/common.choose_option') }}</span>
             </template>
            <div class="flex flex-wrap gap-2">
                <template x-for="id in selected" :key="id">
                    <span class="inline-flex items-center gap-1 bg-billmora-2 px-2 py-1 text-sm text-slate-600 font-semibold rounded-md break-all max-w-full">
                        <span x-text="options.find(o => o.id === id)?.label"></span>
                        <button type="button" @click.stop="toggle(id)" class="hover:text-red-400 cursor-pointer">
                            <x-lucide-x class="w-auto h-3" />
                        </button>
                    </span>
                </template>
            </div>
        </div>

        <div x-show="open" @click.away="open = false"
             class="absolute z-10 mt-1 w-full bg-white border-2 border-billmora-2 rounded-xl p-3">
            <input type="text" placeholder="Search..."
                   x-model="search"
                   @input="errorVisible = false"
                   class="w-full mb-2 text-slate-700 rounded-lg px-3 py-2 border-2 border-billmora-2 outline-none focus:ring-2 ring-billmora-primary placeholder:text-slate-500"/>

            <ul class="max-h-50 overflow-y-auto">
                <template x-for="option in filteredOptions()" :key="option.id">
                    <li @click="toggle(option.id)"
                        class="flex items-center justify-between px-3 py-2 rounded-lg cursor-pointer transition ease-in-out duration-150"
                        :class="isSelected(option.id) ? 'bg-billmora-primary text-white' : 'hover:bg-billmora-primary hover:text-white text-slate-700'">
                        <span x-text="option.label"></span>
                        <template x-if="isSelected(option.id)">
                            <x-lucide-check class="w-auto h-5" />
                        </template>
                    </li>
                </template>
            </ul>
        </div>
    </div>

    <template x-for="id in selected" :key="id">
        <input type="hidden" name="{{ $name }}[]" :value="id">
    </template>

    @if ($error)
        <p class="mt-1 text-sm text-red-400 font-semibold" x-show="errorVisible">{{ $error }}</p>
    @elseif ($helper)
        <p class="mt-1 text-sm text-slate-500">{{ $helper }}</p>
    @endif
</div>
