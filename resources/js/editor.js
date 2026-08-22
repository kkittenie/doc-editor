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

        object_resizing: 'img,table',

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

            .doc-sheet-divider {
                border-top: 2px solid #111827;
                margin: 8px 0;
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