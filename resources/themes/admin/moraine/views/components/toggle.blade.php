@props([
    'name',
    'label' => null,
    'value' => null,
    'error' => $errors->first($name),
    'required' => null,
    'helper' => null,
    'checked' => false,
])

<div x-data="{ errorVisible: {{ $error ? 'true' : 'false' }} }" class="w-full">
    @if ($label)
        <div class="flex gap-1">
            <label for="{{ $name }}" class="block text-slate-600 font-semibold mb-0.5">
                {{ $label }}
            </label>
            <span
                class="text-slate-600">{{ $required ? __('admin/common.symbol_required') : __('admin/common.symbol_optional') }}</span>
        </div>
    @endif

    <label class="inline-flex items-center cursor-pointer">
        <input type="hidden" name="{{ $name }}" value="0">
        <input type="checkbox" name="{{ $name }}" id="{{ $name }}" value="{{ $value }}"
            class="sr-only peer" @checked($checked)>
        <div
            class="relative w-11 h-6 bg-billmora-2 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-billmora-primary after:content-[''] after:absolute after:top-0.5 after:start-[2px] after:bg-white after:border-billmora-2 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-billmora-primary">
        </div>
    </label>

    @if ($error)
        <p class="mt-1 text-sm text-red-400 font-semibold" x-show="errorVisible">{{ $error }}</p>
    @elseif ($helper)
        <p class="mt-1 text-sm text-slate-500">{{ $helper }}</p>
    @endif
</div>
