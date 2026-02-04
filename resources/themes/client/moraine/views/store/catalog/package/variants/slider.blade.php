<div class="mt-4"
     x-data="{ 
         idx: 0,
         get opts() { 
             return getAvailableOptions({{ $variant->id }}); 
         },
         init() {
             this.$watch('selectedOptionByVariant[{{ $variant->id }}]', (val) => {
                 const i = this.opts.findIndex(o => o.id == val);
                 if (i >= 0) this.idx = i;
             });
             const curr = selectedOptionByVariant[{{ $variant->id }}];
             if (curr) {
                 const i = this.opts.findIndex(o => o.id == curr);
                 if (i >= 0) this.idx = i;
             } else {
                 this.update(this.idx);
             }
         },
         update(val) {
             this.idx = Number(val);
             const opt = this.opts[this.idx];
             if (opt) {
                 selectedOptionByVariant[{{ $variant->id }}] = opt.id;
                 recomputeAll();
                 syncUrl();
             }
         },
         tickClass(n, i) {
             if (n <= 1 || i === 0) return 'start-0 text-start';
             if (i === n - 1) return 'end-0 text-end';
             return `left-[${(i / (n - 1)) * 100}%] -translate-x-1/2 text-center`;
         }
     }"
>
    <div class="relative mb-10">
        <input type="range"
            min="0"
            :max="Math.max(0, opts.length - 1)"
            step="1"
            x-model="idx"
            x-on:input="update($event.target.value)"
            class="w-full h-2 cursor-pointer accent-billmora-primary"
        >
        <template x-for="(opt, i) in opts" :key="opt.id">
            <span class="grid text-sm text-body absolute"
                  :class="tickClass(opts.length, i)">
                <span class="text-slate-600 font-semibold" x-text="opt.name"></span>
                <span class="text-slate-500 font-medium" x-text="formatVariantOptionPrice({{ $variant->id }}, opt.id)"></span>
            </span>
        </template>
    </div>
    <input type="hidden" 
           name="variants[{{ $variant->id }}]" 
           :value="opts[idx] ? opts[idx].id : ''">
</div>