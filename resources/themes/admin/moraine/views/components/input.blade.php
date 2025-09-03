@props([
    'name',
    'type' => 'text',
    'label' => null,
    'value' => null,
    'error' => $errors->first($name),
    'required' => false,
    'helper' => null,
])

<div x-data="{ errorVisible: {{ $error ? 'true' : 'false' }} }" class="w-full">
    @if ($label)
        <div class="flex gap-1 mb-1">
            <label for="{{ $name }}" class="text-slate-600 font-semibold">
                {{ $label }}
            </label>
            <span class="text-slate-600">
                {{ $required ? __('admin/common.symbol_required') : __('admin/common.symbol_optional') }}
            </span>
        </div>
    @endif

    <input
        type="{{ $type }}"
        name="{{ $name }}"
        id="{{ $name }}"
        value="{{ $value }}"
        x-on:input="errorVisible = false"
        @class([
            'w-full px-3 py-2 rounded-lg border-2 outline-none text-slate-700 placeholder:text-slate-500 focus:ring-2 ring-billmora-primary',
            'bg-billmora-1 cursor-not-allowed' => $attributes->has('disabled'),
            'cursor-text' => !$attributes->has('disabled'),
            'border-red-400' => $error,
            'border-billmora-2' => !$error,
        ])
        {{ $attributes }}
    />

    @if ($error)
        <p class="mt-1 text-sm text-red-400 font-semibold" x-show="errorVisible">
            {{ $error }}
        </p>
    @elseif ($helper)
        <p class="mt-1 text-sm text-slate-500">{{ $helper }}</p>
    @endif
</div>
