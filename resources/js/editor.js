import tinymce from 'tinymce/tinymce';

// Core UI & theme
import 'tinymce/icons/default';
import 'tinymce/themes/silver';
import 'tinymce/models/dom';

// Plugin yang dipakai
import 'tinymce/plugins/lists';
import 'tinymce/plugins/link';
import 'tinymce/plugins/table';
import 'tinymce/plugins/image';
import 'tinymce/plugins/code';
import 'tinymce/plugins/fullscreen';
import 'tinymce/plugins/charmap';
import 'tinymce/plugins/searchreplace';
import 'tinymce/plugins/visualblocks';

// Skin & content css (self-hosted)
import 'tinymce/skins/ui/oxide/skin.css';
import 'tinymce/skins/content/default/content.css';

window.tinymce = tinymce;

const uploadLogo = async (file) => {
    const image = await new Promise((resolve, reject) => {
        const reader = new FileReader();
        reader.onload = () => resolve(reader.result);
        reader.onerror = () => reject(new Error('Gagal membaca file gambar.'));
        reader.readAsDataURL(file);
    });

    const response = await window.axios.post('/documents/logo', { image });
    return response.data.url;
};

const imagesUploadHandler = (blobInfo) =>
    new Promise((resolve, reject) => {
        const formData = new FormData();
        formData.append('file', blobInfo.blob(), blobInfo.filename());

        window.axios
            .post('/documents/image', formData, {
                headers: { 'Content-Type': 'multipart/form-data' },
            })
            .then((response) => resolve(response.data.url))
            .catch((error) => {
                console.error(error);
                reject('Gagal mengunggah gambar.');
            });
    });

const enableLogoDragging = (editor) => {
    const body = editor.getBody();
    body.style.position = 'relative';

    let logo = null;
    let offsetX = 0;
    let offsetY = 0;

    body.addEventListener('mousedown', (event) => {
        if (event.target.tagName !== 'IMG' || !event.target.classList.contains('document-logo')) return;

        const logoRect = event.target.getBoundingClientRect();
        logo = event.target;
        offsetX = event.clientX - logoRect.left;
        offsetY = event.clientY - logoRect.top;
        event.preventDefault();
        logo.style.cursor = 'grabbing';
    });

    body.addEventListener('mousemove', (event) => {
        if (!logo) return;

        const bodyRect = body.getBoundingClientRect();
        const logoRect = logo.getBoundingClientRect();
        const printableHeaderWidth = Math.min(bodyRect.width, 634);
        const logoColumnWidth = 190;
        const horizontalMargin = 10;
        const minX = horizontalMargin;
        const maxX = Math.max(
            minX,
            Math.min(printableHeaderWidth, logoColumnWidth) - logoRect.width - horizontalMargin,
        );
        const maxY = Math.max(0, bodyRect.height - logoRect.height);
        const left = Math.max(minX, Math.min(event.clientX - bodyRect.left - offsetX, maxX));
        const top = Math.max(0, Math.min(event.clientY - bodyRect.top - offsetY, maxY));

        logo.style.left = `${left}px`;
        logo.style.top = `${top}px`;
    });

    body.addEventListener('mouseup', () => {
        if (!logo) return;
        logo.style.cursor = 'grab';
        logo = null;
        editor.save();
    });
};


const registerSharedToolbarVisibility = (editor) => {
    editor.on('init', function () {
        const container = editor.getContainer();
        if (container) {
            container.style.maxWidth = '100%';
            container.style.width = '100%';
        }
    });

    editor.on('focus', function () {
        const wrapper = document.getElementById('body-toolbar-container');
        if (!wrapper) return;

        Array.from(wrapper.children).forEach((child) => {
            child.classList.remove('active-page-toolbar');
        });

        const container = editor.getContainer();
        if (container) {
            container.classList.add('active-page-toolbar');
        }
    });
};

