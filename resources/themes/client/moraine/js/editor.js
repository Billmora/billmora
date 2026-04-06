import { Editor } from '@tiptap/core';
import StarterKit from '@tiptap/starter-kit';
import Underline from '@tiptap/extension-underline';
import Link from '@tiptap/extension-link';
import Image from '@tiptap/extension-image';
import Table from '@tiptap/extension-table';
import TableRow from '@tiptap/extension-table-row';
import TableHeader from '@tiptap/extension-table-header';
import TableCell from '@tiptap/extension-table-cell';
import TextAlign from '@tiptap/extension-text-align';
import TextStyle from '@tiptap/extension-text-style';
import { Color } from '@tiptap/extension-color';
import CodeBlockLowlight from '@tiptap/extension-code-block-lowlight';
import { lowlight } from 'lowlight';

// ─────────────────────────────────────────────
// Toolbar button helpers
// ─────────────────────────────────────────────
const ICON = {
    bold:        '<i class="fa-solid fa-bold"></i>',
    italic:      '<i class="fa-solid fa-italic"></i>',
    underline:   '<i class="fa-solid fa-underline"></i>',
    strike:      '<i class="fa-solid fa-strikethrough"></i>',
    h1:          '<span class="tiptap-tb-text">H1</span>',
    h2:          '<span class="tiptap-tb-text">H2</span>',
    h3:          '<span class="tiptap-tb-text">H3</span>',
    p:           '<span class="tiptap-tb-text">¶</span>',
    alignLeft:   '<i class="fa-solid fa-align-left"></i>',
    alignCenter: '<i class="fa-solid fa-align-center"></i>',
    alignRight:  '<i class="fa-solid fa-align-right"></i>',
    alignJustify:'<i class="fa-solid fa-align-justify"></i>',
    ul:          '<i class="fa-solid fa-list-ul"></i>',
    ol:          '<i class="fa-solid fa-list-ol"></i>',
    outdent:     '<i class="fa-solid fa-outdent"></i>',
    indent:      '<i class="fa-solid fa-indent"></i>',
    link:        '<i class="fa-solid fa-link"></i>',
    image:       '<i class="fa-solid fa-image"></i>',
    table:       '<i class="fa-solid fa-table"></i>',
    hr:          '<i class="fa-solid fa-minus"></i>',
    code:        '<i class="fa-solid fa-code"></i>',
    blockquote:  '<i class="fa-solid fa-quote-left"></i>',
    undo:        '<i class="fa-solid fa-rotate-left"></i>',
    redo:        '<i class="fa-solid fa-rotate-right"></i>',
    fullscreen:  '<i class="fa-solid fa-expand"></i>',
    exitFullscreen: '<i class="fa-solid fa-compress"></i>',
};

function btn(title, icon, action, activeCheck) {
    return { title, icon, action, activeCheck };
}

function makeBtn(editor, title, iconHtml, action, activeCheck) {
    const el = document.createElement('button');
    el.type = 'button';
    el.title = title;
    el.className = 'tiptap-tb-btn';
    el.innerHTML = iconHtml;
    el.addEventListener('mousedown', (e) => {
        e.preventDefault();
        action(editor);
    });
    return { el, activeCheck };
}

// ─────────────────────────────────────────────
// Link prompt helper
// ─────────────────────────────────────────────
function promptLink(editor) {
    const current = editor.getAttributes('link').href || '';
    const url = window.prompt('Enter URL', current);
    if (url === null) return;
    if (url === '') {
        editor.chain().focus().unsetLink().run();
        return;
    }
    editor.chain().focus().setLink({ href: url, target: '_blank' }).run();
}

// ─────────────────────────────────────────────
// Image URL prompt helper
// ─────────────────────────────────────────────
function promptImage(editor) {
    const url = window.prompt('Image URL');
    if (url) editor.chain().focus().setImage({ src: url }).run();
}

// ─────────────────────────────────────────────
// Table insertion dropdown
// ─────────────────────────────────────────────
function makeTableBtn(editor) {
    const wrap = document.createElement('div');
    wrap.className = 'tiptap-tb-dropdown-wrap';

    const btn = document.createElement('button');
    btn.type = 'button';
    btn.title = 'Insert Table';
    btn.className = 'tiptap-tb-btn';
    btn.innerHTML = ICON.table;

    const drop = document.createElement('div');
    drop.className = 'tiptap-tb-dropdown';
    drop.innerHTML = `
        <div class="tiptap-tb-grid" id="tiptap-table-grid-${Math.random().toString(36).slice(2)}">
            ${Array.from({ length: 25 }, (_, i) => {
                const r = Math.floor(i / 5) + 1;
                const c = (i % 5) + 1;
                return `<div class="tiptap-table-cell" data-row="${r}" data-col="${c}"></div>`;
            }).join('')}
        </div>
        <p class="tiptap-table-hint">0 × 0</p>
    `;

    btn.addEventListener('click', (e) => {
        e.stopPropagation();
        const allDrops = document.querySelectorAll('.tiptap-tb-dropdown.open');
        allDrops.forEach(d => d !== drop && d.classList.remove('open'));
        drop.classList.toggle('open');
    });

    const cells = drop.querySelectorAll('.tiptap-table-cell');
    const hint = drop.querySelector('.tiptap-table-hint');
    cells.forEach(cell => {
        cell.addEventListener('mouseover', () => {
            const r = +cell.dataset.row;
            const c = +cell.dataset.col;
            cells.forEach(c2 => {
                c2.classList.toggle('hover', +c2.dataset.row <= r && +c2.dataset.col <= c);
            });
            hint.textContent = `${r} × ${c}`;
        });
        cell.addEventListener('click', () => {
            const r = +cell.dataset.row;
            const c = +cell.dataset.col;
            editor.chain().focus().insertTable({ rows: r, cols: c, withHeaderRow: true }).run();
            drop.classList.remove('open');
        });
    });

    document.addEventListener('click', () => drop.classList.remove('open'));
    wrap.appendChild(btn);
    wrap.appendChild(drop);
    return { el: wrap, activeCheck: null };
}

