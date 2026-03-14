@props([
    'name',
    'label' => null,
    'options' => [],
    'value' => null,
    'error' => null,
    'required' => false,
    'helper' => null,
])

@php
    /**
     * Normalize the HTML input name (bracket notation) into Laravel's validation key (dot notation).
     */
    $errorKey = preg_replace('/\[(.*?)\]/', '.$1', $name);
    $errorKey = preg_replace('/\.+/', '.', $errorKey);
    $errorKey = trim($errorKey, '.');

    $resolvedError = $error ?? $errors->first($errorKey);
    $resolvedValue = old($errorKey, $value);
@endphp

<div x-data="{
        sliderIdx: 0,
        options: {{ json_encode(array_values($options)) }},
        hasError: {{ $resolvedError ? 'true' : 'false' }},
        init() {
            if (this.options.length === 0) return;
            let found = this.options.findIndex(o => o.value == '{{ $resolvedValue }}');
            this.sliderIdx = found !== -1 ? found : 0;
        },
        updateSelection() {
            this.hasError = false;
        }
    }"
    class="w-full">

    @if ($label)
        <div class="flex gap-1 mb-1">
            <label for="{{ $name }}" class="text-slate-600 font-semibold">
                {{ $label }}
            </label>
            <span class="text-slate-600">
                {{ $required ? __('common.symbol_required') : __('common.symbol_optional') }}
            </span>
        </div>
    @endif

    <div class="relative mb-10">
        <input 
            type="range"
            min="0"
            :max="Math.max(0, options.length - 1)"
            step="1"
            x-model="sliderIdx"
            x-on:input="updateSelection"
            class="w-full h-2 cursor-pointer accent-billmora-primary-500"
            :class="hasError ? 'border-red-400' : ''"
        />

        <template x-for="(option, i) in options" :key="option.value">
            <span
                class="absolute -bottom-10 flex flex-col gap-0.5"
                :class="{
                    'start-0 items-start': i === 0,
                    'end-0 items-end': i === options.length - 1,
                    'start-1/2 -translate-x-1/2 items-center': i > 0 && i < options.length - 1
                }"
                x-show="i === 0 || i === options.length - 1 || options.length <= 3"
            >
                <span class="text-sm font-semibold text-slate-700" x-text="option.title"></span>
                <span class="text-xs text-slate-500" x-text="option.subtitle" x-show="!!option.subtitle"></span>
            </span>
        </template>
    </div>

    <input type="hidden" name="{{ $name }}" :value="options[sliderIdx]?.value || ''">

    @if ($resolvedError)
        <p class="mt-1 text-sm text-red-400 font-semibold" x-show="hasError">
            {{ $resolvedError }}
        </p>
    @endif

    @if ($helper)
        <p class="mt-1 text-sm text-slate-500" x-show="!hasError">
            {{ $helper }}
        </p>
    @endif
</div>