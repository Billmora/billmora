@props([
    'name',
    'label' => null,
    'error' => null,
    'required' => false,
    'helper' => null,
])

@php
    /**
     * Normalize the HTML select name (bracket notation) into Laravel's validation key (dot notation).
     */
    $errorKey = preg_replace('/\[(.*?)\]/', '.$1', $name);
    $errorKey = preg_replace('/\.+/', '.', $errorKey);
    $errorKey = trim($errorKey, '.');

    $resolvedError = $error ?? $errors->first($errorKey);
@endphp

<div 
    x-data="{
        hasError: {{ $resolvedError ? 'true' : 'false' }},
    }" 
    class="w-full"
>
    @if ($label)
        <div class="flex gap-1 mb-1">
            <label 
                for="{{ $name }}" 
                class="text-slate-600 font-semibold"
            >
                {{ $label }}
            </label>
            <span class="text-slate-600">
                {{ $required ? __('common.symbol_required') : __('common.symbol_optional') }}
            </span>
        </div>
    @endif

    <div class="flex items-center w-full">
        <select
            name="{{ $name }}"
            id="{{ $name }}"
            x-on:change="hasError = false"
            :class="hasError ? 'border-red-400' : ''"
            @class([
                'w-full text-slate-700 rounded-lg px-3 py-2.5 border-2 border-billmora-2 outline-none focus:ring-2 ring-billmora-primary-500 appearance-none',
                'bg-billmora-1 cursor-not-allowed' => $attributes->has('disabled') && $attributes->get('disabled') !== false,
                'cursor-pointer' => !$attributes->has('disabled') || $attributes->get('disabled') === false,
            ])
            {{ $attributes }}
        >
            <option value="" class="text-slate-500" {{ !isset($attributes['wire:model']) ? 'selected' : '' }} {{ $required ? 'disabled' : '' }}>
                {{ __('common.choose_option') }}
            </option>
            {{ $slot }}
        </select>
        <x-lucide-chevrons-up-down class="w-auto h-5 -ml-7 text-slate-700 pointer-events-none" />
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