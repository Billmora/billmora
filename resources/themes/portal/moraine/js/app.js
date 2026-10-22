import Alpine from "alpinejs";

const overlayManager = {
    active: new Set(),
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
            document.body.classList.add("overflow-hidden");
            document.documentElement.classList.add("overflow-hidden");
        } else {
            document.body.classList.remove("overflow-hidden");
            document.documentElement.classList.remove("overflow-hidden");
        }
    },
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