// Dipakai buat editor Header & Footer di halaman "Buat Dokumen Baru".
window.initDocumentEditor = function (selector, initialContent = '', allowLogoUpload = true) {
    tinymce.init({
        selector: selector,

        license_key: 'gpl',

        height: 400,
        menubar: false,
        branding: false,
        statusbar: false,

        relative_urls: false,
        remove_script_host: false,
        convert_urls: false,

        plugins: 'lists link table image',

        toolbar: `undo redo | bold italic underline | alignleft aligncenter alignright alignjustify | bullist numlist | table link image${allowLogoUpload ? ' uploadlogo' : ''} | removeformat`,
        content_style: 'body { position: relative; font-family: Georgia, serif; font-size: 13px; } body > p, body > h1, body > h2, body > h3, body > h4, body > h5, body > h6, body > ul, body > ol, body > table, body > blockquote, body > div { position: relative; z-index: 2; } body.has-document-logo > p, body.has-document-logo > h1, body.has-document-logo > h2, body.has-document-logo > h3, body.has-document-logo > h4, body.has-document-logo > h5, body.has-document-logo > h6 { margin-left: 190px; } .document-logo { position: absolute; z-index: 1; width: auto; max-width: 150px; height: auto; max-height: 70px; cursor: grab; }',
        skin: false,
        content_css: false,
        images_upload_handler: imagesUploadHandler,
        setup: function (editor) {
            if (allowLogoUpload) {
                editor.ui.registry.addButton('uploadlogo', {
                    icon: 'image',
                    text: 'Logo',
                    tooltip: 'Unggah logo dari perangkat',
                    onAction: () => {
                        const input = document.createElement('input');
                        input.type = 'file';
                        input.accept = 'image/png,image/jpeg,image/svg+xml';

                        input.addEventListener('change', async () => {
                            const file = input.files?.[0];
                            if (!file) return;

                            editor.setProgressState(true);
                            try {
                                const url = await uploadLogo(file);
                                const image = document.createElement('img');
                                image.src = url;
                                image.alt = file.name;
                                image.className = 'document-logo';
                                image.style.maxWidth = '150px';
                                image.style.maxHeight = '70px';
                                image.style.width = 'auto';
                                image.style.height = 'auto';
                                image.style.position = 'absolute';
                                image.style.left = '10px';
                                image.style.top = '10px';
                                image.style.zIndex = '1';
                                image.style.cursor = 'grab';
                                const body = editor.getBody();
                                body.classList.add('has-document-logo');
                                body.insertBefore(image, body.firstChild);
                                editor.nodeChanged();
                                editor.save();
                            } catch (error) {
                                window.Swal?.fire({
                                    icon: 'error',
                                    title: 'Gagal',
                                    text: 'Gagal mengunggah gambar.',
                                    confirmButtonColor: '#1B2A4A',
                                });
                                console.error(error);
                            } finally {
                                editor.setProgressState(false);
                            }
                        });

                        input.click();
                    },
                });
            }

            editor.on('init', function () {
                if (initialContent) editor.setContent(initialContent);
                if (allowLogoUpload) enableLogoDragging(editor);
            });
            editor.on('change keyup', function () {
                editor.save();
            });
        }
    });
};

