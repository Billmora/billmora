@props([
    'name',
    'type' => 'text',
    'label' => null,
    'value' => null,
    'error' => null,
    'required' => false,
    'helper' => null,
    'placeholder' => null,
])

@php
    /**
     * Normalize the HTML input name (bracket notation) into Laravel's validation key (dot notation).
     */
    $errorKey = preg_replace('/\[(.*?)\]/', '.$1', $name);
    $errorKey = preg_replace('/\.+/', '.', $errorKey);
    $errorKey = trim($errorKey, '.');

    $resolvedError = $error ?? $errors->first($errorKey);

    $resolvedValue = $value;
    if ($type !== 'file') {
        $resolvedValue = old($errorKey, $value);
    }
@endphp

<div
    x-data="{
        hasError: {{ $resolvedError ? 'true' : 'false' }},
    }"
    class="w-full"
>
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

    <input
        type="{{ $type }}"
        name="{{ $name }}"
        id="{{ $name }}"
        placeholder="{{ $placeholder }}"
        value="{{ $resolvedValue }}"
        x-on:input="hasError = false"
        :class="hasError ? 'border-red-400' : ''"
        @class([
            'w-full px-3 py-2 rounded-lg border-2 border-billmora-2 outline-none text-slate-700 placeholder:text-slate-500 focus:ring-2 ring-billmora-primary-500',
            'bg-billmora-1 cursor-not-allowed' => $attributes->has('disabled') && $attributes->get('disabled') !== false,
            'cursor-pointer' => ($type === 'file') && (!$attributes->has('disabled') || $attributes->get('disabled') === false),
            'cursor-text' => ($type !== 'file') && (!$attributes->has('disabled') || $attributes->get('disabled') === false),
        ])
        {{ $attributes }}
    />

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