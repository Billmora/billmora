@props([
    'name',
    'label' => null,
    'value' => [],
    'error' => $errors->first($name),
    'required' => null,
    'helper' => null,
])

<div 
    class="w-full"
    x-data="{
        tags: @js(is_array($value) ? $value : []),
        input: '',
        errorVisible: {{ $error ? 'true' : 'false' }},
        addTag() {
            const val = this.input.trim()
            if (!val) return
            if (!this.tags.includes(val)) this.tags.push(val)
            this.input = ''
            this.errorVisible = false
        },
        removeTag(i) {
            this.tags.splice(i, 1)
            this.errorVisible = false
        }
    }"
>
    @if ($label)
        <div class="flex gap-1">
            <label for="{{ $name }}" class="block text-slate-600 font-semibold mb-0.5">
                {{ $label }}
            </label>
            <span class="text-slate-600">
                {{ $required ? __('admin/common.symbol_required') : __('admin/common.symbol_optional') }}
            </span>
        </div>
    @endif

    <div
        @class([
            'my-1 rounded-lg border-2 focus-within:ring-2 ring-billmora-primary',
            'border-red-400' => $error,
            'border-billmora-2' => !$error,
        ])
    >
        <input
            x-model="input"
            x-on:input="errorVisible = false"
            x-on:keydown.enter.prevent="addTag()"
            class="w-full bg-transparent px-3 py-2 text-slate-700 outline-none placeholder:text-slate-500 rounded-t-lg"
            {{ $attributes }}
        />

        <template x-if="tags.length > 0">
            <div class="flex flex-wrap gap-2 border-t-2 border-billmora-2 px-3 py-3">
                <template x-for="(tag, i) in tags" :key="i">
                    <span
                        class="inline-flex items-center gap-1 bg-billmora-2 px-3 py-1 text-sm text-slate-600 font-semibold rounded-md break-all max-w-full"
                    >
                        <span x-text="tag"></span>
                        <button
                            type="button"
                            x-on:click="removeTag(i)"
                            class="hover:text-red-400 cursor-pointer"
                        >
                            <x-lucide-x class="w-auto h-3" />
                        </button>
                        <input type="hidden" name="{{ $name }}[]" :value="tag">
                    </span>
                </template>
            </div>
        </template>
    </div>

    @if ($error)
        <p class="mt-1 text-sm text-red-400 font-semibold" x-show="errorVisible">
            {{ $error }}
        </p>
    @elseif ($helper)
        <p class="mt-1 text-sm text-slate-500">{{ $helper }}</p>
    @endif
</div>
