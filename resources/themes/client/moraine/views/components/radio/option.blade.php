@props([
    'name',
    'label' => null,
    'value' => null,
    'checked' => false,
])

@php
    /**
     * Normalize the HTML input name (bracket notation) into Laravel's validation key (dot notation).
     */
    $errorKey = preg_replace('/\[(.*?)\]/', '.$1', $name);
    $errorKey = preg_replace('/\.+/', '.', $errorKey);
    $errorKey = trim($errorKey, '.');

    $resolvedValue = old($errorKey, $checked);
@endphp

<div class="inline-flex items-center gap-2 w-full">
    <label for="{{ $name }}-{{ $value }}" class="relative flex items-center cursor-pointer">
        <input
            type="radio"
            name="{{ $name }}"
            id="{{ $name }}-{{ $value }}"
            value="{{ $value }}"
            x-on:change="hasError = false"
            class="peer h-5 w-5 cursor-pointer appearance-none rounded-full border-2 border-billmora-2 checked:border-billmora-primary-500 transition"
            @checked($resolvedValue == $value)
        />
        <span
            class="absolute w-3 h-3 rounded-full bg-billmora-primary-500 opacity-0 peer-checked:opacity-100 transition-opacity duration-200 top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2">
        </span>
    </label>

    <label for="{{ $name }}-{{ $value }}" class="text-slate-600 cursor-pointer">
        {{ $label }}
    </label>
</div>