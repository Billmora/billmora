<div class="mt-4" x-data="{ idx: 0 }" x-show="variantHasAvailableOptions({{ $variant->id }})">
    <div class="relative mb-10">
        <input type="range"
            min="0"
            :max="Math.max(0, getAvailableOptionsForCycle({{ $variant->id }}).length - 1)"
            step="1"
            class="w-full h-2 cursor-pointer accent-billmora-primary"
            x-on:input="idx = Number($event.target.value); setVariantSlider({{ $variant->id }}, idx);"
        >
        <template x-for="(opt, i) in getAvailableOptionsForCycle({{ $variant->id }})" :key="opt.id">
            <span
                class="text-sm text-body absolute -bottom-6"
                :class="sliderTickClass(getAvailableOptionsForCycle({{ $variant->id }}).length, i)"
            >
                <span x-text="opt.name"></span>
                <span class="text-slate-500" x-text="`(${formatVariantOptionPrice({{ $variant->id }}, opt.id)})`"></span>
            </span>
        </template>
    </div>
    <input type="hidden" name="variants[{{ $variant->id }}]" :value="sliderOptionId({{ $variant->id }}, idx)">
</div>