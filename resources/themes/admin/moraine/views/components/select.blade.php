@props([
    'name',
    'label' => null,
    'error' => $errors->first($name),
    'required' => null,
    'helper' => null
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

    <div class="flex items-center w-full my-1">
        <select name="{{ $name }}" id="{{ $name }}"
            x-on:select="errorVisible = false"
            :class="[
                'w-full {{ $attributes->has('disabled') ? 'bg-billmora-1 cursor-not-allowed' : 'cursor-pointer' }} text-slate-700 rounded-lg px-3 py-2.5 border-2 border-billmora-2 outline-none focus:ring-2 ring-billmora-primary appearance-none',
                errorVisible ? 'border-red-400' : 'border-billmora-2'
            ]"
            {{ $attributes }}>
            <option class="text-slate-500" selected disabled>{{ __('admin/common.choose_option') }}</option>
            {{ $slot }}
        </select>
        <x-lucide-chevrons-up-down class="w-auto h-5 -ml-7 text-slate-700 pointer-events-none" />
    </div>

    @if ($error)
        <p class="mt-1 text-sm text-red-400 font-semibold" x-show="errorVisible">{{ $error }}</p>
    @elseif ($helper)
        <p class="mt-1 text-sm text-slate-500">{{ $helper }}</p>
    @endif
</div>
