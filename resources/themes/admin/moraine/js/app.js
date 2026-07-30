import Alpine from "alpinejs";
import "./editor.js";

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
});

Alpine.start();
