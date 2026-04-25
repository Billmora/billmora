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
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script>
  function initSortableTable(tbodyEl, model) {
    if (typeof Sortable === 'undefined') return;
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

    Sortable.create(tbodyEl, {
      animation: 200,
      handle: '.drag-handle',
      ghostClass: 'bm-sortable-ghost',
      chosenClass: 'bm-sortable-chosen',
      onStart: function (evt) {
        evt.item.style.opacity = '0.5';
      },
      onEnd: async function (evt) {
        evt.item.style.opacity = '';

        const items = [];
        Array.from(tbodyEl.children).forEach(function (child, index) {
          if (child.dataset.id) {
            items.push({ id: parseInt(child.dataset.id), order: index });
          }
        });

        if (items.length === 0) return;

        const wrapper = tbodyEl.closest('[data-sortable-wrapper]');
        const saveIndicator = wrapper ? wrapper.querySelector('[data-save-indicator]') : null;
        if (saveIndicator) saveIndicator.classList.remove('opacity-0');

        try {
          await fetch('{{ route('admin.reorder') }}', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'X-CSRF-TOKEN': csrfToken,
              'Accept': 'application/json',
            },
            body: JSON.stringify({ model: model, items: items }),
          });
        } catch (e) {
          console.error('Reorder failed', e);
        } finally {
          if (saveIndicator) setTimeout(function () { saveIndicator.classList.add('opacity-0'); }, 1200);
        }
      }
    });
  }

  function initAllSortables() {
    document.querySelectorAll('[data-sortable]').forEach(function (el) {
      initSortableTable(el, el.dataset.sortable);
    });
  }

  // Use 'load' event to ensure CDN SortableJS script is fully executed first.
  window.addEventListener('load', initAllSortables);
</script>
<style>
  .bm-sortable-ghost {
    opacity: 0.35;
    background-color: #eff6ff !important;
  }

  .bm-sortable-chosen {
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.1);
    border-radius: 6px;
  }

  .drag-handle {
    cursor: grab;
    user-select: none;
  }

  .drag-handle:active {
    cursor: grabbing;
  }
</style>

@stack('scripts')