<div class="flex items-center w-full my-1 mt-4">
    <select
        name="variants[{{ $variant->id }}]"
        x-model="selectedOptionByVariant[{{ $variant->id }}]"
        x-on:change="setVariantSelect({{ $variant->id }}, $event.target.value)"
        class="w-full text-slate-700 rounded-lg px-3 py-2.5 border-2 border-billmora-2 outline-none focus:ring-2 ring-billmora-primary appearance-none"
    >
        <option class="text-slate-500" selected disabled>
            {{ __('common.choose_option') }}
        </option>
        @foreach($variant->options as $option)
            <option
                value="{{ $option->id }}"
                x-show="variantOptionAvailable({{ $variant->id }}, {{ $option->id }})"
                x-text="selectOptionLabel({{ $variant->id }}, {{ $option->id }}, @js($option->name))"
            ></option>
        @endforeach
    </select>
    <x-lucide-chevrons-up-down class="w-auto h-5 -ml-7 text-slate-700 pointer-events-none" />
</div>