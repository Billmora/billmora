@props([
  'name',
  'type' => 'text',
  'label' => null,
  'value' => old($name),
  'error' => $errors->first($name),
  'required' => null,
])

<div x-data="{ errorVisible: {{ $error ? 'true' : 'false' }} }" class="w-full">
  @if ($label)
  <div class="flex gap-1">
    <label for="{{ $name }}" class="block text-slate-500 font-semibold mb-0.5">
      {{ $label }}
    </label>
    @if ($required)
      <span class="text-red-500">{{ __('common.required_symbol') }}</span> 
    @else
      <span class="text-slate-400">{{ __('common.optional_symbol') }}</span>
    @endif
  </div>
  @endif

  <div class="relative">
    <input
      type="{{ $type }}"
      name="{{ $name }}"
      id="{{ $name }}"
      value="{{ $value }}"
      x-on:input="errorVisible = false"
      :class="[
        'w-full bg-billmora-2 text-gray-700 border-2 rounded-lg px-3 py-2 focus:outline-none focus:border-billmora-ring-1 hover:border-billmora-ring-1 appearance-none cursor-pointer',
        errorVisible ? 'border-red-400' : 'border-billmora-3'
      ]"
      {{ $attributes }}
    />
  </div>

  <template x-if="errorVisible">
    <p class="mt-1 text-sm text-red-400 font-semibold">{{ $error }}</p>
  </template>
</div>
