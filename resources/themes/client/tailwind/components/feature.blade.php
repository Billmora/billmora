@props(['icon' => null, 'title', 'description'])

<div class="flex flex-col bg-billmora-2 border-3 border-billmora-3 rounded-2xl p-6 md:max-w-[22rem] lg:max-w-100">
  <div class="flex gap-2 items-center">
    @if ($icon)
    <div class="bg-billmora-secondary p-2 rounded-lg">
      <x-dynamic-component :component="$icon" class="h-7 w-auto text-billmora-primary"/>
    </div>
    @endif
    <h3 class="text-billmora-primary text-xl font-semibold">{{ $title }}</h3>
  </div>
  <p class="text-slate-700 text-base mt-6">{{ $description }}</p>
</div>