// ─────────────────────────────────────────────
// Code block language dropdown
// ─────────────────────────────────────────────
const CODE_LANGUAGES = [
    { value: 'auto',       label: 'Auto Detect' },
    { value: 'javascript', label: 'JavaScript' },
    { value: 'typescript', label: 'TypeScript' },
    { value: 'php',        label: 'PHP' },
    { value: 'html',       label: 'HTML' },
    { value: 'css',        label: 'CSS' },
    { value: 'json',       label: 'JSON' },
    { value: 'sql',        label: 'SQL' },
    { value: 'bash',       label: 'Bash / Shell' },
    { value: 'python',     label: 'Python' },
    { value: 'nginx',      label: 'Nginx' },
    { value: 'yaml',       label: 'YAML' },
];

function makeCodeBlockBtn(editor) {
    const wrap = document.createElement('div');
    wrap.className = 'tiptap-tb-dropdown-wrap';

    const btn = document.createElement('button');
    btn.type = 'button';
    btn.title = 'Code Block';
    btn.className = 'tiptap-tb-btn';
    btn.innerHTML = ICON.code;

    const drop = document.createElement('div');
    drop.className = 'tiptap-tb-dropdown tiptap-tb-dropdown-list';
    CODE_LANGUAGES.forEach(lang => {
        const item = document.createElement('button');
        item.type = 'button';
        item.className = 'tiptap-tb-dropdown-item';
        item.textContent = lang.label;
        item.addEventListener('click', () => {
            const language = lang.value === 'auto' ? null : lang.value;
            editor.chain().focus().setCodeBlock({ language }).run();
            drop.classList.remove('open');
        });
        drop.appendChild(item);
    });

    btn.addEventListener('click', (e) => {
        e.stopPropagation();
        const allDrops = document.querySelectorAll('.tiptap-tb-dropdown.open');
        allDrops.forEach(d => d !== drop && d.classList.remove('open'));
        drop.classList.toggle('open');
    });
    document.addEventListener('click', () => drop.classList.remove('open'));

    wrap.appendChild(btn);
    wrap.appendChild(drop);
    return { el: wrap, activeCheck: (ed) => ed.isActive('codeBlock') };
}

