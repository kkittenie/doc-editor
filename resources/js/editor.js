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

// Skin & content css (self-hosted, gak pakai CDN)
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
        // The printable A4 page has a 634px-wide content column (794px page - 80px margins).
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

window.initDocumentEditor = function (selector, initialContent = '', allowLogoUpload = true) {
    tinymce.init({
        selector: selector,
        license_key: 'gpl',
        height: 400,
        menubar: false,
        branding: false,
        statusbar: false,
        plugins: 'lists link table image',
        toolbar: `undo redo | bold italic underline | alignleft aligncenter alignright alignjustify | bullist numlist | table link${allowLogoUpload ? ' uploadlogo' : ''} | removeformat`,
        content_style: 'body { position: relative; font-family: Georgia, serif; font-size: 13px; } body > p, body > h1, body > h2, body > h3, body > h4, body > h5, body > h6, body > ul, body > ol, body > table, body > blockquote, body > div { position: relative; z-index: 2; } body.has-document-logo > p, body.has-document-logo > h1, body.has-document-logo > h2, body.has-document-logo > h3, body.has-document-logo > h4, body.has-document-logo > h5, body.has-document-logo > h6 { margin-left: 190px; } .document-logo { position: absolute; z-index: 1; width: auto; max-width: 150px; height: auto; max-height: 70px; cursor: grab; }',
        skin: false,
        content_css: false,
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
                editor.save(); // sync ke textarea asli, biar kebaca Alpine
            });
        }
    });
};
