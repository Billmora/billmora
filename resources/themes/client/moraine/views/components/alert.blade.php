@props([
  'variant' => 'primary',
  'title' => null,
])

<div
  @class([
    "w-full flex flex-col items-start gap-4 p-4 border-2 rounded-2xl",
    "bg-violet-100 text-violet-800 border-violet-600" => $variant === 'primary',
    "bg-green-100 text-green-800 border-green-600" => $variant === 'success',
    "bg-yellow-100 text-yellow-800 border-yellow-600" => $variant === 'warning',
    "bg-red-100 text-red-800 border-red-600" => $variant === 'danger',
  ])
  role="alert"
>
  @if ($title)
    <div class="flex gap-2 items-center">
      @switch($variant)
        @case('success')
          <x-lucide-check-circle class="w-auto h-6"/>
          @break
        @case('warning')
          <x-lucide-circle-alert class="w-auto h-6"/>
          @break
        @case('danger')
          <x-lucide-triangle-alert class="w-auto h-6"/>
          @break
        @default
          <x-lucide-info class="w-auto h-6"/>
      @endswitch
      <p class="font-semibold">{{ $title }}</p>
    </div>
  @endif
  {{ $slot }}
</div>