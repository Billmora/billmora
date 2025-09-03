@props([
    'name',
    'label' => null,
    'error' => $errors->first($name),
    'required' => null,
    'helper' => null,
    'checked' => false,
])

<div 
    x-data="{ errorVisible: {{ $error ? 'true' : 'false' }} }" 
    class="w-full"
>
    @if ($label)
        <div class="flex gap-1">
            <label for="{{ $name }}" class="block text-slate-600 font-semibold mb-0.5">
                {{ $label }}
            </label>
            <span class="text-slate-600">
                {{ $required ? __('admin/common.symbol_required') : __('admin/common.symbol_optional') }}
            </span>
        </div>
    @endif

    <label class="inline-flex items-center cursor-pointer my-1">
        <input type="hidden" name="{{ $name }}" value="0">
        <input 
            type="checkbox" 
            name="{{ $name }}" 
            id="{{ $name }}" 
            value="1"
            class="sr-only peer"
            @checked(old($name, $checked))
        >
        <div
            class="relative w-11 h-6 rounded-full bg-billmora-2 peer peer-checked:bg-billmora-primary after:absolute after:top-0.5 after:start-[2px] after:h-5 after:w-5 after:rounded-full after:bg-white after:border after:border-billmora-2 after:transition-all peer-checked:after:translate-x-full peer-checked:after:border-billmora-primary"
        ></div>
    </label>

    @if ($error)
        <p class="mt-1 text-sm text-red-400 font-semibold" x-show="errorVisible">
            {{ $error }}
        </p>
    @elseif ($helper)
        <p class="mt-1 text-sm text-slate-500">{{ $helper }}</p>
    @endif
</div>
