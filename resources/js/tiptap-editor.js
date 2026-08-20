import { Editor } from '@tiptap/core'
import StarterKit from '@tiptap/starter-kit'

import TextAlign from '@tiptap/extension-text-align'
import Underline from '@tiptap/extension-underline'
// import TextStyle from '@tiptap/extension-text-style'
// import Color from '@tiptap/extension-color'
// import Highlight from '@tiptap/extension-highlight'
import Link from '@tiptap/extension-link'
import Image from '@tiptap/extension-image'

import { Table } from '@tiptap/extension-table'
import { TableRow } from '@tiptap/extension-table-row'
import { TableCell } from '@tiptap/extension-table-cell'
import { TableHeader } from '@tiptap/extension-table-header'

import Superscript from '@tiptap/extension-superscript'
import Subscript from '@tiptap/extension-subscript'


window.tiptapEditors = {}


/*
|--------------------------------------------------------------------------
| CREATE EDITOR
|--------------------------------------------------------------------------
*/

window.createTiptapEditor = function (
    id,
    element,
    content = '<p></p>',
    onUpdate = null
) {
    if (!element) {
        console.error('[Tiptap] Element tidak ditemukan:', id)
        return null
    }

    if (window.tiptapEditors[id]) {
        window.tiptapEditors[id].destroy()
    }

    const editor = new Editor({
        element: element,

        content: content || '<p></p>',

        extensions: [
            StarterKit,

            // Underline,

            TextAlign.configure({
                types: [
                    'heading',
                    'paragraph'
                ]
            })
        ],

        onUpdate({ editor }) {
            if (typeof onUpdate === 'function') {
                onUpdate(editor.getHTML())
            }

            // pastikan tombol toolbar (bold/italic/dll)
            // tetap sinkron setelah setiap perubahan
            window.tiptapToolbarRefresh?.()
        },

        onSelectionUpdate() {
            // update highlight tombol toolbar setiap kali
            // kursor / seleksi berpindah posisi
            window.tiptapToolbarRefresh?.()
        },

        onFocus() {
            // saat editor ini fokus, toolbar harus
            // langsung merefleksikan state-nya
            window.tiptapToolbarRefresh?.()
        }
    })

    window.tiptapEditors[id] = editor

    console.log(
        '[Tiptap] Editor berhasil dibuat:',
        id
    )

    return editor
}

window.tiptapCommand = function (
    id,
    command,
    value = null
) {

    const editor =
        window.tiptapEditors[id]


    if (!editor) {

        console.error(
            '[Tiptap] Editor tidak ditemukan:',
            id
        )

        return
    }


    const chain =
        editor
            .chain()
            .focus()


    switch (command) {

        /*
        |--------------------------------------------------------------------------
        | HISTORY
        |--------------------------------------------------------------------------
        */

        case 'undo':

            chain
                .undo()
                .run()

            break


        case 'redo':

            chain
                .redo()
                .run()

            break


        /*
        |--------------------------------------------------------------------------
        | TEXT FORMAT
        |--------------------------------------------------------------------------
        */

        case 'bold':

            chain
                .toggleBold()
                .run()

            break


        case 'italic':

            chain
                .toggleItalic()
                .run()

            break


        case 'underline':

            chain
                .toggleUnderline()
                .run()

            break


        case 'strike':

            chain
                .toggleStrike()
                .run()

            break


        /*
        |--------------------------------------------------------------------------
        | TEXT ALIGN
        |--------------------------------------------------------------------------
        */

        case 'alignLeft':

            chain
                .setTextAlign('left')
                .run()

            break


        case 'alignCenter':

            chain
                .setTextAlign('center')
                .run()

            break


        case 'alignRight':

            chain
                .setTextAlign('right')
                .run()

            break


        case 'alignJustify':

            chain
                .setTextAlign('justify')
                .run()

            break


        /*
        |--------------------------------------------------------------------------
        | PARAGRAPH
        |--------------------------------------------------------------------------
        */

        case 'paragraph':

            chain
                .setParagraph()
                .run()

            break


        /*
        |--------------------------------------------------------------------------
        | HEADING
        |--------------------------------------------------------------------------
        */

        case 'heading':

            chain
                .toggleHeading({
                    level: Number(value)
                })
                .run()

            break


        /*
        |--------------------------------------------------------------------------
        | LIST
        |--------------------------------------------------------------------------
        */

        case 'bulletList':

            chain
                .toggleBulletList()
                .run()

            break


        case 'orderedList':

            chain
                .toggleOrderedList()
                .run()

            break


        /*
        |--------------------------------------------------------------------------
        | BLOCKQUOTE
        |--------------------------------------------------------------------------
        */

        case 'blockquote':

            chain
                .toggleBlockquote()
                .run()

            break


        /*
        |--------------------------------------------------------------------------
        | HORIZONTAL RULE
        |--------------------------------------------------------------------------
        */

        case 'horizontalRule':

            chain
                .setHorizontalRule()
                .run()

            break


        /*
        |--------------------------------------------------------------------------
        | SUPERSCRIPT
        |--------------------------------------------------------------------------
        */

        case 'superscript':

            chain
                .toggleSuperscript()
                .run()

            break


        /*
        |--------------------------------------------------------------------------
        | SUBSCRIPT
        |--------------------------------------------------------------------------
        */

        case 'subscript':

            chain
                .toggleSubscript()
                .run()

            break


        /*
        |--------------------------------------------------------------------------
        | CLEAR
        |--------------------------------------------------------------------------
        */

        case 'clear':

            chain
                .clearNodes()
                .unsetAllMarks()
                .run()

            break


        default:

            console.warn(
                '[Tiptap] Command tidak dikenal:',
                command
            )
    }
}


/*
|--------------------------------------------------------------------------
| GET HTML
|--------------------------------------------------------------------------
*/

window.getTiptapHTML = function (id) {

    const editor =
        window.tiptapEditors[id]


    if (!editor) {

        console.error(
            '[Tiptap] Editor tidak ditemukan:',
            id
        )

        return '<p></p>'
    }


    return editor.getHTML()
}


/*
|--------------------------------------------------------------------------
| SET HTML
|--------------------------------------------------------------------------
*/

window.setTiptapHTML = function (
    id,
    html
) {

    const editor =
        window.tiptapEditors[id]


    if (!editor) {

        console.error(
            '[Tiptap] Editor tidak ditemukan:',
            id
        )

        return
    }


    editor.commands.setContent(
        html || '<p></p>',
        {
            emitUpdate: false
        }
    )
}


/*
|--------------------------------------------------------------------------
| DESTROY
|--------------------------------------------------------------------------
*/

window.destroyTiptapEditor = function (id) {

    const editor =
        window.tiptapEditors[id]


    if (!editor) {
        return
    }


    try {

        editor.destroy()

    } catch (error) {

        console.warn(
            '[Tiptap] Gagal destroy:',
            error
        )
    }


    delete window.tiptapEditors[id]
}