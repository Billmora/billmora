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
<script type='text/javascript' src='https://cdn.jsdelivr.net/npm/froala-editor@latest/js/froala_editor.pkgd.min.js'></script>
@foreach ($langs as $lang)
  <script src="https://cdnjs.cloudflare.com/ajax/libs/froala-editor/4.3.1/js/languages/{{ explode('_', $lang['lang'])[0] }}.min.js"></script>
@endforeach
@stack('scripts')