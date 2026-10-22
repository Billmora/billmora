import Alpine from "alpinejs";
import "./editor.js";

const overlayManager = {
    active: new Set(),
    _wheelHandler: null,
    _touchHandler: null,
    _keyHandler: null,

    open(id) {
        this.active.add(id);
        this.update();
    },

    close(id) {
        this.active.delete(id);
        this.update();
    },

    update() {
        if (this.active.size > 0) {
            document.documentElement.classList.add("has-overlay");
            if (!this._wheelHandler) {
                this._attachListeners();
            }
        } else {
            document.documentElement.classList.remove("has-overlay");
            this._removeListeners();
        }
    },

    _attachListeners() {
        this._wheelHandler = (e) => {
            const scrollable = e.target.closest(
                '.overflow-y-auto, .overflow-auto, textarea, [contenteditable="true"]'
            );

            if (!scrollable) {
                e.preventDefault();
                return;
            }

            const isScrollingDown = e.deltaY > 0;
            const canScrollDown = scrollable.scrollTop + scrollable.clientHeight < scrollable.scrollHeight - 1;
            const canScrollUp = scrollable.scrollTop > 1;

            if (isScrollingDown && !canScrollDown) {
                e.preventDefault();
            } else if (!isScrollingDown && !canScrollUp) {
                e.preventDefault();
            }
        };

        this._touchHandler = (e) => {
            const scrollable = e.target.closest(
                '.overflow-y-auto, .overflow-auto, textarea, [contenteditable="true"]'
            );
            if (!scrollable) {
                e.preventDefault();
            }
        };

        this._keyHandler = (e) => {
            const scrollKeys = ['ArrowUp', 'ArrowDown', 'PageUp', 'PageDown', 'Home', 'End', ' '];
            if (scrollKeys.includes(e.key)) {
                const isInput = e.target.closest('input, textarea, select, [contenteditable="true"]');
                if (!isInput) {
                    e.preventDefault();
                }
            }
        };

        window.addEventListener('wheel', this._wheelHandler, { passive: false });
        window.addEventListener('touchmove', this._touchHandler, { passive: false });
        window.addEventListener('keydown', this._keyHandler, { passive: false });
    },

    _removeListeners() {
        if (this._wheelHandler) {
            window.removeEventListener('wheel', this._wheelHandler);
            this._wheelHandler = null;
        }
        if (this._touchHandler) {
            window.removeEventListener('touchmove', this._touchHandler);
            this._touchHandler = null;
        }
        if (this._keyHandler) {
            window.removeEventListener('keydown', this._keyHandler);
            this._keyHandler = null;
        }
    }
};
window.overlayManager = overlayManager;

document.addEventListener("alpine:init", () => {
    Alpine.store("modal", {
        open: null,
        show(name) {
            this.open = name;
        },
        close() {
            this.open = null;
        },
    });

    Alpine.store("drawer", {
        open: null,
        show(name) {
            this.open = name;
        },
        close() {
            this.open = null;
        },
    });

    Alpine.effect(() => {
        const modalOpen = Alpine.store("modal")?.open;
        if (modalOpen) {
            overlayManager.open("modal");
        } else {
            overlayManager.close("modal");
        }
    });

    Alpine.effect(() => {
        const drawerOpen = Alpine.store("drawer")?.open;
        if (drawerOpen) {
            overlayManager.open("drawer");
        } else {
            overlayManager.close("drawer");
        }
    });

    Alpine.data("browse", (staticItems) => ({
        open: false,
        query: "",
        results: [],
        loading: false,
        selectedIndex: -1,
        _debounceTimer: null,

        init() {
            // Read search URL from data attribute set by Blade on the root element
            this._searchUrl = this.$el.dataset.searchUrl ?? "";

            window.addEventListener("openBrowse", this.openModal.bind(this));

            this.$watch("open", (isOpen) => {
                if (isOpen) {
                    overlayManager.open("browse");
                } else {
                    overlayManager.close("browse");
                }
            });

            this.$watch("query", (val) => {
                clearTimeout(this._debounceTimer);
                this.selectedIndex = -1;

                if (!val || val.length < 2) {
                    this.results = [];
                    this.loading = false;
                    return;
                }

                // Parse optional "category:term" prefix syntax (e.g. "user:john", "service:SVC-")
                const colonIdx = val.indexOf(":");
                const categoryHint = colonIdx > 0 ? val.slice(0, colonIdx).toLowerCase().trim() : null;
                const searchTerm   = colonIdx > 0 ? val.slice(colonIdx + 1).trim() : val;

                // If user typed only the category prefix with no term yet, wait
                if (!searchTerm || searchTerm.length < 1) {
                    this.results = [];
                    this.loading = false;
                    return;
                }

                // 1. Instantly filter static nav items (client-side, no latency)
                const staticMatches = staticItems.filter((item) => {
                    if (categoryHint) {
                        return (
                            item.category === categoryHint &&
                            item.title.toLowerCase().includes(searchTerm.toLowerCase())
                        );
                    }
                    return `${item.category}:${item.title}`
                        .toLowerCase()
                        .includes(val.toLowerCase());
                });
                this.results = staticMatches;

                // 2. Debounce API call for dynamic model data (services, users, etc.)
                this.loading = true;
                this._debounceTimer = setTimeout(
                    () => this._fetchDynamic(searchTerm, categoryHint),
                    300
                );
            });
        },

        async _fetchDynamic(searchTerm, categoryHint) {
            try {
                const url = `${this._searchUrl}?q=${encodeURIComponent(searchTerm)}`;
                const response = await fetch(url, {
                    headers: {
                        Accept: "application/json",
                        "X-Requested-With": "XMLHttpRequest",
                    },
                });
                let dynamicItems = await response.json();

                // If a category prefix was given, filter dynamic results to that category only
                if (categoryHint) {
                    dynamicItems = dynamicItems.filter(
                        (item) => item.category === categoryHint
                    );
                }

                // Re-compute static matches with the same category logic
                const staticMatches = staticItems.filter((item) => {
                    if (categoryHint) {
                        return (
                            item.category === categoryHint &&
                            item.title.toLowerCase().includes(searchTerm.toLowerCase())
                        );
                    }
                    return `${item.category}:${item.title}`
                        .toLowerCase()
                        .includes(searchTerm.toLowerCase());
                });

                this.results = [...staticMatches, ...dynamicItems];
            } catch (e) {
                console.error("Browse search failed", e);
            } finally {
                this.loading = false;
            }
        },

        openModal() {
            this.open = true;
            this.selectedIndex = -1;
            this.$nextTick(() => this.$refs.input.focus());
        },

        close() {
            this.open = false;
            this.query = "";
            this.results = [];
            this.selectedIndex = -1;
            this.loading = false;
            clearTimeout(this._debounceTimer);
        },

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
            this.selectedIndex = Math.max(
                0,
                Math.min(newIndex, this.results.length - 1)
            );

            this.$nextTick(() => {
                const container = this.$refs.resultsContainer;
                const items = container.querySelectorAll("a");
                const selectedElement = items[this.selectedIndex];
                selectedElement?.scrollIntoView({
                    block: "nearest",
                    behavior: "smooth",
                });
            });
        },

        selectItem() {
            if (this.selectedIndex >= 0 && this.results[this.selectedIndex]) {
                window.location.href = this.results[this.selectedIndex].url;
            }
        },
    }));
});

Alpine.start();
