@props(['id'])

<div 
  class="relative"
  x-data="{ isOpen: false }"
  x-on:keydown.esc.window="isOpen = false"
  x-on:click.away="isOpen = false">
  @isset($trigger)
    <div x-on:click="isOpen = ! isOpen">
      {{ $trigger }}
    </div>
  @endisset
  <div
      x-cloak
      x-show="isOpen"
      x-transition
      class="absolute top-11 right-0 flex min-w-[18rem] flex-col overflow-hidden rounded-xl border-3 border-billmora-3 bg-billmora-2 p-4">
    {{ $slot }}
  </div>
</div>
