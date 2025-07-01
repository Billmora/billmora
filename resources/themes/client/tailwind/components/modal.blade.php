@props(['modal', 'size' => 'md', 'variant' => 'primary', 'position' => 'simple', 'icon' => null, 'title', 'description' => null])

<div
    x-cloak
    x-data
    x-show="$store.modal.open === '{{ $modal }}'"
    x-transition.opacity
    x-on:keydown.escape.window="$store.modal.close()"
    tabindex="0"
    class="fixed flex items-center justify-center w-full h-full z-50"
  >
  <div class="fixed top-0 w-full h-full bg-black opacity-40 z-50"></div>
  @switch($size)
    @case('sm')
      <div
        x-show="$store.modal.open === '{{ $modal }}'"
        x-transition
        class=" w-full max-w-[18.75rem] h-auto mx-2 xl:mx-0 bg-billmora-1 rounded-xl shadow-md p-6 z-51"
        x-on:click.away="$store.modal.close()"
        >
        @switch($variant)
          @case('primary')     
            <div class="flex @if($position == 'simple') items-start @elseif ($position == 'centered') flex-col justify-center text-center items-center @endif gap-4 relative mb-4">
              <div class="w-12 h-12 bg-billmora-secondary flex justify-center items-center rounded-full shrink-0">
                <x-dynamic-component :component="$icon" class="w-7 h-7 text-billmora-primary"/>
              </div>
              <div @if ($position == 'simple') class="pr-8" @endif>
                <h3 class="text-xl text-slate-700 font-bold">{{ $title }}</h3>
                <p class="text-slate-500">{{ $description }}</p>
              </div>
              <x-lucide-x x-on:click="$store.modal.close()" class="absolute top-0 right-0 w-6 h-auto text-slate-400 cursor-pointer"/>
            </div>
            @break
          @case('success')
            <div class="flex @if($position == 'simple') items-start @elseif ($position == 'centered') flex-col justify-center text-center items-center @endif gap-4 relative mb-4">
              <div class="w-12 h-12 bg-green-100 flex justify-center items-center rounded-full shrink-0">
                <x-dynamic-component :component="$icon" class="w-7 h-7 text-green-500"/>
              </div>
              <div @if ($position == 'simple') class="pr-8" @endif>
                <h3 class="text-xl text-slate-700 font-bold">{{ $title }}</h3>
                <p class="text-slate-500">{{ $description }}</p>
              </div>
              <x-lucide-x x-on:click="$store.modal.close()" class="absolute top-0 right-0 w-6 h-auto text-slate-400 cursor-pointer"/>
            </div>
            @break
          @case('warning')
            <div class="flex @if($position == 'simple') items-start @elseif ($position == 'centered') flex-col justify-center text-center items-center @endif gap-4 relative mb-4">
              <div class="w-12 h-12 bg-yellow-100 flex justify-center items-center rounded-full shrink-0">
                <x-dynamic-component :component="$icon" class="w-7 h-7 text-yellow-500"/>
              </div>
              <div @if ($position == 'simple') class="pr-8" @endif>
                <h3 class="text-xl text-slate-700 font-bold">{{ $title }}</h3>
                <p class="text-slate-500">{{ $description }}</p>
              </div>
              <x-lucide-x x-on:click="$store.modal.close()" class="absolute top-0 right-0 w-6 h-auto text-slate-400 cursor-pointer"/>
            </div>
            @break
          @case('danger')
            <div class="flex @if($position == 'simple') items-start @elseif ($position == 'centered') flex-col justify-center text-center items-center @endif gap-4 relative mb-4">
              <div class="w-12 h-12 bg-red-100 flex justify-center items-center rounded-full shrink-0">
                <x-dynamic-component :component="$icon" class="w-7 h-7 text-red-500"/>
              </div>
              <div @if ($position == 'simple') class="pr-8" @endif>
                <h3 class="text-xl text-slate-700 font-bold">{{ $title }}</h3>
                <p class="text-slate-500">{{ $description }}</p>
              </div>
              <x-lucide-x x-on:click="$store.modal.close()" class="absolute top-0 right-0 w-6 h-auto text-slate-400 cursor-pointer"/>
            </div>
            @break
          @default
        @endswitch
        {{ $slot }}
      </div>
      @break
    @case('md')
      <div
        x-show="$store.modal.open === '{{ $modal }}'"
        x-transition
        class=" w-full max-w-[31.25rem] h-auto mx-2 xl:mx-0 bg-billmora-1 rounded-xl shadow-md p-6 z-51"
        x-on:click.away="$store.modal.close()"
        >
        @switch($variant)
          @case('primary')     
            <div class="flex @if($position == 'simple') items-start @elseif ($position == 'centered') flex-col justify-center text-center items-center @endif gap-4 relative mb-4">
              <div class="w-12 h-12 bg-billmora-secondary flex justify-center items-center rounded-full shrink-0">
                <x-dynamic-component :component="$icon" class="w-7 h-7 text-billmora-primary"/>
              </div>
              <div @if ($position == 'simple') class="pr-8" @endif>
                <h3 class="text-xl text-slate-700 font-bold">{{ $title }}</h3>
                <p class="text-slate-500">{{ $description }}</p>
              </div>
              <x-lucide-x x-on:click="$store.modal.close()" class="absolute top-0 right-0 w-6 h-auto text-slate-400 cursor-pointer"/>
            </div>
            @break
          @case('success')
            <div class="flex @if($position == 'simple') items-start @elseif ($position == 'centered') flex-col justify-center text-center items-center @endif gap-4 relative mb-4">
              <div class="w-12 h-12 bg-green-100 flex justify-center items-center rounded-full shrink-0">
                <x-dynamic-component :component="$icon" class="w-7 h-7 text-green-500"/>
              </div>
              <div @if ($position == 'simple') class="pr-8" @endif>
                <h3 class="text-xl text-slate-700 font-bold">{{ $title }}</h3>
                <p class="text-slate-500">{{ $description }}</p>
              </div>
              <x-lucide-x x-on:click="$store.modal.close()" class="absolute top-0 right-0 w-6 h-auto text-slate-400 cursor-pointer"/>
            </div>
            @break
          @case('warning')
            <div class="flex @if($position == 'simple') items-start @elseif ($position == 'centered') flex-col justify-center text-center items-center @endif gap-4 relative mb-4">
              <div class="w-12 h-12 bg-yellow-100 flex justify-center items-center rounded-full shrink-0">
                <x-dynamic-component :component="$icon" class="w-7 h-7 text-yellow-500"/>
              </div>
              <div @if ($position == 'simple') class="pr-8" @endif>
                <h3 class="text-xl text-slate-700 font-bold">{{ $title }}</h3>
                <p class="text-slate-500">{{ $description }}</p>
              </div>
              <x-lucide-x x-on:click="$store.modal.close()" class="absolute top-0 right-0 w-6 h-auto text-slate-400 cursor-pointer"/>
            </div>
            @break
          @case('danger')
            <div class="flex @if($position == 'simple') items-start @elseif ($position == 'centered') flex-col justify-center text-center items-center @endif gap-4 relative mb-4">
              <div class="w-12 h-12 bg-red-100 flex justify-center items-center rounded-full shrink-0">
                <x-dynamic-component :component="$icon" class="w-7 h-7 text-red-500"/>
              </div>
              <div @if ($position == 'simple') class="pr-8" @endif>
                <h3 class="text-xl text-slate-700 font-bold">{{ $title }}</h3>
                <p class="text-slate-500">{{ $description }}</p>
              </div>
              <x-lucide-x x-on:click="$store.modal.close()" class="absolute top-0 right-0 w-6 h-auto text-slate-400 cursor-pointer"/>
            </div>
            @break
          @default
        @endswitch
        {{ $slot }}
      </div>
      @break
    @case('lg')
      <div
        x-show="$store.modal.open === '{{ $modal }}'"
        x-transition
        class=" w-full max-w-[50rem] h-auto mx-2 xl:mx-0 bg-billmora-1 rounded-xl shadow-md p-6 z-51"
        x-on:click.away="$store.modal.close()"
        >
        @switch($variant)
          @case('primary')     
            <div class="flex @if($position == 'simple') items-start @elseif ($position == 'centered') flex-col justify-center text-center items-center @endif gap-4 relative mb-4">
              <div class="w-12 h-12 bg-billmora-secondary flex justify-center items-center rounded-full shrink-0">
                <x-dynamic-component :component="$icon" class="w-7 h-7 text-billmora-primary"/>
              </div>
              <div @if ($position == 'simple') class="pr-8" @endif>
                <h3 class="text-xl text-slate-700 font-bold">{{ $title }}</h3>
                <p class="text-slate-500">{{ $description }}</p>
              </div>
              <x-lucide-x x-on:click="$store.modal.close()" class="absolute top-0 right-0 w-6 h-auto text-slate-400 cursor-pointer"/>
            </div>
            @break
          @case('success')
            <div class="flex @if($position == 'simple') items-start @elseif ($position == 'centered') flex-col justify-center text-center items-center @endif gap-4 relative mb-4">
              <div class="w-12 h-12 bg-green-100 flex justify-center items-center rounded-full shrink-0">
                <x-dynamic-component :component="$icon" class="w-7 h-7 text-green-500"/>
              </div>
              <div @if ($position == 'simple') class="pr-8" @endif>
                <h3 class="text-xl text-slate-700 font-bold">{{ $title }}</h3>
                <p class="text-slate-500">{{ $description }}</p>
              </div>
              <x-lucide-x x-on:click="$store.modal.close()" class="absolute top-0 right-0 w-6 h-auto text-slate-400 cursor-pointer"/>
            </div>
            @break
          @case('warning')
            <div class="flex @if($position == 'simple') items-start @elseif ($position == 'centered') flex-col justify-center text-center items-center @endif gap-4 relative mb-4">
              <div class="w-12 h-12 bg-yellow-100 flex justify-center items-center rounded-full shrink-0">
                <x-dynamic-component :component="$icon" class="w-7 h-7 text-yellow-500"/>
              </div>
              <div @if ($position == 'simple') class="pr-8" @endif>
                <h3 class="text-xl text-slate-700 font-bold">{{ $title }}</h3>
                <p class="text-slate-500">{{ $description }}</p>
              </div>
              <x-lucide-x x-on:click="$store.modal.close()" class="absolute top-0 right-0 w-6 h-auto text-slate-400 cursor-pointer"/>
            </div>
            @break
          @case('danger')
            <div class="flex @if($position == 'simple') items-start @elseif ($position == 'centered') flex-col justify-center text-center items-center @endif gap-4 relative mb-4">
              <div class="w-12 h-12 bg-red-100 flex justify-center items-center rounded-full shrink-0">
                <x-dynamic-component :component="$icon" class="w-7 h-7 text-red-500"/>
              </div>
              <div @if ($position == 'simple') class="pr-8" @endif>
                <h3 class="text-xl text-slate-700 font-bold">{{ $title }}</h3>
                <p class="text-slate-500">{{ $description }}</p>
              </div>
              <x-lucide-x x-on:click="$store.modal.close()" class="absolute top-0 right-0 w-6 h-auto text-slate-400 cursor-pointer"/>
            </div>
            @break
          @default
        @endswitch
        {{ $slot }}
      </div>
      @break
    @case('xl')
      <div
        x-show="$store.modal.open === '{{ $modal }}'"
        x-transition
        class=" w-full max-w-[71.25rem] h-auto mx-2 xl:mx-0 bg-billmora-1 rounded-xl shadow-md p-6 z-51"
        x-on:click.away="$store.modal.close()"
        >
        @switch($variant)
          @case('primary')     
            <div class="flex @if($position == 'simple') items-start @elseif ($position == 'centered') flex-col justify-center text-center items-center @endif gap-4 relative mb-4">
              <div class="w-12 h-12 bg-billmora-secondary flex justify-center items-center rounded-full shrink-0">
                <x-dynamic-component :component="$icon" class="w-7 h-7 text-billmora-primary"/>
              </div>
              <div @if ($position == 'simple') class="pr-8" @endif>
                <h3 class="text-xl text-slate-700 font-bold">{{ $title }}</h3>
                <p class="text-slate-500">{{ $description }}</p>
              </div>
              <x-lucide-x x-on:click="$store.modal.close()" class="absolute top-0 right-0 w-6 h-auto text-slate-400 cursor-pointer"/>
            </div>
            @break
          @case('success')
            <div class="flex @if($position == 'simple') items-start @elseif ($position == 'centered') flex-col justify-center text-center items-center @endif gap-4 relative mb-4">
              <div class="w-12 h-12 bg-green-100 flex justify-center items-center rounded-full shrink-0">
                <x-dynamic-component :component="$icon" class="w-7 h-7 text-green-500"/>
              </div>
              <div @if ($position == 'simple') class="pr-8" @endif>
                <h3 class="text-xl text-slate-700 font-bold">{{ $title }}</h3>
                <p class="text-slate-500">{{ $description }}</p>
              </div>
              <x-lucide-x x-on:click="$store.modal.close()" class="absolute top-0 right-0 w-6 h-auto text-slate-400 cursor-pointer"/>
            </div>
            @break
          @case('warning')
            <div class="flex @if($position == 'simple') items-start @elseif ($position == 'centered') flex-col justify-center text-center items-center @endif gap-4 relative mb-4">
              <div class="w-12 h-12 bg-yellow-100 flex justify-center items-center rounded-full shrink-0">
                <x-dynamic-component :component="$icon" class="w-7 h-7 text-yellow-500"/>
              </div>
              <div @if ($position == 'simple') class="pr-8" @endif>
                <h3 class="text-xl text-slate-700 font-bold">{{ $title }}</h3>
                <p class="text-slate-500">{{ $description }}</p>
              </div>
              <x-lucide-x x-on:click="$store.modal.close()" class="absolute top-0 right-0 w-6 h-auto text-slate-400 cursor-pointer"/>
            </div>
            @break
          @case('danger')
            <div class="flex @if($position == 'simple') items-start @elseif ($position == 'centered') flex-col justify-center text-center items-center @endif gap-4 relative mb-4">
              <div class="w-12 h-12 bg-red-100 flex justify-center items-center rounded-full shrink-0">
                <x-dynamic-component :component="$icon" class="w-7 h-7 text-red-500"/>
              </div>
              <div @if ($position == 'simple') class="pr-8" @endif>
                <h3 class="text-xl text-slate-700 font-bold">{{ $title }}</h3>
                <p class="text-slate-500">{{ $description }}</p>
              </div>
              <x-lucide-x x-on:click="$store.modal.close()" class="absolute top-0 right-0 w-6 h-auto text-slate-400 cursor-pointer"/>
            </div>
            @break
          @default
        @endswitch
        {{ $slot }}
      </div>
      @break
    @default
  @endswitch
</div>