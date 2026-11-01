import grapesjs from 'grapesjs';
import presetWebpage from 'grapesjs-preset-webpage';
import 'grapesjs/dist/css/grapes.min.css';

const container = document.getElementById('gjs');

if (container) {
    const form = document.getElementById('preorder-form');
    const contentField = document.getElementById('content-field');
    const previewDialog = document.getElementById('preview-dialog');
    const previewBody = document.getElementById('preview-body');
    const template = document.createElement('template');
    template.innerHTML = contentField.value;

    const savedStyles = template.content.querySelector('style[data-cms-editor-style]');
    const initialStyles = savedStyles?.textContent ?? '';
    savedStyles?.remove();

    const blocks = [
        { id: 'heading', label: 'Heading', category: 'Text', content: '<h2>Judul</h2>' },
        { id: 'paragraph', label: 'Paragraph', category: 'Text', content: '<p>Tulis paragraf di sini.</p>' },
        { id: 'image', label: 'Image', category: 'Media', content: { type: 'image' } },
        { id: 'divider', label: 'Divider', category: 'Layout', content: '<hr>' },
        { id: 'columns-1', label: '1 Kolom', category: 'Kolom', content: '<div class="preorder-columns preorder-columns--1"><div>Kolom</div></div>' },
        { id: 'columns-2', label: '2 Kolom', category: 'Kolom', content: '<div class="preorder-columns preorder-columns--2"><div>Kolom 1</div><div>Kolom 2</div></div>' },
        { id: 'columns-3', label: '3 Kolom', category: 'Kolom', content: '<div class="preorder-columns preorder-columns--3"><div>Kolom 1</div><div>Kolom 2</div><div>Kolom 3</div></div>' },
        { id: 'columns-4', label: '4 Kolom', category: 'Kolom', content: '<div class="preorder-columns preorder-columns--4"><div>Kolom 1</div><div>Kolom 2</div><div>Kolom 3</div><div>Kolom 4</div></div>' },
        { id: 'spacer', label: 'Spacer', category: 'Layout', content: '<div style="height: 40px;"></div>' },
        { id: 'custom-html', label: 'HTML custom', category: 'Advanced', content: '<div data-gjs-type="text">HTML custom</div>' },
    ];

    const editor = grapesjs.init({
        container,
        height: '650px',
        storageManager: false,
        noticeOnUnload: false,
        plugins: [presetWebpage],
        pluginsOpts: {
            [presetWebpage]: { blocks: [] },
        },
        blockManager: {
            appendTo: '#gjs-blocks',
            blocks,
        },
    });

    editor.setComponents(template.innerHTML);
    editor.setStyle(initialStyles || `
        .preorder-columns { display: grid; gap: 1rem; }
        .preorder-columns--1 { grid-template-columns: 1fr; }
        .preorder-columns--2 { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .preorder-columns--3 { grid-template-columns: repeat(3, minmax(0, 1fr)); }
        .preorder-columns--4 { grid-template-columns: repeat(4, minmax(0, 1fr)); }
        @media (max-width: 640px) {
            .preorder-columns--2, .preorder-columns--3, .preorder-columns--4 { grid-template-columns: 1fr; }
        }
    `);

    form.addEventListener('submit', () => {
        contentField.value = `${editor.getHtml()}<style data-cms-editor-style>${editor.getCss()}</style>`;
    });

    window.previewContent = () => {
        previewBody.innerHTML = `${editor.getHtml()}<style>${editor.getCss()}</style>`;
        previewDialog.showModal();
    };
}
