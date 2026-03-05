@props([
    'name',
    'label' => null,
    'error' => $errors->first($name),
    'required' => false,
    'helper' => null,
])

<div x-data="{ errorVisible: {{ $error ? 'true' : 'false' }} }" class="w-full">
    @if ($label)
        <div class="flex gap-1">
            <label for="{{ $name }}" class="block text-slate-600 font-semibold mb-0.5">
                {{ $label }}
            </label>
            <span class="text-slate-600">
                {{ $required ? __('common.symbol_required') : __('common.symbol_optional') }}
            </span>
        </div>
    @endif

    <div
        x-on:input="errorVisible = false"
        @class([
            'w-full text-slate-700 rounded-lg my-1',
            'fr-error' => $error,
        ])
    >
        <textarea
            id="froala_{{ $name }}"
            name="{{ $name }}"
            {{ $attributes }}
        >{{ $slot }}</textarea>
    </div>

    @if ($error)
        <p class="mt-1 text-sm text-red-400 font-semibold" x-show="errorVisible">
            {{ $error }}
        </p>
    @elseif ($helper)
        <p class="mt-1 text-sm text-slate-500">{{ $helper }}</p>
    @endif
</div>
@push('scripts')
<script>
    const CODE_LANGUAGES = [
        { value: 'auto', label: 'Auto Detect' },
        { value: 'javascript', label: 'JavaScript' },
        { value: 'typescript', label: 'TypeScript' },
        { value: 'php', label: 'PHP' },
        { value: 'html', label: 'HTML' },
        { value: 'css', label: 'CSS' },
        { value: 'json', label: 'JSON' },
        { value: 'sql', label: 'SQL' },
        { value: 'bash', label: 'Bash / Shell' },
        { value: 'python', label: 'Python' },
        { value: 'nginx', label: 'Nginx' },
        { value: 'yaml', label: 'YAML' },
    ];
    FroalaEditor.DefineIcon('codeBlock', {
        template: 'font_awesome',
        NAME: 'code'
    });
    FroalaEditor.RegisterCommand('codeBlock', {
        title: 'Code Block',
        icon: 'codeBlock',
        type: 'dropdown',
        focus: true,
        undo: true,
        refreshAfterCallback: true,
        options: CODE_LANGUAGES.reduce((acc, lang) => {
            acc[lang.value] = lang.label;
            return acc;
        }, {}),
        callback: function (cmd, lang) {
            const selected = this.selection.text() || 'your code here';
            const langClass = lang === 'auto' ? 'hljs' : `language-${lang}`;
            this.html.insert(
                `<pre><code class="${langClass}">${selected}</code></pre><p><br></p>`
            );
            this.el.querySelectorAll('pre code:not([data-highlighted])').forEach(block => {
                hljs.highlightElement(block);
            });
        }
    });
    new FroalaEditor('#froala_{{ $name }}', {
        language: '{{ explode('_', $langActive['lang'])[0] }}',
        toolbarButtons: [
            'bold', 'italic', 'underline', 'strikeThrough', 'fontSize', 'color', 'paragraphStyle', 'paragraphFormat',
            'align', 'formatOL', 'formatUL', 'outdent', 'indent', 'insertLink', 'insertImage', 'insertTable',
            'specialCharacters', 'insertHR', 'selectAll', 'help', 'fullscreen', 'codeBlock', 'html',
        ],
        useClasses: false,
        quickInsertTags: [],
        height: 500,
        spellcheck: false,
        imageUpload: true,
        charCounterCount: true,
        toolbarSticky: false,
        events: {
            'contentChanged': function () {
                this.el.querySelectorAll('pre code').forEach(block => {
                    hljs.highlightElement(block);
                });
            }
        }
    });
</script>
@endpush