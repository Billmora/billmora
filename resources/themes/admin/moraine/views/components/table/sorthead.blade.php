@props([
    'column',
    'label',
    'sort',
    'direction',
])

<a 
    href="{{ request()->fullUrlWithQuery([
        'sort' => $column, 
        'direction' => ($sort === $column && $direction === 'asc') ? 'desc' : 'asc'
    ]) }}"
    class="inline-flex items-center gap-1 hover:text-billmora-primary-500 transition duration-300"
>
    <span>{{ $label }}</span>
    <span>
        @if ($sort === $column && $direction === 'asc')
            <x-lucide-chevron-up class="w-4 h-auto" />
        @else
            <x-lucide-chevron-down class="w-4 h-auto" />
        @endif
    </span>
</a>