window.initBodyEditor = function (selector, initialContent = '', onSync = null, allowLogoUpload = false) {

    tinymce.init({
        selector: selector,

        license_key: 'gpl',

        height: 500,

        menubar: false,
        branding: false,
        statusbar: false,

        plugins: 'lists link table image code fullscreen charmap searchreplace visualblocks',

        toolbar:
            'undo redo | blocks | fontfamily fontsizeinput | ' +
            'bold italic underline strikethrough | ' +
            'forecolor backcolor | ' +
            'alignleft aligncenter alignright alignjustify | ' +
            'outdent indent | bullist numlist | ' +
            'superscript subscript | ' +
            `table link image charmap hr kopdivider${allowLogoUpload ? ' uploadlogo' : ''} | ` +
            'searchreplace removeformat code fullscreen',

        toolbar_mode: 'wrap',

        fixed_toolbar_container: '#body-toolbar-container',

        toolbar_persist: true,

        inline: true,

        object_resizing: 'table',

        content_style: `
            body {
                font-family: Arial, sans-serif;
                font-size: 14px;
                line-height: 1.8;
                margin: 0;
                padding: 0;
                overflow-wrap: break-word;
                word-break: break-word;
            }

            .doc-sheet {
                position: relative;
                width: 210mm;
                min-height: 297mm;
                height: 297mm;
                max-height: 297mm;
                margin: 0 auto 24px;
                box-sizing: border-box;
                overflow: hidden;
                background: white;
                box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
                padding: 20mm 20mm;
                font-family: Arial, sans-serif;
                font-size: 14px;
                line-height: 1.6;
                color: #111827;
                outline: none;
                display: flex;
                flex-direction: column;
            }

            .doc-sheet-header {
                position: relative;
                z-index: 0;
                min-height: 40px;
                padding-bottom: 8px;
            }

            .doc-sheet-body {
                position: relative;
                z-index: 0;
                flex: 1;
                min-height: 0;
            }

            .doc-sheet-footer {
                position: relative;
                z-index: 0;
                min-height: 40px;
                padding-top: 8px;
            }

            .doc-sheet p {
                margin: 0 0 10px 0;
                padding: 0;
            }

            .doc-sheet p+p {
                margin-top: 4px;
            }

            .doc-sheet p:last-child {
                margin-bottom: 0;
            }

            .doc-sheet h1 {
                font-size: 28px;
                line-height: 1.3;
                font-weight: 700;
                margin: 18px 0 12px;
            }

            .doc-sheet h2 {
                font-size: 22px;
                line-height: 1.3;
                font-weight: 700;
                margin: 16px 0 10px;
            }

            .doc-sheet h3 {
                font-size: 18px;
                line-height: 1.35;
                font-weight: 700;
                margin: 14px 0 8px;
            }

            .doc-sheet>h1:first-child,
            .doc-sheet>h2:first-child,
            .doc-sheet>h3:first-child,
            .doc-sheet>h4:first-child,
            .doc-sheet>h5:first-child,
            .doc-sheet>h6:first-child {
                margin-top: 0;
            }

            .doc-sheet ul,
            .doc-sheet ol {
                margin-top: 8px;
                margin-bottom: 12px;
                padding-left: 30px;
            }

            .doc-sheet li {
                margin-bottom: 4px;
            }

            .doc-sheet blockquote {
                margin: 14px 0;
                padding-left: 15px;
                border-left: 3px solid #ccc;
                font-style: italic;
            }

            .doc-sheet table {
                width: 100%;
                border-collapse: collapse !important;
                border-spacing: 0 !important;
                margin: 14px 0;
                table-layout: fixed;
            }

            .doc-sheet table,
            .doc-sheet tr,
            .doc-sheet td,
            .doc-sheet th {
                box-sizing: border-box;
            }

            .doc-sheet table th,
            .doc-sheet table td {
                border: 1px solid #374151 !important;
                padding: 8px 10px !important;
                min-width: 60px;
                height: 32px;
                vertical-align: top;
            }

            .doc-sheet th {
                font-weight: 700;
                background: #f3f4f6;
            }

            .doc-sheet td p,
            .doc-sheet th p {
                margin: 0 !important;
            }

            .doc-sheet img {
                display: block;
                margin-top: 8px;
                margin-bottom: 12px;
                cursor: pointer !important;
                max-width: 100%;
            }

            .doc-sheet img.image-selected {
                outline: 1px solid #2563eb !important;
                outline-offset: 1px;
                user-select: none !important;
                -webkit-user-drag: none !important;
            }
        `,

        skin: false,
        content_css: false,

        images_upload_handler: imagesUploadHandler,

        setup: function (editor) {

            registerSharedToolbarVisibility(editor);
            registerImageLayoutTools(editor);
            registerToolbarTooltips();

            // =========================================
            // CTRL+A: PILIH ISI KONTEN SAJA
            // Struktur kertas (.doc-sheet) tidak boleh ikut
            // terseleksi/terhapus saat select-all + delete.
            // =========================================
            const selectPageContentOnly = () => {
                try {
                    const node = editor.selection.getNode();
                    if (!node) return false;

                    const container =
                        editor.dom.getParent(node, '.doc-sheet-body, .doc-sheet-header, .doc-sheet-footer') ||
                        editor.dom.getParent(node, '.doc-sheet')?.querySelector('.doc-sheet-body') ||
                        editor.getBody().querySelector('.doc-sheet-body');

                    if (!container) return false;

                    const rng = editor.dom.createRng();
                    rng.selectNodeContents(container);
                    editor.selection.setRng(rng);
                    return true;
                } catch (err) {
                    return false;
                }
            };

            editor.on('keydown', (e) => {
                if (!(e.ctrlKey || e.metaKey) || e.altKey || e.shiftKey) return;
                if ((e.key || '').toLowerCase() !== 'a') return;

                if (selectPageContentOnly()) {
                    e.preventDefault();
                    e.stopPropagation();
                }
            }, true);

            // Jaring pengaman: pastikan struktur kertas selalu ada,
            // dan TANPA menyisakan paragraf/elemen kosong (tidak bikin celah)
            const isEmptyBlock = (el) => {
                if (!el || (el.nodeName !== 'P' && el.nodeName !== 'DIV')) return false;
                if (el.classList.contains('doc-signature')) return false;
                if (el.querySelector('img, table, ul, ol, hr, h1, h2, h3, h4, h5, h6, a, span')) return false;
                return (el.textContent || '').trim() === '';
            };

            const ensureDocumentStructure = () => {
                const body = editor.getBody();
                if (!body) return;

                // 1. Buang elemen liar yang nyasar langsung di bawah editor
                //    (di luar kertas) -> ini sumber "celah" di atas kertas
                Array.from(body.childNodes).forEach((child) => {
                    if (child.nodeType === 1 && !(child.classList && child.classList.contains('doc-sheet'))) {
                        editor.dom.remove(child);
                    }
                });

                // 2. Minimal satu kertas harus selalu ada
                if (!body.querySelector('.doc-sheet[data-sheet-type="page"]')) {
                    const sheet = editor.dom.create('div', {
                        class: 'doc-sheet',
                        'data-sheet-type': 'page',
                        'data-page-uid': 'page-' + Date.now()
                    });
                    body.appendChild(sheet);
                }

                // 3. Tiap kertas wajib punya region body
                body.querySelectorAll('.doc-sheet[data-sheet-type="page"]').forEach((sheet) => {
                    if (!sheet.querySelector('.doc-sheet-body[data-region="body"]')) {
                        const region = editor.dom.create('div', {
                            class: 'doc-sheet-body',
                            'data-region': 'body'
                        });

                        const footer = sheet.querySelector('.doc-sheet-footer');
                        if (footer) sheet.insertBefore(region, footer);
                        else sheet.appendChild(region);
                    }
                });

                // 4. Region yang isinya CUMA paragraf kosong:
                //    - kursor masih di dalam region -> sisakan TEPAT SATU baris
                //      kosong (tempat kedipan kursor, hilang saat pindah klik)
                //    - kursor sudah di luar          -> buang semuanya,
                //      kertas bersih total tanpa celah
                body.querySelectorAll(
                    '.doc-sheet-body[data-region="body"], .doc-sheet-header[data-region="header"], .doc-sheet-footer[data-region="footer"]'
                ).forEach((region) => {
                    const blocks = Array.from(region.children);
                    if (blocks.length === 0 || !blocks.every(isEmptyBlock)) return;

                    let caretInside = false;
                    try {
                        const node = editor.selection.getNode();
                        caretInside = !!(node && region.contains(node));
                    } catch (err) {
                        caretInside = false;
                    }

                    if (caretInside) {
                        blocks.slice(1).forEach((b) => editor.dom.remove(b));
                    } else {
                        blocks.forEach((b) => editor.dom.remove(b));
                    }
                });
            };

            editor.on('SetContent change keyup NodeChange', ensureDocumentStructure);

            // Klik pada region yang benar-benar kosong -> siapkan SATU
            // paragraf kosong sebagai tempat kursor SEBELUM caret ditempatkan,
            // supaya halaman yang sudah dibersihkan tetap bisa diketik lagi.
            editor.on('mousedown', (e) => {
                const target = e.target;
                if (!target || !target.closest) return;
                const region = target.closest('.doc-sheet-body, .doc-sheet-header, .doc-sheet-footer');
                if (!region) return;
                if (region.firstElementChild) return;

                const p = editor.dom.create('p');
                p.innerHTML = '<br data-mce-bogus="1">';
                region.appendChild(p);
            });

            editor.ui.registry.addButton('kopdivider', {
                icon: 'horizontal-rule',
                text: '===',
                tooltip: 'Sisipkan garis pembatas kop surat',
                onAction: () => {
                    editor.insertContent(
                        '<hr class="kop-divider" style="border:none;border-top:2px solid #1B2A4A;margin:8px 0;" />'
                    );
                },
            });

            if (allowLogoUpload) {
                editor.ui.registry.addButton('uploadlogo', {
                    icon: 'image',
                    text: 'Logo',
                    tooltip: 'Unggah logo dari perangkat',
                    onAction: () => {
                        const input = document.createElement('input');
                        input.type = 'file';
                        input.accept = 'image/png,image/jpeg,image/svg+xml';

                        input.addEventListener('change', async () => {
                            const file = input.files?.[0];
                            if (!file) return;

                            editor.setProgressState(true);
                            try {
                                const url = await uploadLogo(file);
                                editor.insertContent(`<img src="${url}" style="max-width:150px;max-height:70px;" />`);
                            } catch (error) {
                                window.Swal?.fire({
                                    icon: 'error',
                                    title: 'Gagal',
                                    text: 'Gagal mengunggah gambar.',
                                    confirmButtonColor: '#1B2A4A',
                                });
                                console.error(error);
                            } finally {
                                editor.setProgressState(false);
                            }
                        });

                        input.click();
                    },
                });
            }

            editor.on('init', function () {

                if (initialContent) {
                    editor.setContent(initialContent);
                }

                const wrapper = document.getElementById('body-toolbar-container');
                if (wrapper) {
                    const hasActive = Array.from(wrapper.children).some((child) =>
                        child.classList.contains('active-page-toolbar')
                    );
                    if (!hasActive) {
                        const container = editor.getContainer();
                        if (container) {
                            container.classList.add('active-page-toolbar');
                        }
                    }
                }
            });

            const ensureTrailingParagraph = () => {
                const body = editor.getBody();
                const last = body.lastElementChild;

                if (last && last.nodeName === 'IMG') {
                    const p = editor.dom.create('p');
                    p.innerHTML = '<br data-mce-bogus="1">';
                    body.appendChild(p);
                }
            };

            editor.on('NodeChange SetContent', ensureTrailingParagraph);

            editor.on('SetContent NodeChange', () => {
                editor.getBody()
                    .querySelectorAll('img:not([data-drag-disabled])')
                    .forEach((img) => {
                        img.setAttribute('draggable', 'false');
                        img.style.webkitUserDrag = 'none';
                        img.dataset.dragDisabled = '1';
                        img.addEventListener('dragstart', (e) => e.preventDefault());
                    });
            });

            editor.on('change keyup undo redo', function () {
                editor.save();
                if (typeof onSync === 'function') {
                    onSync(editor.getContent());
                }
            });
        }
    });
};

