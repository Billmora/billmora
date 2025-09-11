@props([
    'name',
    'label' => null,
    'options' => [],
    'checked' => [],
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
                    x-on:input="errorVisible = false"
                    @checked(in_array($value, $checked))
                    @class([
                        'w-4 h-4 accent-billmora-primary text-red border-2 outline-none focus:ring-2 ring-billmora-primary',
                        'bg-billmora-1 cursor-not-allowed' => $attributes->has('disabled'),
                        'cursor-pointer' => !$attributes->has('disabled'),
                        'border-red-400' => $error,
                        'border-billmora-2' => !$error,
                    ])
                />
    
                <label for="{{ "{$name}_{$value}" }}" class="text-slate-600 font-semibold cursor-pointer">
                    {{ $optionLabel }}
                </label>
            </div>
        @endforeach
    </div>

    @if ($error)
        <p class="mt-1 text-sm text-red-400 font-semibold" x-show="errorVisible">
            {{ $error }}
        </p>
    @elseif ($helper)
        <p class="mt-1 text-sm text-slate-500">{{ $helper }}</p>
    @endif
</div>
