@props([
  'label' => null,
  'name' => null,
  'required' => null,
  ])

<div class="w-full">      
  @if ($label)
    <label for="{{ $name }}" class="block text-slate-500 font-semibold mb-0.5">
      {{ $label }}
      @if ($required)
        <span class="text-red-500">{{ __('auth.required_symbol') }}</span> 
      @else
        <span class="text-slate-400">{{ __('auth.optional_symbol') }}</span>
      @endif
    </label>
  @endif
  <div class="relative">
    <select name="{{ $name }}" id="{{ $name }}" class="w-full bg-billmora-2 text-gray-700 border-2 border-billmora-3 rounded-lg px-3 py-2 focus:outline-none focus:border-billmora-ring-1 hover:border-billmora-ring-1 appearance-none cursor-pointer">
      <option class="text-slate-400" selected disabled>Choose a {{ $label }}</option>
      {{ $slot }}
    </select>
    <x-lucide-chevrons-up-down class="h-4 w-auto absolute flex justify-center items-center top-[14px] right-[8px] text-gray-700 pointer-events-none" />
  </div>
</div>