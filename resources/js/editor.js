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

window.initDocumentEditor = function (selector, initialContent = '') {
    tinymce.init({
        selector: selector,
        license_key: 'gpl',
        height: 400,
        menubar: false,
        plugins: 'lists link table image',
        toolbar: 'undo redo | bold italic underline | alignleft aligncenter alignright alignjustify | bullist numlist | table link image | removeformat',
        content_style: 'body { font-family: Georgia, serif; font-size: 13px; }',
        skin: false,
        content_css: false,
        setup: function (editor) {
            editor.on('init', function () {
                if (initialContent) editor.setContent(initialContent);
            });
            editor.on('change keyup', function () {
                editor.save(); // sync ke textarea asli, biar kebaca Alpine
            });
        }
    });
};