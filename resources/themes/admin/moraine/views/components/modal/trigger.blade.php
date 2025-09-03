@props([
    'modal' => null,
    'variant' => 'open',
])

<button x-data 
        @if ($variant === 'open')
            x-on:click="$store.modal.show('{{ $modal }}')"
        @elseif ($variant === 'close')
            x-on:click="$store.modal.close()"
        @endif
        {{ $attributes }}>
    {{ $slot }}
</button>