// =========================================
// IMAGE LAYOUT TOOLS (versi simpel: bubble + resize saja)
// =========================================

let activeImage = null;
let activeEditor = null;
let bubbleEl = null;
let removeBtnEl = null;
let dragSurfaceEl = null;
let panelEl = null;
let handleEls = [];
let isResizingImage = false;
let resizeStartW = 0, resizeStartX = 0, resizeCorner = null;
let watchTimer = null;

// Semua editor yang memasang image tools (dipakai listener global
// untuk menemukan editor pemilik gambar)
let registeredImageToolEditors = [];

// State drag bebas untuk gambar floating (behind/front text)
let floatingImg = null;
let floatingRegion = null;
let floatingDragArmed = false;
let isDraggingFloating = false;
let floatStartX = 0, floatStartY = 0;
let floatBaseLeft = 0, floatBaseTop = 0;

const removeImageTools = () => {
    clearInterval(watchTimer);
    watchTimer = null;
    bubbleEl?.remove();
    removeBtnEl?.remove();
    dragSurfaceEl?.remove();
    panelEl?.remove();
    handleEls.forEach((h) => h.remove());
    bubbleEl = null;
    removeBtnEl = null;
    dragSurfaceEl = null;
    panelEl = null;
    handleEls = [];
    activeImage = null;
    activeEditor = null;
};

const positionImageTools = () => {
    if (!activeImage || !activeImage.isConnected) {
        removeImageTools();
        return;
    }

    const rect = activeImage.getBoundingClientRect();

    if (bubbleEl) {
        bubbleEl.style.left = (rect.right - 12) + 'px';
        bubbleEl.style.top = (rect.top - 12) + 'px';
    }

    if (removeBtnEl) {
        removeBtnEl.style.left = (rect.right - 12 - 32) + 'px';
        removeBtnEl.style.top = (rect.top - 12) + 'px';
    }

    // Permukaan drag menutupi area gambar selama mode edit aktif
    if (dragSurfaceEl) {
        dragSurfaceEl.style.left = rect.left + 'px';
        dragSurfaceEl.style.top = rect.top + 'px';
        dragSurfaceEl.style.width = rect.width + 'px';
        dragSurfaceEl.style.height = rect.height + 'px';
    }

    const corners = [
        ['nw', rect.left, rect.top],
        ['ne', rect.right, rect.top],
        ['sw', rect.left, rect.bottom],
        ['se', rect.right, rect.bottom],
    ];

    handleEls.forEach((h, i) => {
        const [, x, y] = corners[i];
        h.style.left = x + 'px';
        h.style.top = y + 'px';
    });
};

