@props([
    'drawer' => null,
    'variant' => 'open',
])

<button x-data
        @switch($variant)
            @case('open')
                x-on:click="$store.drawer.show('{{ $drawer }}')"
                @break
            @case('close')
                x-on:click="$store.drawer.close()"
                @break
        @endswitch
        {{ $attributes }}>
    {{ $slot }}
</button>
