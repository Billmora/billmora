@props([
  'variant' => 'primary',
  'description',
  'icon' => null,
])

@switch($variant)
  @case('primary')
    <div class="w-full flex flex-col items-start bg-blue-400 gap-4 p-4 rounded-xl">
      <div class="flex gap-2 items-center">
        @if ($icon)
          <x-dynamic-component :component="$icon" class="text-blue-900 w-6 h-6"/>
        @endif
        <p class="text-blue-800 font-semibold">{{ $description }}</p>
      </div>
      {{ $slot }}
    </div>
    @break
  @case('success')
    <div class="w-full flex flex-col items-start bg-green-400 gap-4 p-4 rounded-xl">
      <div class="flex gap-2 items-center">
        @if ($icon)
          <x-dynamic-component :component="$icon" class="text-green-900 w-6 h-6"/>
        @endif
        <p class="text-green-800 font-semibold">{{ $description }}</p>
      </div>
      {{ $slot }}
    </div>
    @break
  @case('warning')
    <div class="w-full flex flex-col items-start bg-yellow-400 gap-4 p-4 rounded-xl">
      <div class="flex gap-2 items-center">
        @if ($icon)
          <x-dynamic-component :component="$icon" class="text-yellow-900 w-6 h-6"/>
        @endif
        <p class="text-yellow-800 font-semibold">{{ $description }}</p>
      </div>
      {{ $slot }}
    </div>
    @break
  @case('danger')
    <div class="w-full flex flex-col items-start bg-red-400 gap-4 p-4 rounded-xl">
      <div class="flex gap-2 items-center">
        @if ($icon)
          <x-dynamic-component :component="$icon" class="text-red-900 w-6 h-6"/>
        @endif
        <p class="text-red-800 font-semibold">{{ $description }}</p>
      </div>
      {{ $slot }}
    </div>
    @break
  @default
@endswitch