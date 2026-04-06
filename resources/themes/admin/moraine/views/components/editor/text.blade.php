@props([
    'name',
    'label' => null,
    'error' => $errors->first($name),
    'required' => false,
    'helper' => null,
])

<div x-data="{ errorVisible: {{ $error ? 'true' : 'false' }} }" class="w-full">
    @if ($label)
        <div class="flex gap-1">
            <label for="{{ $name }}" class="block text-slate-600 font-semibold mb-0.5">
                {{ $label }}
            </label>
            <span class="text-slate-600">
                {{ $required ? __('common.symbol_required') : __('common.symbol_optional') }}
            </span>
        </div>
    @endif

    <div
        x-on:input="errorVisible = false"
        @class([
            'w-full text-slate-700 rounded-lg my-1',
            'tiptap-has-error' => $error,
        ])
    >
        {{-- Hidden input synced by TipTap --}}
        <input
            type="hidden"
            id="tiptap_{{ $name }}"
            name="{{ $name }}"
            value="{{ $slot->isEmpty() ? '' : $slot }}"
            {{ $attributes->except(['id', 'name']) }}
        >
        {{-- TipTap mounts here (injected by initTiptap) --}}
    </div>

    @if ($error)
        <p class="mt-1 text-sm text-red-400 font-semibold" x-show="errorVisible">
            {{ $error }}
        </p>
    @elseif ($helper)
        <p class="mt-1 text-sm text-slate-500">{{ $helper }}</p>
    @endif
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        if (typeof window.initTiptap === 'function') {
            window.initTiptap('tiptap_{{ $name }}');
        }
    });
</script>
@endpush