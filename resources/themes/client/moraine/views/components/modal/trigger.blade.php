@props([
    'modal' => null,
    'variant' => 'open',
])

<button x-data
        @switch($variant)
            @case('open')
                x-on:click="$store.modal.show('{{ $modal }}')"
                @break
            @case('close')
                x-on:click="$store.modal.close()"
                @break
        @endswitch
        {{ $attributes }}>
    {{ $slot }}
</button>