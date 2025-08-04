{{-- Sidebar handler --}}
<script>
  const sidebar = document.getElementById('sidebar');
  const backdrop = document.getElementById('backdrop');
  const toggle = document.getElementById('toggleSidebar');
  const close = document.getElementById('closeSidebar');

  function openSidebar() {
    sidebar.classList.remove('-translate-x-full');
    backdrop.classList.remove('opacity-0', 'pointer-events-none');
  }

  function closeSidebar() {
    sidebar.classList.add('-translate-x-full');
    backdrop.classList.add('opacity-0', 'pointer-events-none');
  }

  toggle.addEventListener('click', () => {
    if (sidebar.classList.contains('-translate-x-full')) {
      openSidebar();
    } else {
      closeSidebar();
    }
  });

  backdrop.addEventListener('click', closeSidebar);
  close.addEventListener('click', closeSidebar);
</script>
<script>
    // Bind click on any #quickSearch element to trigger opening the global quick-search modal
    document.querySelectorAll('#quickSearch').forEach(button => {
        button.addEventListener('click', () => {
            window.dispatchEvent(new CustomEvent('openQuickSearch'));
        });
    });
</script>