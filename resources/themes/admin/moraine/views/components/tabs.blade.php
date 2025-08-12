@props([
    'tabs' => [],
    'active' => null
])

<div class="flex gap-4 bg-white w-full p-4 border-2 border-billmora-2 rounded-2xl overflow-x-auto">
    @foreach ($tabs as $tab)
        <a href="{{ $tab['route'] }}"
           class="flex items-center gap-2 px-3 py-2 rounded-lg transition ease-in-out duration-150
                  {{ $active === $tab['route'] ? 'bg-billmora-primary text-white' : 'hover:bg-billmora-primary text-slate-700 hover:text-white' }}">
            <x-dynamic-component :component="$tab['icon']" class="w-auto h-5" />
            <span>{{ $tab['label'] }}</span>
        </a>
    @endforeach
</div>