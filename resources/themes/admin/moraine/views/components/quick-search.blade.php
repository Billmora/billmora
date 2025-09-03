<div 
    x-data="globalSearch()" 
    x-init="init()" 
    x-cloak
    x-on:keydown.window.ctrl.k.prevent="openModal()"
    x-on:keydown.window.meta.k.prevent="openModal()"
>
    <div 
        x-show="open" 
        x-transition.opacity 
        class="fixed inset-0 z-110 flex items-start justify-center md:pt-28"
    >
        <!-- Backdrop -->
        <div 
            class="fixed inset-0 bg-black/25 backdrop-blur-sm" 
            x-on:click="close()" 
            aria-hidden="true"
        ></div>

        <!-- Modal box -->
        <div class="relative flex flex-col gap-6 bg-white w-full h-full md:w-[600px] md:h-fit p-4 md:p-8 md:rounded-xl overflow-hidden z-50">
            <!-- Search Input -->
            <div class="flex gap-4 relative w-full mr-auto group">
                <x-lucide-search 
                    class="w-auto h-5 absolute top-1/2 left-2 -translate-y-1/2 pointer-events-none text-slate-400 group-focus-within:text-billmora-primary transition-colors duration-150" 
                />
                <input 
                    x-ref="input" 
                    x-model="query"
                    type="text"
                    placeholder="{{ __('admin/common.quick_search') }} e.g. setting:General"
                    x-on:keydown.escape="close"
                    x-on:keydown.arrow-up.prevent="moveSelection(-1)"
                    x-on:keydown.arrow-down.prevent="moveSelection(1)"
                    x-on:keydown.enter.prevent="selectItem()"
                    class="w-full bg-billmora-1 p-2 pl-9 placeholder:text-slate-500 rounded-lg outline-none focus:ring-2 ring-billmora-primary" 
                />
                <button 
                    type="button"
                    class="block bg-billmora-1 hover:bg-billmora-primary p-2.5 text-slate-600 hover:text-white rounded-full transition-colors duration-300 cursor-pointer"
                    x-on:click="close()"
                >
                    <x-lucide-x class="w-auto h-5" />
                </button>
            </div>

            <!-- Search result list -->
            <div x-ref="resultsContainer" class="h-full md:max-h-80 overflow-y-auto">
                <template x-if="results.length">
                    <div class="space-y-2">
                        <template x-for="(item, index) in results" :key="item.url">
                            <a 
                                :href="item.url"
                                x-on:mouseenter="selectedIndex = index"
                                class="flex flex-col px-4 py-3 rounded-xl transition-colors duration-150"
                                :class="{
                                    'bg-billmora-primary text-white': selectedIndex === index,
                                    'hover:bg-billmora-100': selectedIndex !== index
                                }"
                            >
                                <span 
                                    x-text="item.title" 
                                    class="font-semibold" 
                                    :class="selectedIndex === index ? 'text-white' : 'text-slate-600'"
                                ></span>
                                <span 
                                    x-text="item.category" 
                                    class="text-md" 
                                    :class="selectedIndex === index ? 'text-white' : 'text-slate-500'"
                                ></span>
                            </a>
                        </template>
                    </div>
                </template>

                <!-- Empty state -->
                <div 
                    x-show="!results.length && query" 
                    class="text-center text-slate-500"
                >
                    {{ __('admin/common.quick_search_not_found') }}
                </div>
            </div>

            <!-- Helper shortcuts -->
            <div class="hidden md:flex gap-6">
                <div class="flex gap-2 items-center">
                    <div class="bg-billmora-1 p-1 text-sm text-slate-600 font-semibold rounded-lg">
                        <x-lucide-arrow-up class="w-auto h-5" />
                    </div>
                    <div class="bg-billmora-1 p-1 text-sm text-slate-600 font-semibold rounded-lg">
                        <x-lucide-arrow-down class="w-auto h-5" />
                    </div>
                    <span class="font-semibold text-slate-600">{{ __('admin/common.quick_search_navigate') }}</span>
                </div>
                <div class="flex gap-2 items-center">
                    <span class="bg-billmora-1 p-1 text-sm text-slate-600 font-semibold rounded-lg uppercase">enter</span>
                    <span class="font-semibold text-slate-600">{{ __('admin/common.quick_search_select') }}</span>
                </div>
                <div class="flex gap-2 items-center">
                    <span class="bg-billmora-1 p-1 text-sm text-slate-600 font-semibold rounded-lg uppercase">esc</span>
                    <span class="font-semibold text-slate-600">{{ __('admin/common.quick_search_close') }}</span>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    /**
     * Alpine.JS component for global quick-search modal
     */
    function globalSearch() {
        return {
            open: false, // modal visibility
            query: '', // user input
            list: [], // loaded items
            selectedIndex: -1, // currently highlighted index

            init() {
                // listen to custom event dispatched from outside
                window.addEventListener('openQuickSearch', this.openModal.bind(this));
            },

            async openModal() {
                this.open = true;
                this.selectedIndex = -1;

                // lazy-load results only the first time
                if (this.list.length === 0) {
                    const res = await fetch("{{ route('admin.quick-search') }}");
                    this.list = await res.json();
                }

                // focus search input when opening
                this.$nextTick(() => this.$refs.input.focus());
            },

            close() {
                // reset when closing
                this.open = false;
                this.query = '';
                this.selectedIndex = -1;
            },

            // filter results based on query (supports `category:title` syntax)
            get results() {
                if (!this.query) return [];
                return this.list.filter(item =>
                    (`${item.category}:${item.title}`).toLowerCase().includes(this.query.toLowerCase())
                );
            },

            // handle UP / DOWN navigation
            moveSelection(step) {
                if (this.results.length === 0) {
                    this.selectedIndex = -1;
                    return;
                }

                if (this.selectedIndex === -1) {
                    this.selectedIndex = step === 1 ? 0 : this.results.length - 1;
                    return;
                }

                const newIndex = this.selectedIndex + step;
                this.selectedIndex = Math.max(0, Math.min(newIndex, this.results.length - 1));

                // auto-scroll to keep selected item visible
                this.$nextTick(() => {
                    const container = this.$refs.resultsContainer
                    const items = container.querySelectorAll('a')
                    const selectedElement = items[this.selectedIndex]
                    selectedElement?.scrollIntoView({ block: 'nearest', behavior: 'smooth' })
                })
            },

            // redirect to selected item URL
            selectItem() {
                if (this.selectedIndex >= 0 && this.results[this.selectedIndex]) {
                    window.location.href = this.results[this.selectedIndex].url;
                }
            }
        }
    }
</script>
