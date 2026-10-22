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

    Alpine.effect(() => {
        const modalOpen = Alpine.store("modal")?.open;
        if (modalOpen) {
            overlayManager.open("modal");
        } else {
            overlayManager.close("modal");
        }
    });
});

Alpine.start();