// =========================================
// HELPER GAMBAR FLOATING (MS WORD STYLE)
// =========================================

const FLOAT_REGION_SELECTOR = '.doc-sheet-body, .doc-sheet-header, .doc-sheet-footer';

const isFloatingImage = (img) => getComputedStyle(img).position === 'absolute';

const getImageLayout = (img) => {
    if (!img) return 'inline';
    if (isFloatingImage(img)) {
        const z = parseInt(getComputedStyle(img).zIndex, 10);
        return z < 0 ? 'behind' : 'front';
    }
    const f = getComputedStyle(img).cssFloat;
    if (f === 'left' || f === 'right') return 'square';
    if (getComputedStyle(img).display === 'block') return 'topbottom';
    return 'inline';
};

// Pastikan region masih punya minimal satu blok untuk menaruh kursor
const ensureRegionHasBlock = (editor, region) => {
    const hasBlock = Array.from(region.children).some((el) =>
        ['P', 'DIV', 'H1', 'H2', 'H3', 'H4', 'H5', 'H6', 'UL', 'OL', 'TABLE', 'BLOCKQUOTE', 'HR']
            .includes(el.nodeName) &&
        !(el.classList && el.classList.contains('doc-signature'))
    );
    if (!hasBlock) {
        const p = editor.dom.create('p');
        p.innerHTML = '<br data-mce-bogus="1">';
        region.insertBefore(p, region.firstChild);
    }
};

// Bersihkan paragraf yang jadi kosong setelah gambarnya dipindah jadi floating
const cleanupAfterFloatMove = (editor, oldParent, region) => {
    try {
        if (
            oldParent &&
            oldParent.nodeType === 1 &&
            oldParent !== region &&
            region.contains(oldParent) &&
            !oldParent.matches(FLOAT_REGION_SELECTOR) &&
            !oldParent.querySelector('img') &&
            !(oldParent.textContent || '').trim() &&
            !oldParent.querySelector('table, hr, ul, ol')
        ) {
            editor.dom.remove(oldParent);
        }
    } catch (err) {
        // noop
    }
};

// Cari editor TinyMCE yang memuat node tertentu.
// Prioritas: registry internal (pasti terisi saat init),
// lalu tinymce.editors (kalau tersedia), lalu activeEditor.
const findEditorContaining = (node) => {
    const candidates = [
        ...registeredImageToolEditors,
        ...((window.tinymce && window.tinymce.editors) || []),
    ];

    for (let i = 0; i < candidates.length; i++) {
        const ed = candidates[i];
        try {
            if (ed?.getBody?.().contains(node)) return ed;
        } catch (err) {
            // noop
        }
    }

    try {
        if (activeEditor?.getBody?.().contains(node)) return activeEditor;
    } catch (err) {
        // noop
    }

    return null;
};

const applyImageLayout = (img, layout) => {
    const editor = activeEditor;
    if (!editor) return;

    const oldParent = img.parentElement;

    // Reset semua style layout dulu
    img.style.float = '';
    img.style.display = '';
    img.style.margin = '';
    img.style.position = '';
    img.style.left = '';
    img.style.top = '';
    img.style.zIndex = '';
    img.style.cursor = '';
    img.classList.remove('doc-image-behind', 'doc-image-front');

    const region = img.closest(FLOAT_REGION_SELECTOR);

    if ((layout === 'behind' || layout === 'front') && region) {
        // Catat posisi visual gambar SAAT INI relatif terhadap region,
        // supaya gambar tidak "lompat" saat berubah jadi floating.
        const imgRect = img.getBoundingClientRect();
        const regionRect = region.getBoundingClientRect();
        const left = Math.max(0, Math.round(imgRect.left - regionRect.left));
        const top = Math.max(0, Math.round(imgRect.top - regionRect.top));

        // Keluarkan gambar dari paragrafnya -> jadi anak langsung region.
        // Teks jadi mengalir normal di belakang / di depan gambar.
        region.appendChild(img);
        cleanupAfterFloatMove(editor, oldParent, region);
        ensureRegionHasBlock(editor, region);

        img.style.position = 'absolute';
        img.style.left = left + 'px';
        img.style.top = top + 'px';
        img.style.margin = '0';
        img.style.cursor = 'grab';
        img.setAttribute('draggable', 'false');

        if (layout === 'behind') {
            // z-index negatif = di bawah teks (region wajib stacking context,
            // sudah diatur via CSS .doc-sheet-body { z-index: 0 })
            img.style.zIndex = '-1';
            img.classList.add('doc-image-behind');
        } else {
            // z-index positif = di atas semua teks & tabel
            img.style.zIndex = '20';
            img.classList.add('doc-image-front');
        }
    }

    editor.save();
    editor.fire('change');
    editor.nodeChanged();

    requestAnimationFrame(positionImageTools);
};

