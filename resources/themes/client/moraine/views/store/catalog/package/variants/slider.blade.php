<div class="mt-4" 
     x-data="{ 
         idx: 0,
         init() {
             this.syncSliderIndex();
         },
         syncSliderIndex() {
             const variantId = {{ $variant->id }};
             const selectedOptionId = $wire.selectedOptionByVariant?.[variantId] || selectedOptionByVariant?.[variantId];
             
             if (selectedOptionId) {
                 const availableOptions = getAvailableOptionsForCycle(variantId);
                 const foundIndex = availableOptions.findIndex(opt => opt.id === selectedOptionId);
                 if (foundIndex >= 0) {
                     this.idx = foundIndex;
                 }
             }
         }
     }"
     x-init="init()"
     x-show="variantHasAvailableOptions({{ $variant->id }})"
>
    <div class="relative mb-10">
        <input type="range"
            min="0"
            :max="Math.max(0, getAvailableOptionsForCycle({{ $variant->id }}).length - 1)"
            step="1"
            :value="idx"
            class="w-full h-2 cursor-pointer accent-billmora-primary"
            x-on:input="idx = Number($event.target.value); setVariantSlider({{ $variant->id }}, idx);"
        >
        <template x-for="(opt, i) in getAvailableOptionsForCycle({{ $variant->id }})" :key="opt.id">
            <span
                class="grid text-sm text-body absolute"
                :class="sliderTickClass(getAvailableOptionsForCycle({{ $variant->id }}).length, i)"
            >
                <span class="text-slate-600 font-semibold" x-text="opt.name"></span>
                <span class="text-slate-500 font-medium" x-text="formatVariantOptionPrice({{ $variant->id }}, opt.id)"></span>
            </span>
        </template>
    </div>
    <input type="hidden" name="variants[{{ $variant->id }}]" :value="sliderOptionId({{ $variant->id }}, idx)">
</div>