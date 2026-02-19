@props([
    'name',
    'label' => null,
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
@endphp

<div x-data="{ hasError: {{ $resolvedError ? 'true' : 'false' }} }" class="w-full">
    @if ($label)
        <label for="{{ $name }}" class="flex gap-1 mb-2 text-slate-600 font-semibold">
            {{ $label }}
            <span class="font-normal">
                {{ $required ? __('common.symbol_required') : __('common.symbol_optional') }}
            </span>
        </label>
    @endif

    <div class="flex flex-col gap-2">
        {{ $slot }}
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
