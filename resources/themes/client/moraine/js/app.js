import Alpine from "alpinejs";

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
});

Alpine.start();
