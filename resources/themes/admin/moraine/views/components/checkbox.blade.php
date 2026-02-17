@props([
    'name',
    'label' => null,
    'options' => [],
    'checked' => [],
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
    
    $oldChecked = old($errorKey, $checked);
    $resolvedChecked = is_array($oldChecked) ? $oldChecked : $checked;
@endphp

<div x-data="{ hasError: {{ $resolvedError ? 'true' : 'false' }} }" class="w-full">
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
    
    <div {{ $attributes }}>
        @foreach ($options as $value => $optionLabel)
            <div class="flex items-center gap-2 mb-1">
                <input
                    type="checkbox"
                    name="{{ $name }}[]"
                    id="{{ "{$name}_{$value}" }}"
                    value="{{ $value }}"
                    x-on:input="hasError = false"
                    @checked(in_array($value, $resolvedChecked))
                    @class([
                        'w-4 h-4 accent-billmora-primary text-red border-2 outline-none focus:ring-2 ring-billmora-primary',
                        'bg-billmora-1 cursor-not-allowed' => $attributes->has('disabled'),
                        'cursor-pointer' => !$attributes->has('disabled'),
                        'border-red-400' => $resolvedError,
                        'border-billmora-2' => !$resolvedError,
                    ])
                />
    
                <label for="{{ "{$name}_{$value}" }}" class="text-slate-600 font-semibold cursor-pointer">
                    {{ $optionLabel }}
                </label>
            </div>
        @endforeach
    </div>

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
