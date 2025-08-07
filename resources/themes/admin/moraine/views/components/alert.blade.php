@props([
  'variant' => 'primary',
  'title'
])

@switch($variant)
  @case('primary')
    <div class="w-full flex flex-col items-start gap-4 bg-blue-100 p-4 text-blue-800 border-2 border-blue-600 rounded-2xl" role="alert">
        <div class="flex gap-2 items-center">
            <x-lucide-info class="w-auto h-6" />
            <p class="font-semibold">{{ $title }}</p>
        </div>
        {{ $slot }}
    </div>
    @break
  @case('success')
    <div class="w-full flex flex-col items-start gap-4 bg-green-100 p-4 text-green-800 border-2 border-green-600 rounded-2xl" role="alert">
        <div class="flex gap-2 items-center">
            <x-lucide-check-circle class="w-auto h-6" />
            <p class="font-semibold">{{ $title }}</p>
        </div>
        {{ $slot }}
    </div>
    @break
  @case('warning')
    <div class="w-full flex flex-col items-start gap-4 bg-yellow-100 p-4 text-yellow-800 border-2 border-yellow-600 rounded-2xl" role="alert">
        <div class="flex gap-2 items-center">
            <x-lucide-circle-alert class="w-auto h-6" />
            <p class="font-semibold">{{ $title }}</p>
        </div>
        {{ $slot }}
    </div>
    @break
  @case('danger')
    <div class="w-full flex flex-col items-start gap-4 bg-red-100 p-4 text-red-800 border-2 border-red-600 rounded-2xl" role="alert">
        <div class="flex gap-2 items-center">
            <x-lucide-triangle-alert class="w-auto h-6" />
            <p class="font-semibold">{{ $title }}</p>
        </div>
        {{ $slot }}
    </div>
    @break
  @default
@endswitch