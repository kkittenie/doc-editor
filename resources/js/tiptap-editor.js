import { Editor } from '@tiptap/core';
import StarterKit from '@tiptap/starter-kit';

import { TextAlign } from '@tiptap/extension-text-align';
import { Underline } from '@tiptap/extension-underline';
import { TextStyle } from '@tiptap/extension-text-style';
import { Color } from '@tiptap/extension-color';
import { Highlight } from '@tiptap/extension-highlight';
import { Link } from '@tiptap/extension-link';
import { Image } from '@tiptap/extension-image';

import { Table } from '@tiptap/extension-table';
import { TableRow } from '@tiptap/extension-table-row';
import { TableCell } from '@tiptap/extension-table-cell';
import { TableHeader } from '@tiptap/extension-table-header';

import { Superscript } from '@tiptap/extension-superscript';
import { Subscript } from '@tiptap/extension-subscript';


/*
|--------------------------------------------------------------------------
| Tiptap Editor Registry
|--------------------------------------------------------------------------
|
| Editor Tiptap disimpan di sini, BUKAN di Alpine reactive state.
|
*/

window.tiptapEditors = {};


/*
|--------------------------------------------------------------------------
| Create Editor
|--------------------------------------------------------------------------
*/

window.createTiptapEditor = function (
    id,
    element,
    initialContent = '',
    onUpdate = null
) {

    if (!element) {
        console.error('Element Tiptap tidak ditemukan:', id);
        return null;
    }


    /*
    |--------------------------------------------------------------------------
    | Kalau editor dengan ID yang sama sudah ada
    |--------------------------------------------------------------------------
    */

    if (window.tiptapEditors[id]) {

        try {
            window.tiptapEditors[id].destroy();
        } catch (error) {
            console.warn(
                'Gagal destroy editor lama:',
                error
            );
        }

        delete window.tiptapEditors[id];
    }


    /*
    |--------------------------------------------------------------------------
    | Buat editor baru
    |--------------------------------------------------------------------------
    */

    const editor = new Editor({

        element,

        extensions: [

            StarterKit.configure({
                link: false,
                underline: false,
            }),

            TextAlign.configure({
                types: [
                    'heading',
                    'paragraph',
                ],
            }),

            Underline,

            TextStyle,

            Color,

            Highlight.configure({
                multicolor: true,
            }),

            Link.configure({
                openOnClick: false,
                autolink: true,
                linkOnPaste: true,
            }),

            Image.configure({
                inline: false,
                allowBase64: true,
            }),

            Table.configure({
                resizable: true,
            }),

            TableRow,
            TableHeader,
            TableCell,

            Superscript,
            Subscript,
        ],

        content: initialContent || '<p></p>',

        onUpdate: ({ editor }) => {

            const html = editor.getHTML();

            if (typeof onUpdate === 'function') {
                onUpdate(html);
            }

        },

    });


    /*
    |--------------------------------------------------------------------------
    | Simpan editor asli
    |--------------------------------------------------------------------------
    */

    window.tiptapEditors[id] = editor;


    return editor;
};


/*
|--------------------------------------------------------------------------
| Get Editor
|--------------------------------------------------------------------------
*/

window.getTiptapEditor = function (id) {

    return window.tiptapEditors[id] || null;

};


/*
|--------------------------------------------------------------------------
| Command
|--------------------------------------------------------------------------
*/

