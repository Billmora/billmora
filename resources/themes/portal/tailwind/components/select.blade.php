@props(['label' => null, 'name' => null])

<div class="w-full">      
  <div class="relative">
    <select name="{{ $name }}" id="{{ $name }}" class="w-full bg-billmora-2 text-gray-700 text-sm border-2 border-billmora-3 rounded-md px-3 py-2.5 focus:outline-none focus:border-billmora-ring-1 hover:border-billmora-ring-1 appearance-none cursor-pointer">
      <option class="text-slate-400" selected disabled>Choose a {{ $label }}</option>
      {{ $slot }}
    </select>
    <x-lucide-chevrons-up-down class="h-4 w-auto absolute flex justify-center items-center top-[14px] right-[8px] text-gray-700 pointer-events-none" />
  </div>
</div>