@props([
  'modal',
  'size' => 'md',
  'variant' => 'primary',
  'position' => 'simple',
  'title',
  'description' => null,
])

<div
  x-cloak
  x-data
  x-show="$store.modal.open === '{{ $modal }}'"
  x-transition.opacity
  x-on:keydown.escape.window="$store.modal.close()"
  tabindex="0"
  class="fixed inset-0 z-110 flex justify-center items-center"
>
  <div class="fixed inset-0 bg-black/25 backdrop-blur-sm"></div>

  <div
    x-show="$store.modal.open === '{{ $modal }}'"
    x-transition
    @class([
      "w-full h-auto mx-2 xl:mx-0 bg-white rounded-xl shadow-md p-6 z-51",
      "max-w-sm" => $size === 'sm',
      "max-w-md" => $size === 'md',
      "max-w-lg" => $size === 'lg',
      "max-w-xl" => $size === 'xl',
      "max-w-2xl" => $size === '2xl',
      "max-w-3xl" => $size === '3xl',
      "max-w-4xl" => $size === '4xl',
      "max-w-5xl" => $size === '5xl',
      "max-w-6xl" => $size === '6xl',
      "max-w-7xl" => $size === '7xl',
    ])
    x-on:click.away="$store.modal.close()"
  >
    <div
      @class([
        "flex gap-4 relative mb-4",
        "items-start" => $position === 'simple',
        "flex-col justify-center text-center items-center" => $position === 'centered',
      ])
    >
      <div
        @class([
          "w-12 h-12 flex justify-center items-center rounded-full shrink-0",
          "bg-billmora-secondary" => $variant === 'primary',
          "bg-green-100" => $variant === 'success',
          "bg-yellow-100" => $variant === 'warning',
          "bg-red-100" => $variant === 'danger',
        ])
      >
        @switch($variant)
          @case('success')
            <x-lucide-check-circle class="w-auto h-7 text-green-500"/>
            @break
          @case('warning')
            <x-lucide-circle-alert class="w-auto h-7 text-yellow-500"/>
            @break
          @case('danger')
            <x-lucide-triangle-alert class="w-auto h-7 text-red-500"/>
            @break
          @default
            <x-lucide-info class="w-auto h-7 text-billmora-primary"/>
        @endswitch
      </div>

      <div @if ($position === 'simple') class="pr-8" @endif>
        <h3 class="text-xl text-slate-700 font-bold">{{ $title }}</h3>
        @if($description)
          <p class="text-slate-500">{{ $description }}</p>
        @endif
      </div>

      <div 
        x-on:click="$store.modal.close()"
        class="absolute top-0 right-0 bg-billmora-1 hover:bg-billmora-primary p-2.5 text-slate-600 hover:text-white rounded-full transition-colors duration-300 cursor-pointer">
        <x-lucide-x class="w-auto h-5"/>
      </div>
    </div>

    {{ $slot }}
  </div>
</div>
