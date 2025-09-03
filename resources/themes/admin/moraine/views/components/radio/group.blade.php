@props([
    'name',
    'label' => null,
    'error' => $errors->first($name),
    'required' => false,
    'helper' => null,
])

<div x-data="{ errorVisible: {{ $error ? 'true' : 'false' }} }" class="w-full">
    @if ($label)
        <label for="{{ $name }}" class="flex gap-1 mb-2 text-slate-600 font-semibold">
            {{ $label }}
            <span class="font-normal">
                {{ $required ? __('admin/common.symbol_required') : __('admin/common.symbol_optional') }}
            </span>
        </label>
    @endif

    <div class="flex flex-col gap-2">
        {{ $slot }}
    </div>

    @if ($error)
        <p class="mt-1 text-sm text-red-400 font-semibold" x-show="errorVisible">
            {{ $error }}
        </p>
    @elseif ($helper)
        <p class="mt-1 text-sm text-slate-500">{{ $helper }}</p>
    @endif
</div>
