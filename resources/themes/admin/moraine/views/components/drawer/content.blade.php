@props([
  'drawer',
  'title',
  'description' => null,
  'action' => '',
  'method' => 'GET'
])

{{-- Backdrop overlay --}}
<div
  x-data
  x-cloak
  x-show="$store.drawer.open === '{{ $drawer }}'"
  x-transition:enter="transition ease-out duration-200"
  x-transition:enter-start="opacity-0"
  x-transition:enter-end="opacity-100"
  x-transition:leave="transition ease-in duration-150"
  x-transition:leave-start="opacity-100"
  x-transition:leave-end="opacity-0"
  class="fixed inset-0 bg-black/25 backdrop-blur-sm z-100"
  x-on:click="$store.drawer.close()"
  x-on:keydown.escape.window="$store.drawer.close()"
></div>

{{-- Drawer Panel --}}
<div
  x-data
  x-cloak
  x-show="$store.drawer.open === '{{ $drawer }}'"
  x-transition:enter="transition ease-out duration-300 transform"
  x-transition:enter-start="translate-x-full"
  x-transition:enter-end="translate-x-0"
  x-transition:leave="transition ease-in duration-200 transform"
  x-transition:leave-start="translate-x-0"
  x-transition:leave-end="translate-x-full"
  class="fixed top-0 right-0 h-full w-full max-w-md bg-white shadow-xl flex flex-col z-110"
  x-on:keydown.escape.window="$store.drawer.close()"
>
  {{-- Header --}}
  <div class="flex items-center justify-between p-6 border-b border-billmora-neutral-100">
    <div>
      <h3 class="text-xl text-slate-700 font-bold">{{ $title }}</h3>
      @if($description)
        <p class="text-sm text-slate-500 mt-1">{{ $description }}</p>
      @endif
    </div>
    <button
      type="button"
      x-on:click="$store.drawer.close()"
      class="bg-billmora-neutral-50 hover:bg-billmora-primary-500 p-2.5 text-slate-600 hover:text-white rounded-full transition-colors duration-300 cursor-pointer"
    >
      <x-lucide-x class="w-auto h-5"/>
    </button>
  </div>

  {{-- Form content --}}
  <form action="{{ $action }}" method="{{ $method }}" class="flex-1 flex flex-col overflow-hidden m-0">
    <div class="flex-1 overflow-y-auto p-6 space-y-5">
      {{ $slot }}
    </div>

    {{-- Footer actions --}}
    <div class="p-6 border-t border-billmora-neutral-100 bg-white flex gap-3">
      <a href="{{ $action }}" class="px-5 py-2.5 w-full text-center rounded-xl font-semibold text-sm text-slate-600 bg-white border border-billmora-neutral-200 hover:bg-slate-50 transition-colors cursor-pointer">
        {{ __('common.clear_all') }}
      </a>
      <button type="submit" class="px-5 py-2.5 w-full rounded-xl font-semibold text-sm text-white bg-billmora-primary-500 hover:bg-billmora-primary-600 transition-colors cursor-pointer">
        {{ __('common.apply') }}
      </button>
    </div>
  </form>
</div>
