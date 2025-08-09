@props([
    'name',
    'label' => null,
    'value' => null,
    'checked' => false,
])

<div class="w-full inline-flex items-center gap-2">
    <label class="relative flex items-center cursor-pointer">
        <input type="radio"
            name="{{ $name }}"
            id="{{ $value }}"
            value="{{ $value }}"
            class="peer h-5 w-5 cursor-pointer appearance-none rounded-full border-3 border-billmora-2 checked:border-billmora-primary transition-all" 
            @checked(old($name) !== null ? old($name) == $value : $checked) />
    
        <span
            class="absolute bg-billmora-primary w-3 h-3 rounded-full opacity-0 peer-checked:opacity-100 transition-opacity duration-200 top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2"></span>
    </label>
    <label class="text-slate-500 cursor-pointer" for="{{ $value }}">{{ $label }}</label>
</div>