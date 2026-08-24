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
                min-height: 40px;
                padding-bottom: 8px;
            }

            .doc-sheet-body {
                flex: 1;
                min-height: 0;
            }

            .doc-sheet-footer {
                position: relative;
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
let panelEl = null;
let handleEls = [];
let isResizingImage = false;
let resizeStartW = 0, resizeStartX = 0, resizeCorner = null;
let watchTimer = null;

const removeImageTools = () => {
    clearInterval(watchTimer);
    watchTimer = null;
    bubbleEl?.remove();
    panelEl?.remove();
    handleEls.forEach((h) => h.remove());
    bubbleEl = null;
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

const applyImageLayout = (img, layout) => {
    img.style.float = '';
    img.style.display = '';
    img.style.margin = '';

    if (layout === 'square') {
        img.style.float = 'left';
        img.style.margin = '4px 14px 8px 0';
    } else if (layout === 'topbottom') {
        img.style.display = 'block';
        img.style.margin = '12px auto';
    }
    // 'inline' -> biarin default, gak perlu style tambahan

    activeEditor.save();
    activeEditor.fire('change');

    requestAnimationFrame(positionImageTools);
};

const buildPanel = () => {
    panelEl?.remove();

    const options = [
        { key: 'inline', label: 'Sejajar dengan Teks' },
        { key: 'square', label: 'Persegi (teks di samping)' },
        { key: 'topbottom', label: 'Atas dan Bawah' },
    ];

    panelEl = document.createElement('div');
    panelEl.style.cssText =
        'position:fixed;z-index:999999;width:210px;background:#fff;border:1px solid #d6d3cc;' +
        'border-radius:10px;box-shadow:0 8px 24px rgba(0,0,0,.18);padding:8px;font-family:Arial,sans-serif;';

    panelEl.innerHTML =
        '<div style="font-size:12px;font-weight:700;color:#1B2A4A;margin-bottom:6px;padding:0 4px;">Layout Options</div>' +
        options.map(o =>
            `<button type="button" data-layout="${o.key}" style="display:block;width:100%;text-align:left;` +
            `padding:7px 8px;border-radius:6px;background:transparent;border:none;cursor:pointer;font-size:12.5px;color:#1B2A4A;">${o.label}</button>`
        ).join('');

    document.body.appendChild(panelEl);

    const bubbleRect = bubbleEl.getBoundingClientRect();
    panelEl.style.left = Math.max(8, bubbleRect.left - 190) + 'px';
    panelEl.style.top = (bubbleRect.bottom + 6) + 'px';

    panelEl.querySelectorAll('button[data-layout]').forEach((btn) => {
        btn.addEventListener('mouseenter', () => btn.style.background = '#f5f2eb');
        btn.addEventListener('mouseleave', () => btn.style.background = 'transparent');
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
    bubbleEl.title = 'Layout Options';
    bubbleEl.style.cssText =
        'position:fixed;z-index:999999;width:26px;height:26px;border-radius:6px;background:#1B2A4A;' +
        'color:#fff;display:flex;align-items:center;justify-content:center;cursor:pointer;' +
        'box-shadow:0 2px 6px rgba(0,0,0,.25);font-size:13px;';
    document.body.appendChild(bubbleEl);

    bubbleEl.addEventListener('click', (e) => {
        e.stopPropagation();
        panelEl ? (panelEl.remove(), panelEl = null) : buildPanel();
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
        if (e.target === panelEl || panelEl?.contains(e.target)) return;
        if (handleEls.includes(e.target)) return;
        if (e.target.nodeName === 'IMG') return;
        removeImageTools();
    });
}

const registerImageLayoutTools = (editor) => {
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