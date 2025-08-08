@props([
    'name',
    'type' => 'text',
    'label' => null,
    'value' => old($name),
    'error' => $errors->first($name),
    'required' => null,
    'helper' => null,
])

<div x-data="{ errorVisible: {{ $error ? 'true' : 'false' }} }" class="w-full">
    @if ($label)
        <div class="flex gap-1">
            <label for="{{ $name }}" class="block text-slate-600 font-semibold mb-0.5">
                {{ $label }}
            </label>
            <span class="text-slate-600">{{ $required ? __('admin/common.symbol_required') : __('admin/common.symbol_optional') }}</span>
        </div>
    @endif

    <div class="my-1">
        <input type="{{ $type }}" name="{{ $name }}" id="{{ $name }}" value="{{ $value }}"
            x-on:input="errorVisible = false"
            :class="[
                'w-full text-slate-700 rounded-lg px-3 py-2 border-2 border-billmora-2 outline-none focus:ring-2 ring-billmora-primary placeholder:text-slate-500 cursor-text',
                errorVisible ? 'border-red-400' : 'border-billmora-2'
            ]"
            {{ $attributes }} />
    </div>

    @if ($error)
        <p class="mt-1 text-sm text-red-400 font-semibold" x-show="errorVisible">{{ $error }}</p>
    @elseif ($helper)
        <p class="mt-1 text-sm text-slate-500">{{ $helper }}</p>
    @endif
</div>