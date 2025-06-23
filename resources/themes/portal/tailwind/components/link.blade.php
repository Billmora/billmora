@props(['variant' => null, 'icon' => null, 'active' => false])

@switch($variant)
  @case('primary')
    <a {{ $attributes->class([
      'flex items-center gap-2 px-3 py-2 rounded-lg transition-colors duration-300 ease-in-out cursor-pointer focus:outline-none focus:ring-3 focus:ring-billmora-ring-1',
      'bg-billmora-primary text-white' => $active,
      'bg-billmora-primary hover:bg-billmora-primary-hover hover:text-white' => !$active,
    ]) }}>
      @if ($icon)
        <x-dynamic-component :component="$icon" class="h-5.25 w-auto"/>
      @endif
      {{ $slot }}
    </a>
    @break
  @case('secondary')
    <a {{ $attributes->class([
      'flex items-center gap-2 px-3 py-2 rounded-lg transition-colors duration-300 ease-in-out cursor-pointer focus:outline-none focus:ring-3 focus:ring-billmora-ring-1',
      'bg-billmora-secondary-hover text-white' => $active,
      'text-billmora-primary bg-billmora-secondary hover:bg-billmora-secondary-hover hover:text-white' => !$active,
    ]) }}>
      @if ($icon)
        <x-dynamic-component :component="$icon" class="h-5.25 w-auto"/>
      @endif
      {{ $slot }}
    </a>
    @break
  @case('text')
    <a {{ $attributes->class([
      'flex items-center gap-2 px-3 py-2 rounded-lg transition-colors duration-300 ease-in-out cursor-pointer focus:outline-none focus:ring-3 focus:ring-billmora-ring-1',
      'bg-billmora-secondary text-billmora-primary' => $active,
      'text-slate-700 hover:bg-billmora-secondary hover:text-billmora-primary' => !$active,
    ]) }}>
      @if ($icon)
        <x-dynamic-component :component="$icon" class="h-5.25 w-auto"/>
      @endif
      {{ $slot }}
    </a>
    @break
  @default
    <a {{ $attributes->class([
      'flex items-center gap-2 w-fit rounded-lg transition-colors duration-300 ease-in-out cursor-pointer focus:outline-none focus:ring-3 focus:ring-billmora-ring-1',
      'text-billmora-primary' => $active,
      'text-slate-700 hover:text-billmora-primary' => !$active,
    ]) }}>
      @if ($icon)
        <x-dynamic-component :component="$icon" class="h-5.25 w-auto"/>
      @endif
      {{ $slot }}
    </a>
    @break
@endswitch