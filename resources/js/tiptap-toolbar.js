window.createTiptapToolbar = function (
    container,
    getActiveEditorId
) {

    if (!container) {
        console.error('[Tiptap Toolbar] Container tidak ditemukan');
        return;
    }

    container.innerHTML = '';

    // =========================================
    // BUTTON
    // =========================================

    const button = (
        html,
        command,
        title,
        value = null
    ) => {

        const btn = document.createElement('button');

        btn.type = 'button';
        btn.className = 'tiptap-toolbar-button';
        btn.innerHTML = html;
        btn.title = title;

        btn.addEventListener('mousedown', (event) => {

            event.preventDefault();

            const editorId =
                typeof getActiveEditorId === 'function'
                    ? getActiveEditorId()
                    : null;

            if (!editorId) {
                console.warn(
                    '[Tiptap Toolbar] Tidak ada editor aktif'
                );
                return;
            }

            window.tiptapCommand?.(
                editorId,
                command,
                value
            );
        });

        return btn;
    };


    // =========================================
    // DIVIDER
    // =========================================

    const divider = () => {

        const el = document.createElement('span');

        el.className = 'tiptap-toolbar-divider';

        return el;
    };


    // =========================================
    // GROUP
    // =========================================

    const group = (...elements) => {

        const wrapper = document.createElement('div');

        wrapper.className = 'tiptap-toolbar-group';

        elements.forEach(element => {
            wrapper.appendChild(element);
        });

        return wrapper;
    };


    // =========================================
    // UNDO / REDO
    // =========================================

    container.appendChild(
        group(
            button('↶', 'undo', 'Undo'),
            button('↷', 'redo', 'Redo')
        )
    );


    // =========================================
    // TEXT FORMAT
    // =========================================

    container.appendChild(
        group(
            button('<b>B</b>', 'bold', 'Bold'),
            button('<i>I</i>', 'italic', 'Italic'),
            button('<u>U</u>', 'underline', 'Underline'),
            button('<s>S</s>', 'strike', 'Strikethrough')
        )
    );


    // =========================================
    // ALIGNMENT
    // =========================================

    container.appendChild(
        group(
            button('≡', 'alignLeft', 'Align Left'),
            button('≡', 'alignCenter', 'Align Center'),
            button('≡', 'alignRight', 'Align Right'),
            button('≡', 'alignJustify', 'Justify')
        )
    );


    // =========================================
    // FORMAT
    // =========================================

    const format = document.createElement('select');

    format.className = 'tiptap-toolbar-select';
    format.title = 'Format';

    format.innerHTML = `
        <option value="paragraph">Normal</option>
        <option value="1">Heading 1</option>
        <option value="2">Heading 2</option>
        <option value="3">Heading 3</option>
    `;

    format.addEventListener('change', () => {

        const editorId =
            typeof getActiveEditorId === 'function'
                ? getActiveEditorId()
                : null;

        if (!editorId) return;

        if (format.value === 'paragraph') {

            window.tiptapCommand(
                editorId,
                'paragraph'
            );

        } else {

            window.tiptapCommand(
                editorId,
                'heading',
                format.value
            );
        }
    });

    container.appendChild(
        group(format)
    );


    // =========================================
    // LIST
    // =========================================

    container.appendChild(
        group(
            button('•', 'bulletList', 'Bullet List'),
            button('1.', 'orderedList', 'Numbered List')
        )
    );


    // =========================================
    // INSERT / SPECIAL
    // =========================================

    container.appendChild(
        group(
            button('❝', 'blockquote', 'Blockquote'),
            button('―', 'horizontalRule', 'Horizontal Rule'),
            button('x²', 'superscript', 'Superscript'),
            button('x₂', 'subscript', 'Subscript')
        )
    );


    // =========================================
    // CLEAR FORMAT
    // =========================================

    container.appendChild(
        group(
            button('Tx', 'clear', 'Clear Formatting')
        )
    );


    console.log(
        '[Tiptap Toolbar] Toolbar berhasil dibuat'
    );
};  