// ─────────────────────────────────────────────
// Build toolbar
// ─────────────────────────────────────────────
function buildToolbar(editor, container) {
    const toolbar = document.createElement('div');
    toolbar.className = 'tiptap-toolbar';

    const groups = [
        // Group 1: inline styles
        [
            makeBtn(editor, 'Bold',       ICON.bold,      ed => ed.chain().focus().toggleBold().run(),      ed => ed.isActive('bold')),
            makeBtn(editor, 'Italic',     ICON.italic,    ed => ed.chain().focus().toggleItalic().run(),    ed => ed.isActive('italic')),
            makeBtn(editor, 'Underline',  ICON.underline, ed => ed.chain().focus().toggleUnderline().run(), ed => ed.isActive('underline')),
            makeBtn(editor, 'Strikethrough', ICON.strike, ed => ed.chain().focus().toggleStrike().run(),    ed => ed.isActive('strike')),
        ],
        // Group 2: headings + paragraph
        [
            makeBtn(editor, 'Heading 1', ICON.h1, ed => ed.chain().focus().toggleHeading({ level: 1 }).run(), ed => ed.isActive('heading', { level: 1 })),
            makeBtn(editor, 'Heading 2', ICON.h2, ed => ed.chain().focus().toggleHeading({ level: 2 }).run(), ed => ed.isActive('heading', { level: 2 })),
            makeBtn(editor, 'Heading 3', ICON.h3, ed => ed.chain().focus().toggleHeading({ level: 3 }).run(), ed => ed.isActive('heading', { level: 3 })),
            makeBtn(editor, 'Paragraph', ICON.p,  ed => ed.chain().focus().setParagraph().run(),             ed => ed.isActive('paragraph')),
        ],
        // Group 3: alignment
        [
            makeBtn(editor, 'Align Left',    ICON.alignLeft,    ed => ed.chain().focus().setTextAlign('left').run(),    ed => ed.isActive({ textAlign: 'left' })),
            makeBtn(editor, 'Align Center',  ICON.alignCenter,  ed => ed.chain().focus().setTextAlign('center').run(),  ed => ed.isActive({ textAlign: 'center' })),
            makeBtn(editor, 'Align Right',   ICON.alignRight,   ed => ed.chain().focus().setTextAlign('right').run(),   ed => ed.isActive({ textAlign: 'right' })),
            makeBtn(editor, 'Justify',       ICON.alignJustify, ed => ed.chain().focus().setTextAlign('justify').run(), ed => ed.isActive({ textAlign: 'justify' })),
        ],
        // Group 4: lists
        [
            makeBtn(editor, 'Bullet List',   ICON.ul,      ed => ed.chain().focus().toggleBulletList().run(),  ed => ed.isActive('bulletList')),
            makeBtn(editor, 'Ordered List',  ICON.ol,      ed => ed.chain().focus().toggleOrderedList().run(), ed => ed.isActive('orderedList')),
            makeBtn(editor, 'Outdent',       ICON.outdent, ed => ed.chain().focus().liftListItem('listItem').run(), null),
            makeBtn(editor, 'Indent',        ICON.indent,  ed => ed.chain().focus().sinkListItem('listItem').run(), null),
        ],
        // Group 5: insert
        [
            makeBtn(editor, 'Link',       ICON.link,       ed => promptLink(ed),  ed => ed.isActive('link')),
            makeBtn(editor, 'Image',      ICON.image,      ed => promptImage(ed), null),
            makeTableBtn(editor),
            makeBtn(editor, 'Horizontal Rule', ICON.hr,   ed => ed.chain().focus().setHorizontalRule().run(), null),
            makeCodeBlockBtn(editor),
            makeBtn(editor, 'Blockquote', ICON.blockquote, ed => ed.chain().focus().toggleBlockquote().run(), ed => ed.isActive('blockquote')),
        ],
        // Group 6: history + fullscreen
        [
            makeBtn(editor, 'Undo', ICON.undo, ed => ed.chain().focus().undo().run(), null),
            makeBtn(editor, 'Redo', ICON.redo, ed => ed.chain().focus().redo().run(), null),
            {
                el: (() => {
                    const b = document.createElement('button');
                    b.type = 'button';
                    b.title = 'Fullscreen';
                    b.className = 'tiptap-tb-btn tiptap-fullscreen-btn';
                    b.innerHTML = ICON.fullscreen;
                    b.addEventListener('click', () => {
                        const isFs = container.classList.toggle('tiptap-fullscreen');
                        b.innerHTML = isFs ? ICON.exitFullscreen : ICON.fullscreen;
                        b.title = isFs ? 'Exit Fullscreen' : 'Fullscreen';
                    });
                    return b;
                })(),
                activeCheck: null,
            },
        ],
    ];

    const allBtns = [];
    groups.forEach((group, gi) => {
        if (gi > 0) {
            const sep = document.createElement('span');
            sep.className = 'tiptap-tb-sep';
            toolbar.appendChild(sep);
        }
        group.forEach(({ el, activeCheck }) => {
            toolbar.appendChild(el);
            if (activeCheck) allBtns.push({ el: el.tagName === 'BUTTON' ? el : el.querySelector('button'), activeCheck });
        });
    });

    // Update active states on selection change
    editor.on('selectionUpdate', () => syncActive(allBtns, editor));
    editor.on('transaction',     () => syncActive(allBtns, editor));

    return toolbar;
}

function syncActive(btns, editor) {
    btns.forEach(({ el, activeCheck }) => {
        if (!el) return;
        el.classList.toggle('is-active', activeCheck(editor));
    });
}

// ─────────────────────────────────────────────
// Public init function
// ─────────────────────────────────────────────
export function initTiptap(inputId, options = {}) {
    const hiddenInput = document.getElementById(inputId);
    if (!hiddenInput) return;

    const container = document.createElement('div');
    container.className = 'tiptap-editor';

    const editorArea = document.createElement('div');
    editorArea.className = 'tiptap-content-area';

    hiddenInput.parentNode.insertBefore(container, hiddenInput.nextSibling);

    const editor = new Editor({
        element: editorArea,
        extensions: [
            StarterKit.configure({
                codeBlock: false, // replaced by CodeBlockLowlight
            }),
            Underline,
            Link.configure({ openOnClick: false }),
            Image,
            Table.configure({ resizable: false }),
            TableRow,
            TableHeader,
            TableCell,
            TextAlign.configure({ types: ['heading', 'paragraph'] }),
            TextStyle,
            Color,
            CodeBlockLowlight.configure({ lowlight }),
        ],
        content: hiddenInput.value || options.content || '',
        onUpdate({ editor }) {
            hiddenInput.value = editor.getHTML();
        },
    });

    const toolbar = buildToolbar(editor, container);
    container.appendChild(toolbar);
    container.appendChild(editorArea);
}

window.initTiptap = initTiptap;
