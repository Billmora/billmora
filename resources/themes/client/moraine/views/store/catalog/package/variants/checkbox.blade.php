<div class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-4"
     x-data="{
         toggle(optId, checked) {
             const set = selectedOptionsByVariant[{{ $variant->id }}];
             checked ? set.add(optId) : set.delete(optId);
             recomputeAll();
             syncUrl();
         },
         isSelected(optId) {
             return selectedOptionsByVariant[{{ $variant->id }}]?.has(optId);
         }
     }">
    @foreach($variant->options as $option)
        <label class="group relative cursor-pointer"
               x-show="variantOptionAvailable({{ $variant->id }}, {{ $option->id }})">
            <input
                type="checkbox"
                name="variants_multi[{{ $variant->id }}][]"
                value="{{ $option->id }}"
                class="hidden"
                x-on:change="toggle({{ $option->id }}, $event.target.checked)"
                :checked="isSelected({{ $option->id }})"
            >
            <div class="h-full bg-white p-4 border-2 border-billmora-2 rounded-xl transition-all group-has-[:checked]:border-billmora-primary-500 hover:border-billmora-primary-500">
                <div class="flex items-start gap-3">
                    <div class="mt-1 h-4 w-4 border-2 border-slate-500 rounded group-has-[:checked]:border-billmora-primary-500 group-has-[:checked]:bg-billmora-primary-500 transition-all"></div>
                    <div class="flex flex-col">
                        <h4 class="text-sm font-semibold text-slate-600">{{ $option->name }}</h4>
                        <span class="text-sm font-semibold text-slate-500"
                              x-text="formatVariantOptionPrice({{ $variant->id }}, {{ $option->id }})"></span>
                    </div>
                </div>
            </div>
        </label>
    @endforeach
</div>