const buildPanel = () => {
    panelEl?.remove();

    const options = [
        { key: 'inline', label: 'Sejajar dengan Teks' },
        { key: 'square', label: 'Persegi — Teks di Samping' },
        { key: 'topbottom', label: 'Atas dan Bawah' },
        { key: 'behind', label: 'Di Belakang Teks' },
        { key: 'front', label: 'Di Depan Teks' },
    ];

    const currentLayout = getImageLayout(activeImage);

    panelEl = document.createElement('div');
    panelEl.style.cssText =
        'position:fixed;z-index:999999;width:230px;background:#fff;border:1px solid #d6d3cc;' +
        'border-radius:10px;box-shadow:0 8px 24px rgba(0,0,0,.18);padding:8px;font-family:Arial,sans-serif;';

    panelEl.innerHTML =
        '<div style="font-size:12px;font-weight:700;color:#1B2A4A;margin-bottom:6px;padding:0 4px;">Atur Posisi Gambar</div>' +
        options.map((o) => {
            const active = o.key === currentLayout;
            return '<button type="button" data-layout="' + o.key + '"' + (active ? ' data-active="1"' : '') +
                ' style="display:block;width:100%;text-align:left;padding:7px 8px;border-radius:6px;' +
                (active
                    ? 'background:#1B2A4A;color:#fff;font-weight:600;'
                    : 'background:transparent;color:#1B2A4A;') +
                'border:none;cursor:pointer;font-size:12.5px;">' +
                (active ? '✓ ' : '') + o.label + '</button>';
        }).join('');

    document.body.appendChild(panelEl);

    const bubbleRect = bubbleEl.getBoundingClientRect();
    panelEl.style.left = Math.max(8, bubbleRect.left - 210) + 'px';
    panelEl.style.top = (bubbleRect.bottom + 6) + 'px';

    panelEl.querySelectorAll('button[data-layout]').forEach((btn) => {
        btn.addEventListener('mouseenter', () => {
            if (!btn.dataset.active) btn.style.background = '#f5f2eb';
        });
        btn.addEventListener('mouseleave', () => {
            if (!btn.dataset.active) btn.style.background = 'transparent';
        });
        btn.addEventListener('click', (e) => {
            e.stopPropagation();
            applyImageLayout(activeImage, btn.dataset.layout);
            panelEl?.remove();
            panelEl = null;
        });
    });
};

const startImageResize = (e, corner) => {
    e.preventDefault();
    e.stopPropagation();
    isResizingImage = true;
    resizeCorner = corner;
    resizeStartW = activeImage.offsetWidth;
    resizeStartX = e.clientX;
    const ratio = activeImage.offsetHeight / activeImage.offsetWidth;
    activeImage.dataset.ratio = ratio;
    document.body.style.userSelect = 'none';
};

const showImageTools = (editor, img) => {
    if (activeImage === img) return;
    removeImageTools();

    activeImage = img;
    activeEditor = editor;

    bubbleEl = document.createElement('div');
    bubbleEl.innerHTML = '⚓';
    bubbleEl.title = 'Atur Posisi Gambar';
    bubbleEl.style.cssText =
        'position:fixed;z-index:999999;width:26px;height:26px;border-radius:6px;background:#1B2A4A;' +
        'color:#fff;display:flex;align-items:center;justify-content:center;cursor:pointer;' +
        'box-shadow:0 2px 6px rgba(0,0,0,.25);font-size:13px;';
    document.body.appendChild(bubbleEl);

    bubbleEl.addEventListener('click', (e) => {
        e.stopPropagation();
        panelEl ? (panelEl.remove(), panelEl = null) : buildPanel();
    });

    // Tombol hapus cepat di samping anchor
    removeBtnEl = document.createElement('div');
    removeBtnEl.innerHTML = '✕';
    removeBtnEl.title = 'Hapus gambar';
    removeBtnEl.style.cssText =
        'position:fixed;z-index:999999;width:26px;height:26px;border-radius:6px;background:#dc2626;' +
        'color:#fff;display:flex;align-items:center;justify-content:center;cursor:pointer;' +
        'box-shadow:0 2px 6px rgba(0,0,0,.25);font-size:12px;';
    document.body.appendChild(removeBtnEl);

    removeBtnEl.addEventListener('click', (e) => {
        e.stopPropagation();
        if (!activeImage || !activeEditor) return;
        const ed = activeEditor;
        const img = activeImage;
        removeImageTools();
        ed.dom.remove(img);
        ed.save();
        ed.fire('change');
        ed.nodeChanged();
    });

    handleEls = ['nw', 'ne', 'sw', 'se'].map((corner) => {
        const h = document.createElement('div');
        h.style.cssText =
            'position:fixed;z-index:999999;width:10px;height:10px;background:#fff;border:2px solid #1B2A4A;' +
            `border-radius:50%;cursor:${corner === 'nw' || corner === 'se' ? 'nwse-resize' : 'nesw-resize'};`;
        document.body.appendChild(h);
        h.addEventListener('mousedown', (e) => startImageResize(e, corner));
        return h;
    });

    // Permukaan drag untuk gambar floating: supaya gambar "Di Belakang Teks"
    // tetap bisa digeser meski secara visual tertutup teks.
    if (isFloatingImage(img)) {
        dragSurfaceEl = document.createElement('div');
        dragSurfaceEl.title = 'Geser gambar';
        dragSurfaceEl.style.cssText =
            'position:fixed;z-index:999998;cursor:move;background:transparent;';
        document.body.appendChild(dragSurfaceEl);

        dragSurfaceEl.addEventListener('mousedown', (e) => {
            if (!activeImage || !isFloatingImage(activeImage)) return;
            const region = activeImage.closest(FLOAT_REGION_SELECTOR) || activeImage.closest('.doc-sheet');
            if (!region) return;

            e.preventDefault();
            floatingImg = activeImage;
            floatingRegion = region;
            floatingDragArmed = true;
            isDraggingFloating = false;
            floatStartX = e.clientX;
            floatStartY = e.clientY;
            floatBaseLeft = parseFloat(activeImage.style.left) || 0;
            floatBaseTop = parseFloat(activeImage.style.top) || 0;
        });
    }

    positionImageTools();

    const loop = () => {
        if (!activeImage) return;
        if (!activeImage.isConnected) {
            removeImageTools();
            return;
        }
        positionImageTools();
        watchTimer = requestAnimationFrame(loop);
    };
    watchTimer = requestAnimationFrame(loop);
};

