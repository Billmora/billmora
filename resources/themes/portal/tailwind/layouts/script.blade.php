<script>
    document.addEventListener('alpine:init', () => {
        Alpine.store('modal', {
            open: null,
            show(id) { this.open = id },
            close() { this.open = null },
        });
    });
</script>