window.tiptapCommand = function (
    id,
    command,
    value = null
) {

    const editor =
        typeof id === 'string'
            ? window.getTiptapEditor(id)
            : id;

    if (!editor) {

        console.warn(
            'Tiptap editor tidak ditemukan:',
            id
        );

        return;
    }


    const chain =
        editor
            .chain()
            .focus();


    switch (command) {

        // HISTORY

        case 'undo':
            chain.undo().run();
            break;

        case 'redo':
            chain.redo().run();
            break;


        // TEXT

        case 'bold':

            chain
                .toggleBold()
                .run();

            break;


        case 'italic':

            chain
                .toggleItalic()
                .run();

            break;


        case 'underline':

            chain
                .toggleUnderline()
                .run();

            break;


        case 'strike':

            chain
                .toggleStrike()
                .run();

            break;


        // HEADING

        case 'paragraph':

            chain
                .setParagraph()
                .run();

            break;


        case 'heading':

            chain
                .toggleHeading({
                    level: Number(value),
                })
                .run();

            break;


        // ALIGNMENT

        case 'alignLeft':

            chain
                .setTextAlign('left')
                .run();

            break;

        case 'alignCenter':

            chain
                .setTextAlign('center')
                .run();

            break;

        case 'alignRight':

            chain
                .setTextAlign('right')
                .run();

            break;

        case 'alignJustify':

            chain
                .setTextAlign('justify')
                .run();

            break;


        // LIST

        case 'bulletList':

            chain
                .toggleBulletList()
                .run();

            break;

        case 'orderedList':

            chain
                .toggleOrderedList()
                .run();

            break;

        // TABLE

        case 'insertTable':

            chain
                .insertTable({
                    rows: 3,
                    cols: 3,
                    withHeaderRow: true,
                })
                .run();

            break;


        case 'addRowAfter':

            chain
                .addRowAfter()
                .run();

            break;


        case 'addColumnAfter':

            chain
                .addColumnAfter()
                .run();

            break;


        case 'deleteRow':

            chain
                .deleteRow()
                .run();

            break;


        case 'deleteColumn':

            chain
                .deleteColumn()
                .run();

            break;


        case 'deleteTable':

            chain
                .deleteTable()
                .run();

            break;


        // BLOCK

        case 'blockquote':

            chain
                .toggleBlockquote()
                .run();

            break;

        case 'horizontalRule':

            chain
                .setHorizontalRule()
                .run();

            break;

                // TABLE

        case 'insertTable':

            chain
                .insertTable({
                    rows: Number(value?.rows ?? 3),
                    cols: Number(value?.cols ?? 3),
                    withHeaderRow: true,
                })
                .run();

            break;

        case 'addRowBefore':

            chain
                .addRowBefore()
                .run();

            break;

        case 'addRowAfter':

            chain
                .addRowAfter()
                .run();

            break;

        case 'deleteRow':

            chain
                .deleteRow()
                .run();

            break;

        case 'addColumnBefore':

            chain
                .addColumnBefore()
                .run();

            break;

        case 'addColumnAfter':

            chain
                .addColumnAfter()
                .run();

            break;

        case 'deleteColumn':

            chain
                .deleteColumn()
                .run();

            break;

        case 'deleteTable':

            chain
                .deleteTable()
                .run();

            break;

                // LINK

        case 'setLink':

            if (!value) {
                break;
            }

            chain
                .setLink({
                    href: value,
                    target: '_blank',
                })
                .run();

            break;

        case 'unsetLink':

            chain
                .unsetLink()
                .run();

            break;


        // SCRIPT

        case 'superscript':

            chain
                .toggleSuperscript()
                .run();

            break;

        case 'subscript':

            chain
                .toggleSubscript()
                .run();

            break;


        // CLEAR

        case 'clear':

            chain
                .clearNodes()
                .unsetAllMarks()
                .run();

            break;

    }

};


/*
|--------------------------------------------------------------------------
| Color
|--------------------------------------------------------------------------
*/

window.tiptapSetColor = function (
    id,
    color
) {

    const editor =
        window.getTiptapEditor(id);

    if (!editor) return;

    editor
        .chain()
        .focus()
        .setColor(color)
        .run();

};


/*
|--------------------------------------------------------------------------
| Highlight
|--------------------------------------------------------------------------
*/

window.tiptapSetHighlight = function (
    id,
    color
) {

    const editor =
        window.getTiptapEditor(id);

    if (!editor) return;

    editor
        .chain()
        .focus()
        .setHighlight({
            color,
        })
        .run();

};

/*
|--------------------------------------------------------------------------
| Image
|--------------------------------------------------------------------------
*/

window.tiptapInsertImage = function (
    id,
    src,
    alt = ''
) {

    const editor =
        window.getTiptapEditor(id);

    if (!editor || !src) {
        return;
    }

    editor
        .chain()
        .focus()
        .setImage({
            src,
            alt,
        })
        .run();

};

/*
|--------------------------------------------------------------------------
| Build Toolbar
|--------------------------------------------------------------------------
*/