if (!window.__imageToolsBound) {
    window.__imageToolsBound = true;

    document.addEventListener('mousemove', (e) => {
        if (isResizingImage && activeImage) {
            const deltaX = e.clientX - resizeStartX;
            const ratio = parseFloat(activeImage.dataset.ratio) || 1;
            let newW = resizeStartW;

            if (resizeCorner === 'ne' || resizeCorner === 'se') newW = resizeStartW + deltaX;
            else newW = resizeStartW - deltaX;

            newW = Math.max(30, newW);
            activeImage.style.width = newW + 'px';
            activeImage.style.height = (newW * ratio) + 'px';
            positionImageTools();
        }
    });

    document.addEventListener('mouseup', () => {
        if (isResizingImage) {
            activeEditor?.save();
            activeEditor?.fire('change');
        }
        isResizingImage = false;
        document.body.style.userSelect = '';
    });

    document.addEventListener('scroll', () => positionImageTools(), true);
    window.addEventListener('resize', () => positionImageTools());

    document.addEventListener('click', (e) => {
        if (e.target === bubbleEl || bubbleEl?.contains(e.target)) return;
        if (e.target === removeBtnEl || removeBtnEl?.contains(e.target)) return;
        if (e.target === dragSurfaceEl || dragSurfaceEl?.contains(e.target)) return;
        if (e.target === panelEl || panelEl?.contains(e.target)) return;
        if (handleEls.includes(e.target)) return;
        if (e.target.nodeName === 'IMG') return;
        removeImageTools();
    });

    // =========================================
    // DRAG BEBAS UNTUK GAMBAR FLOATING (behind/front text)
    // =========================================

    document.addEventListener('mousedown', (e) => {
        if (e.target?.nodeName !== 'IMG') return;
        const img = e.target;
        if (!isFloatingImage(img)) return;

        const region = img.closest(FLOAT_REGION_SELECTOR) || img.closest('.doc-sheet');
        if (!region) return;

        // Tanpa preventDefault: klik sekali pada gambar harus tetap
        // berperilaku normal (menaruh kursor teks). Drag baru aktif
        // setelah kursor bergerak melewati threshold di mousemove.
        floatingImg = img;
        floatingRegion = region;
        floatingDragArmed = true;
        isDraggingFloating = false;
        floatStartX = e.clientX;
        floatStartY = e.clientY;
        floatBaseLeft = parseFloat(img.style.left) || 0;
        floatBaseTop = parseFloat(img.style.top) || 0;
    });

    document.addEventListener('mousemove', (e) => {
        if (!floatingImg) return;

        // Aktifkan drag hanya setelah kursor bergerak > 4px,
        // supaya klik biasa tetap terdeteksi sebagai klik.
        if (floatingDragArmed && !isDraggingFloating) {
            if (Math.hypot(e.clientX - floatStartX, e.clientY - floatStartY) < 4) return;
            isDraggingFloating = true;
            try { window.getSelection()?.removeAllRanges(); } catch (err) { /* noop */ }
            floatingImg.style.cursor = 'grabbing';
            document.body.style.userSelect = 'none';
        }

        if (!isDraggingFloating) return;
        e.preventDefault();

        const regionRect = floatingRegion.getBoundingClientRect();
        const maxX = Math.max(0, regionRect.width - floatingImg.offsetWidth);
        const maxY = Math.max(0, regionRect.height - floatingImg.offsetHeight);
        const nextLeft = Math.min(Math.max(floatBaseLeft + (e.clientX - floatStartX), 0), maxX);
        const nextTop = Math.min(Math.max(floatBaseTop + (e.clientY - floatStartY), 0), maxY);

        floatingImg.style.left = nextLeft + 'px';
        floatingImg.style.top = nextTop + 'px';
        positionImageTools();
    });

    document.addEventListener('mouseup', () => {
        if (!floatingImg) return;

        const wasDragged = isDraggingFloating;
        floatingImg.style.cursor = 'grab';
        floatingImg = null;
        floatingRegion = null;
        floatingDragArmed = false;
        isDraggingFloating = false;
        document.body.style.userSelect = '';

        if (wasDragged && activeEditor) {
            activeEditor.save();
            activeEditor.fire('change');
        }
    });

    // =========================================
    // KLIK DUA KALI UNTUK GAMBAR YANG TERTUTUP TEKS
    // Klik sekali pada gambar yang terlihat langsung membuka tools.
    // Gambar "Di Belakang Teks" yang tertutup teks tidak bisa
    // diklik langsung — klik dua kali akan menembus teks dan
    // memilihnya (elementsFromPoint).
    // =========================================

    document.addEventListener('dblclick', (e) => {
        const stack = document.elementsFromPoint(e.clientX, e.clientY);
        const img = stack.find((el) => el.nodeName === 'IMG');
        if (!img) return;

        // Kalau gambar ada di posisi paling atas (bisa diklik langsung),
        // biarkan klik TUNGGAL yang membuka tools — jangan ganggu
        // seleksi kata bawaan browser.
        if (stack[0] === img) return;

        // Tanda tangan punya sistem drag sendiri, jangan ganggu
        if (img.closest('.doc-signature')) return;

        // Harus ada editor TinyMCE yang memuat gambar ini
        // (mencegah ikut menangkap gambar di modal/luar editor)
        const editor = findEditorContaining(img);
        if (!editor) return;

        e.preventDefault();
        try { window.getSelection()?.removeAllRanges(); } catch (err) { /* noop */ }

        showImageTools(editor, img);
    });
}

