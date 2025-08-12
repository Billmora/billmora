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
<script type='text/javascript' src='https://cdn.jsdelivr.net/npm/froala-editor@latest/js/froala_editor.pkgd.min.js'></script>
@foreach ($langs as $lang)
  <script src="https://cdnjs.cloudflare.com/ajax/libs/froala-editor/4.3.1/js/languages/{{ explode('_', $lang['lang'])[0] }}.min.js"></script>
@endforeach
<script>
    let editor = new FroalaEditor('#froalaEditor', {
        language: '{{ explode('_', $langActive['lang'])[0] }}',
        toolbarButtons: [
          'bold', 'italic', 'underline', 'strikeThrough', 'fontSize', 'color', 'paragraphStyle', 'paragraphFormat',
          'align', 'formatOL', 'formatUL', 'outdent', 'indent', 'insertLink', 'insertImage', 'insertTable',
          'emoticons', 'specialCharacters', 'insertHR', 'selectAll', 'help', 'fullscreen',
        ],
        quickInsertTags: [],
        height: 500,
        spellcheck: false,
        imageUpload: true,
        charCounterCount: true,
        toolbarSticky: false,
    });
</script>