window.createTiptapToolbar = function (
    container,
    getEditorId
) {

    if (!container) {
        return;
    }

    container.innerHTML = `
            <div class="flex min-h-[50px] items-center gap-1.5 overflow-x-auto px-2 py-2">

            <button type="button" data-command="undo" title="Undo" class="tiptap-toolbar-button">
                ↶
            </button>

            <button type="button" data-command="redo" title="Redo" class="tiptap-toolbar-button">
                ↷
            </button>

            <span class="tiptap-toolbar-divider"></span>

            <select data-command="heading" title="Gaya teks" class="tiptap-toolbar-select">
                <option value="paragraph">Normal</option>
                <option value="1">Heading 1</option>
                <option value="2">Heading 2</option>
                <option value="3">Heading 3</option>
            </select>

            <span class="tiptap-toolbar-divider"></span>

            <button type="button" data-command="bold" title="Bold" class="tiptap-toolbar-button">
                <b>B</b>
            </button>

            <button type="button" data-command="italic" title="Italic" class="tiptap-toolbar-button">
                <i>I</i>
            </button>

            <button type="button" data-command="underline" title="Underline" class="tiptap-toolbar-button">
                <u>U</u>
            </button>

            <button type="button" data-command="strike" title="Coret" class="tiptap-toolbar-button">
                <s>S</s>
            </button>

            <span class="tiptap-toolbar-divider"></span>

            <button type="button" data-command="alignLeft" title="Rata kiri" class="tiptap-toolbar-button">
                ☰
            </button>

            <button type="button" data-command="alignCenter" title="Rata tengah" class="tiptap-toolbar-button">
                ≡
            </button>

            <button type="button" data-command="alignRight" title="Rata kanan" class="tiptap-toolbar-button">
                ☷
            </button>

            <button type="button" data-command="alignJustify" title="Rata kiri-kanan" class="tiptap-toolbar-button">
                ☷
            </button>

            <span class="tiptap-toolbar-divider"></span>

            <button type="button" data-command="bulletList" title="Bullet" class="tiptap-toolbar-button">
                •☰
            </button>

            <button type="button" data-command="orderedList" title="Numbering" class="tiptap-toolbar-button">
                1☰
            </button>

            <span class="tiptap-toolbar-divider"></span>

            <button type="button" data-command="superscript" title="Superscript" class="tiptap-toolbar-button">
                X²
            </button>

            <button type="button" data-command="subscript" title="Subscript" class="tiptap-toolbar-button">
                X₂
            </button>

            <button type="button" data-command="blockquote" title="Quote" class="tiptap-toolbar-button">
                ❝
            </button>

            <button type="button" data-command="horizontalRule" title="Garis" class="tiptap-toolbar-button">
                ―
            </button>

            <button type="button" class="toolbar-button" title="Tambah Tabel" @mousedown.prevent @click="runBodyCommand('insertTable')">
                ▦
            </button>

            <button
                type="button"
                class="toolbar-button"
                title="Tambah Baris"
                @mousedown.prevent
                @click="runBodyCommand('addRowAfter')"
            >
                +↕
            </button>

            <button
                type="button"
                class="toolbar-button"
                title="Tambah Kolom"
                @mousedown.prevent
                @click="runBodyCommand('addColumnAfter')"
            >
                +↔
            </button>

            <button
                type="button"
                class="toolbar-button"
                title="Hapus Baris"
                @mousedown.prevent
                @click="runBodyCommand('deleteRow')"
            >
                −↕
            </button>

            <button
                type="button"
                class="toolbar-button"
                title="Hapus Kolom"
                @mousedown.prevent
                @click="runBodyCommand('deleteColumn')"
            >
                −↔
            </button>

            <button
                type="button"
                class="toolbar-button"
                title="Hapus Tabel"
                @mousedown.prevent
                @click="runBodyCommand('deleteTable')"
            >
                🗑
            </button>

            <span class="tiptap-toolbar-divider"></span>

            <button type="button" data-command="clear" title="Hapus format" class="tiptap-toolbar-button">
                Tx
            </button>

        </div>
    `;

    container
        .querySelectorAll('[data-command]')
        .forEach((button) => {

            const command =
                button.dataset.command;

            if (button.tagName === 'SELECT') {

                button.addEventListener(
                    'change',
                    () => {

                        const editorId =
                            getEditorId();

                        if (!editorId) {
                            return;
                        }

                        const value =
                            button.value;

                        if (value === 'paragraph') {

                            window.tiptapCommand(
                                editorId,
                                'paragraph'
                            );

                        } else {

                            window.tiptapCommand(
                                editorId,
                                'heading',
                                value
                            );
                        }

                    }
                );

                return;
            }

            button.addEventListener(
                'mousedown',
                (event) => {

                    /*
                    |--------------------------------------------------------------
                    | Jangan sampai klik toolbar menghilangkan selection Tiptap
                    |--------------------------------------------------------------
                    */

                    event.preventDefault();

                }
            );

            button.addEventListener(
                'click',
                () => {

                    const editorId =
                        getEditorId();

                    if (!editorId) {
                        return;
                    }

                    window.tiptapCommand(
                        editorId,
                        command
                    );

                }
            );

        });

};