const registerImageLayoutTools = (editor) => {
    // Daftarkan editor agar listener global (klik dua kali untuk gambar
    // yang tertutup teks) bisa menemukan editor pemiliknya
    if (!registeredImageToolEditors.includes(editor)) {
        registeredImageToolEditors.push(editor);
    }

    // Klik sekali pada gambar langsung membuka mode edit
    // (perilaku seperti awal)
    editor.on('click', (e) => {
        if (e.target.nodeName === 'IMG') {
            showImageTools(editor, e.target);
        }
    });

    editor.on('keydown', (e) => {
        if ((e.key === 'Backspace' || e.key === 'Delete') && activeImage) {
            setTimeout(() => {
                if (!activeImage || !activeImage.isConnected) {
                    removeImageTools();
                }
            }, 50);
        }
    });

    editor.on('remove', () => {
        registeredImageToolEditors = registeredImageToolEditors.filter((ed) => ed !== editor);
        if (activeEditor === editor) removeImageTools();
    });
};

// =========================================
// TOOLTIP BUBBLE UNTUK TOOLBAR
// =========================================

const registerToolbarTooltips = () => {
    if (window.__toolbarTooltipsBound) return;
    window.__toolbarTooltipsBound = true;

    const container = document.getElementById('body-toolbar-container');
    if (!container) return;

    let hoverTimer = null;
    let tooltipEl = null;

    const hideTooltip = () => {
        clearTimeout(hoverTimer);
        tooltipEl?.remove();
        tooltipEl = null;
    };

    container.addEventListener('mouseover', (e) => {
        const btn = e.target.closest('.tox-tbtn, .tox-split-button, [aria-label]');
        if (!btn) return;

        const label = btn.getAttribute('aria-label') || btn.getAttribute('title');
        if (!label) return;

        clearTimeout(hoverTimer);
        hoverTimer = setTimeout(() => {
            hideTooltip();
            const rect = btn.getBoundingClientRect();
            tooltipEl = document.createElement('div');
            tooltipEl.textContent = label;
            tooltipEl.style.cssText =
                'position:fixed;z-index:999999;background:#1B2A4A;color:#fff;padding:5px 9px;' +
                'border-radius:6px;font-size:11px;white-space:nowrap;pointer-events:none;box-shadow:0 2px 6px rgba(0,0,0,.2);';
            document.body.appendChild(tooltipEl);
            tooltipEl.style.left = (rect.left + rect.width / 2 - tooltipEl.offsetWidth / 2) + 'px';
            tooltipEl.style.top = (rect.bottom + 6) + 'px';
        }, 900);
    });

    container.addEventListener('mouseout', (e) => {
        const btn = e.target.closest('.tox-tbtn, .tox-split-button, [aria-label]');
        if (btn) hideTooltip();
    });
};

// =========================================
// GUARD GLOBAL CTRL+A UNTUK DOKUMEN (lapis kedua, capture phase)
// Memastikan select-all bawaan browser/TinyMCE tidak pernah
// memilih elemen kertas (.doc-sheet) — hanya isi kontennya.
// =========================================

if (!window.__selectAllGuardBound) {
    window.__selectAllGuardBound = true;

    document.addEventListener('keydown', (e) => {
        if (!(e.ctrlKey || e.metaKey) || e.altKey || e.shiftKey) return;
        if ((e.key || '').toLowerCase() !== 'a') return;

        const editorEl = document.getElementById('document-editor');
        if (!editorEl || !editorEl.contains(e.target)) return;

        const editor = window.tinymce && window.tinymce.get('document-editor');
        if (!editor) return;

        try {
            const node = editor.selection.getNode();
            const container =
                editor.dom.getParent(node, '.doc-sheet-body, .doc-sheet-header, .doc-sheet-footer') ||
                editor.dom.getParent(node, '.doc-sheet')?.querySelector('.doc-sheet-body') ||
                editor.getBody().querySelector('.doc-sheet-body');

            if (!container) return;

            e.preventDefault();
            e.stopPropagation();

            const rng = editor.dom.createRng();
            rng.selectNodeContents(container);
            editor.selection.setRng(rng);
        } catch (err) {
            // noop
        }
    }, true);
}