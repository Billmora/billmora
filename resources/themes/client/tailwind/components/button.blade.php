@props(['variant' => 'primary', 'icon' => null, 'active' => false, 'modal' => null])

@switch($variant)
  @case('primary')
    <button
      {{ $attributes->class("
        flex items-center gap-2 px-3 py-2 rounded-lg text-white transition-colors duration-300 ease-in-out cursor-pointer focus:outline-none focus:ring-3 focus:ring-billmora-ring-1
        " . ($active
            ? ' bg-billmora-primary text-white'
            : ' bg-billmora-primary hover:bg-billmora-primary-hover hover:text-white')
        )
      }}
      @if ($modal)
        x-data
        x-on:click="$store.modal.show('{{ $modal }}')"
      @endif
    >
      @if ($icon)
        <x-dynamic-component :component="$icon" class="h-5.25 w-auto" />
      @endif
      {{ $slot }}
    </button>
    @break
  @case('secondary')
    <button
      {{ $attributes->class("
        flex items-center gap-2 px-3 py-2 rounded-lg transition-colors duration-300 ease-in-out cursor-pointer focus:outline-none focus:ring-3 focus:ring-billmora-ring-1
        " . ($active
            ? ' bg-billmora-secondary-hover text-white'
            : ' text-billmora-primary bg-billmora-secondary hover:bg-billmora-secondary-hover hover:text-white')
        )
      }}
      @if ($modal)
        x-data
        x-on:click="$store.modal.show('{{ $modal }}')"
      @endif
    >
      @if ($icon)
        <x-dynamic-component :component="$icon" class="h-5.25 w-auto" />
      @endif
      {{ $slot }}
    </button>
    @break
  @case('text')
    <button
      {{ $attributes->class("
        flex items-center gap-2 px-3 py-2 rounded-lg transition-colors duration-300 ease-in-out cursor-pointer focus:outline-none focus:ring-3 focus:ring-billmora-ring-1
        " . ($active
            ? ' bg-billmora-secondary text-billmora-primary'
            : ' text-slate-700 hover:bg-billmora-secondary hover:text-billmora-primary')
        )
      }}
      @if ($modal)
        x-data
        x-on:click="$store.modal.show('{{ $modal }}')"
      @endif
    >
      @if ($icon)
        <x-dynamic-component :component="$icon" class="h-5.25 w-auto" />
      @endif
      {{ $slot }}
    </button>
    @break
  @case('danger')
    <button
      {{ $attributes->class("
        flex items-center gap-2 px-3 py-2 rounded-lg text-white transition-colors duration-300 ease-in-out cursor-pointer focus:outline-none focus:ring-3 focus:ring-billmora-ring-1
        " . ($active
            ? ' bg-red-500 text-white'
            : ' bg-red-500 hover:bg-red-400 hover:text-white')
        )
      }}
      @if ($modal)
        x-data
        x-on:click="$store.modal.show('{{ $modal }}')"
      @endif
    >
      @if ($icon)
        <x-dynamic-component :component="$icon" class="h-5.25 w-auto" />
      @endif
      {{ $slot }}
    </button>
    @break
  @default
    <button
      {{ $attributes->class("
        flex items-center gap-2 px-3 py-2 rounded-lg text-white transition-colors duration-300 ease-in-out cursor-pointer focus:outline-none focus:ring-3 focus:ring-billmora-ring-1
        " . ($active
            ? ' bg-billmora-primary text-white'
            : ' bg-billmora-primary hover:bg-billmora-primary-hover hover:text-white')
        )
      }}
      @if ($modal)
        x-data
        x-on:click="$store.modal.show('{{ $modal }}')"
      @endif
    >
      @if ($icon)
        <x-dynamic-component :component="$icon" class="h-5.25 w-auto" />
      @endif
      {{ $slot }}
    </button>
@endswitch
