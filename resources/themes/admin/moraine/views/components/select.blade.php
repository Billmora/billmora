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
            @if ($required)
                <span class="text-slate-600">{{ __('admin/common.symbol_required') }}</span>
            @else
                <span class="text-slate-600">{{ __('admin/common.symbol_optional') }}</span>
            @endif
        </div>
    @endif

    <div class="relative my-1">
        <select name="{{ $name }}" id="{{ $name }}" placeholder="anjay"
            x-on:select="errorVisible = false"
            :class="[
                'w-full text-slate-700 rounded-lg px-3 py-2.5 border-2 border-billmora-2 outline-none focus:ring-2 ring-billmora-primary appearance-none cursor-pointer',
                errorVisible ? 'border-red-400' : 'border-billmora-2'
            ]"
            {{ $attributes }}>
            <option class="text-slate-500" selected disabled>{{ __('admin/common.choose_option') }}</option>
            {{ $slot }}
        </select>
        <x-lucide-chevrons-up-down
            class="w-auto h-5 absolute top-1/2 right-2 -translate-y-1/2 text-slate-700 pointer-events-none" />
    </div>

    <template x-if="errorVisible">
        <p class="mt-1 text-sm text-red-400 font-semibold">{{ $error }}</p>
    </template>

    <template x-if="!errorVisible && {{ $helper ? 'true' : 'false' }}">
        <p class="mt-1 text-sm text-slate-500">{{ $helper }}</p>
    </template>
</div>
