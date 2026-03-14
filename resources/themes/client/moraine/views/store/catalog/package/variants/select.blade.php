<div class="flex items-center w-full my-1 mt-4"
     x-data="{
         handleChange(e) {
             selectedOptionByVariant[{{ $variant->id }}] = Number(e.target.value);
             recomputeAll();
             syncUrl();
         },
         label(optId, name) {
             const price = formatVariantOptionPrice({{ $variant->id }}, optId);
             return price ? `${name} - ${price}` : name;
         }
     }">
    <select
        name="variants[{{ $variant->id }}]"
        x-model="selectedOptionByVariant[{{ $variant->id }}]"
        x-on:change="handleChange"
        class="w-full text-slate-700 rounded-lg px-3 py-2.5 border-2 border-billmora-2 outline-none focus:ring-2 ring-billmora-primary-500 appearance-none"
    >
        <option class="text-slate-500" selected disabled>{{ __('common.choose_option') }}</option>
        @foreach($variant->options as $option)
            <option
                value="{{ $option->id }}"
                x-show="variantOptionAvailable({{ $variant->id }}, {{ $option->id }})"
                x-text="label({{ $option->id }}, @js($option->name))"
            ></option>
        @endforeach
    </select>
    <x-lucide-chevrons-up-down class="w-auto h-5 -ml-7 text-slate-700 pointer-events-none" />
</div>