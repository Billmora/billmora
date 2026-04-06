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
  (function () {
    const sidemenu = document.getElementById('sidemenu');
    if (sidemenu) {
      const storageKey = 'sidebar-scroll-admin';
      const clickKey = 'sidebar-clicked-admin';
      const savedScroll = sessionStorage.getItem(storageKey);
      const wasClicked = sessionStorage.getItem(clickKey);
      const activeLink = sidemenu.querySelector('.bg-billmora-primary-500');

      // Consume the click flag
      sessionStorage.removeItem(clickKey);

      if (wasClicked && savedScroll) {
        // Restore scroll if we came from a sidebar click
        sidemenu.scrollTop = savedScroll;
      } else if (activeLink) {
        // Scroll into view for manual navigation or fresh loads
        activeLink.scrollIntoView({
          block: 'center'
        });
      }

      // Save scroll position on scroll
      sidemenu.addEventListener('scroll', () => {
        sessionStorage.setItem(storageKey, sidemenu.scrollTop);
      });

      // Set flag when a link is clicked
      sidemenu.addEventListener('click', (e) => {
        if (e.target.closest('a')) {
          sessionStorage.setItem(clickKey, 'true');
        }
      });
    }
  })();
</script>
<script>
  // Bind click on any #browse element to trigger opening the global quick-search modal
  document.querySelectorAll('#browse').forEach(button => {
    button.addEventListener('click', () => {
      window.dispatchEvent(new CustomEvent('openBrowse'));
    });
  });
</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/highlight.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/languages/php.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/languages/blade.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/languages/nginx.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/languages/bash.min.js"></script>

@stack('scripts')