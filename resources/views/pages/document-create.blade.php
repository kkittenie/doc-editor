 blade
@extends('layouts.app')

@section('content')

<div
    x-data="{
        loadingTemplate: null,
        isUploadingLogo: false,

        headerHtml: '<p></p>',
        footerHtml: '<p></p>',
        bodyHtml: '<p></p>',

        headerEditor: null,
        footerEditor: null,

        init() {
            this.$nextTick(() => {
                this.initTiptapEditors();
            });
        },

        initTiptapEditors() {

            // ==========================================
            // HEADER
            // ==========================================

            if (
                this.$refs.headerEditor &&
                typeof window.createTiptapEditor === 'function'
            ) {

                this.headerEditor = window.createTiptapEditor(
                    'header',
                    this.$refs.headerEditor,
                    '<p></p>',
                    (html) => {
                        this.headerHtml = html;
                    }
                );
            }


            // ==========================================
            // FOOTER
            // ==========================================

            if (
                this.$refs.footerEditor &&
                typeof window.createTiptapEditor === 'function'
            ) {

                this.footerEditor = window.createTiptapEditor(
                    'footer',
                    this.$refs.footerEditor,
                    '<p></p>',
                    (html) => {
                        this.footerHtml = html;
                    }
                );
            }

        },


        // ==========================================
        // SET HEADER
        // ==========================================

        setHeaderContent(content) {

            const html = content || '<p></p>';

            this.headerHtml = html;

            if (typeof window.setTiptapHTML === 'function') {
                window.setTiptapHTML('header', html);
            }

        },


        // ==========================================
        // SET FOOTER
        // ==========================================

        setFooterContent(content) {

            const html = content || '<p></p>';

            this.footerHtml = html;

            if (typeof window.setTiptapHTML === 'function') {
                window.setTiptapHTML('footer', html);
            }

        },


        // ==========================================
        // LOAD TEMPLATE
        // ==========================================

        async loadTemplate(key) {

            this.loadingTemplate = key;

            try {

                const res = await window.axios.get(
                    '/documents/template/' + key
                );

                const t = res.data;

                // TITLE
                if (t.title) {
                    const titleInput =
                        document.getElementById('header-judul');

                    if (titleInput) {
                        titleInput.value = t.title;
                    }
                }


                // NOMOR SURAT
                if (t.header_data?.nomorSurat) {

                    const nomorInput =
                        document.getElementById('header-nomor');

                    if (nomorInput) {
                        nomorInput.value =
                            t.header_data.nomorSurat;
                    }
                }


                // HEADER
                if (t.header_data?.content) {
                    this.setHeaderContent(
                        t.header_data.content
                    );
                }


                // FOOTER
                if (t.footer_data?.content) {
                    this.setFooterContent(
                        t.footer_data.content
                    );
                }


                // BODY
                if (t.body_content?.content) {
                    this.bodyHtml =
                        t.body_content.content;
                }


                Swal.fire({
                    icon: 'success',
                    title: 'Template dimuat',
                    text: 'Template berhasil dimasukkan.',
                    confirmButtonColor: '#1B2A4A',
                    timer: 1200,
                    showConfirmButton: false
                });

            } catch (e) {

                console.error(e);

                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: 'Gagal memuat template.',
                    confirmButtonColor: '#1B2A4A'
                });

            } finally {

                this.loadingTemplate = null;

            }

        },


        // ==========================================
        // UPLOAD LOGO
        // ==========================================

        uploadLogo(event) {

            const file =
                event.target.files?.[0];

            if (!file) {
                return;
            }

            this.isUploadingLogo = true;

            const reader =
                new FileReader();

            reader.onload = async (e) => {

                try {

                    const res =
                        await window.axios.post(
                            '/documents/logo',
                            {
                                image: e.target.result
                            }
                        );


                    // Fokus ke header
                    if (this.headerEditor) {

                        this.headerEditor
                            .chain()
                            .focus()
                            .setImage({
                                src: res.data.url
                            })
                            .run();

                    }

                } catch (err) {

                    console.error(err);

                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: 'Gagal mengunggah logo.',
                        confirmButtonColor: '#1B2A4A'
                    });

                } finally {

                    this.isUploadingLogo = false;

                    event.target.value = '';

                }

            };

            reader.readAsDataURL(file);

        },


        // ==========================================
        // COMMAND TIPTAP
        // ==========================================

        command(editorName, commandName, value = null) {

            if (
                typeof window.tiptapCommand !== 'function'
            ) {
                console.warn(
                    'window.tiptapCommand belum tersedia.'
                );

                return;
            }

            window.tiptapCommand(
                editorName,
                commandName,
                value
            );

        },


        // ==========================================
        // SYNC BEFORE SUBMIT
        // ==========================================

        syncBeforeSubmit() {

            if (typeof window.getTiptapHTML === 'function') {

                this.headerHtml =
                    window.getTiptapHTML('header')
                    || this.headerHtml;

                this.footerHtml =
                    window.getTiptapHTML('footer')
                    || this.footerHtml;

            }


            document.getElementById(
                'header-content-input'
            ).value = this.headerHtml || '<p></p>';


            document.getElementById(
                'footer-content-input'
            ).value = this.footerHtml || '<p></p>';

        }

    }"
    x-init="init()"
    class="max-w-4xl mx-auto py-8"
>


    {{-- ========================================== --}}
    {{-- TITLE --}}
    {{-- ========================================== --}}

    <div class="mb-6">

        <h1 class="text-2xl font-bold text-ink-900 dark:text-parchment-50">
            Buat Dokumen Baru
        </h1>

        <p class="mt-2 text-sm text-slate-warm-600 dark:text-parchment-400">
            Susun kop surat dan footer bebas seperti menulis di Word,
            atau mulai cepat dari template.
        </p>

    </div>


    {{-- ========================================== --}}
    {{-- QUICK TEMPLATE --}}
    {{-- ========================================== --}}

    <div
        class="mb-6 rounded-2xl border border-parchment-300
        bg-parchment-50 p-5
        dark:border-slate-warm-700
        dark:bg-slate-warm-800"
    >

        <h2 class="text-sm font-semibold text-ink-900
            dark:text-parchment-50 mb-3">

            Mulai Cepat dari Template

        </h2>


        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">

            <button
                type="button"
                @click="loadTemplate('perjanjian-kerja-sama')"
                :disabled="loadingTemplate"
                class="rounded-xl border border-parchment-300
                bg-white p-3 text-xs font-medium text-ink-900
                hover:border-bronze-400 transition-colors
                dark:bg-slate-warm-900
                dark:border-slate-warm-700
                dark:text-parchment-100"
            >

                <span
                    x-text="
                        loadingTemplate === 'perjanjian-kerja-sama'
                        ? 'Memuat...'
                        : 'Perjanjian / PKS'
                    "
                ></span>

            </button>


            <button
                type="button"
                @click="loadTemplate('kontrak-kerja')"
                :disabled="loadingTemplate"
                class="rounded-xl border border-parchment-300
                bg-white p-3 text-xs font-medium text-ink-900
                hover:border-bronze-400 transition-colors
                dark:bg-slate-warm-900
                dark:border-slate-warm-700
                dark:text-parchment-100"
            >

                <span
                    x-text="
                        loadingTemplate === 'kontrak-kerja'
                        ? 'Memuat...'
                        : 'Kontrak Kerja'
                    "
                ></span>

            </button>


            <button
                type="button"
                @click="loadTemplate('surat-kuasa')"
                :disabled="loadingTemplate"
                class="rounded-xl border border-parchment-300
                bg-white p-3 text-xs font-medium text-ink-900
                hover:border-bronze-400 transition-colors
                dark:bg-slate-warm-900
                dark:border-slate-warm-700
                dark:text-parchment-100"
            >

                <span
                    x-text="
                        loadingTemplate === 'surat-kuasa'
                        ? 'Memuat...'
                        : 'Surat Kuasa'
                    "
                ></span>

            </button>


            <button
                type="button"
                @click="loadTemplate('surat-pernyataan')"
                :disabled="loadingTemplate"
                class="rounded-xl border border-parchment-300
                bg-white p-3 text-xs font-medium text-ink-900
                hover:border-bronze-400 transition-colors
                dark:bg-slate-warm-900
                dark:border-slate-warm-700
                dark:text-parchment-100"
            >

                <span
                    x-text="
                        loadingTemplate === 'surat-pernyataan'
                        ? 'Memuat...'
                        : 'Surat Pernyataan'
                    "
                ></span>

            </button>

        </div>

    </div>


    {{-- ========================================== --}}
    {{-- FORM --}}
    {{-- ========================================== --}}

    <form
        action="{{ route('documents.store') }}"
        method="POST"
        @submit="syncBeforeSubmit()"
    >

        @csrf


        <input
            type="hidden"
            id="header-content-input"
            name="header_data[content]"
        >


        <input
            type="hidden"
            id="footer-content-input"
            name="footer_data[content]"
        >


        <input
            type="hidden"
            name="body_html"
            :value="bodyHtml"
        >


        {{-- ========================================== --}}
        {{-- JUDUL & NOMOR --}}
        {{-- ========================================== --}}

        <div
            class="rounded-2xl border border-parchment-300
            bg-white p-5 shadow-sm
            dark:border-slate-warm-700
            dark:bg-slate-warm-900 mb-5"
        >

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

                <div>

                    <label class="block text-xs font-medium mb-1.5">
                        Judul Dokumen
                    </label>

                    <input
                        type="text"
                        id="header-judul"
                        name="title"
                        required
                        placeholder="Contoh: Surat Keputusan Direksi"
                        class="w-full rounded-xl
                        border border-parchment-300
                        bg-white px-4 py-2.5 text-sm
                        outline-none focus:border-bronze-500
                        dark:border-slate-warm-700
                        dark:bg-slate-warm-800"
                    >

                </div>


                <div>

                    <label class="block text-xs font-medium mb-1.5">
                        Nomor Dokumen
                    </label>

                    <input
                        type="text"
                        id="header-nomor"
                        name="header_data[nomorSurat]"
                        required
                        placeholder="001/SK-DIR/VIII/2026"
                        class="w-full rounded-xl
                        border border-parchment-300
                        bg-white px-4 py-2.5 text-sm
                        outline-none focus:border-bronze-500
                        dark:border-slate-warm-700
                        dark:bg-slate-warm-800"
                    >

                </div>

            </div>

        </div>


        {{-- ========================================== --}}
        {{-- HEADER --}}
        {{-- ========================================== --}}

        <div class="mb-5">

            <label class="block text-xs font-medium mb-1.5">
                Header / Kop Surat
            </label>


            <div
                class="overflow-hidden rounded-xl border
                border-parchment-300 bg-white
                dark:border-slate-warm-700
                dark:bg-slate-warm-900"
            >


                {{-- TOOLBAR HEADER --}}

                <div
                    class="flex flex-wrap items-center gap-1
                    border-b border-parchment-300
                    bg-parchment-50 p-2
                    dark:border-slate-warm-700
                    dark:bg-slate-warm-800"
                >

                    <button
                        type="button"
                        @click="command('header', 'undo')"
                        class="toolbar-btn"
                        title="Undo"
                    >
                        ↶
                    </button>


                    <button
                        type="button"
                        @click="command('header', 'redo')"
                        class="toolbar-btn"
                        title="Redo"
                    >
                        ↷
                    </button>


                    <span class="toolbar-divider"></span>


                    <select
                        @change="
                            $event.target.value === 'paragraph'
                            ? command('header', 'paragraph')
                            : command(
                                'header',
                                'heading',
                                $event.target.value
                            )
                        "
                        class="toolbar-select"
                    >

                        <option value="paragraph">
                            Normal
                        </option>

                        <option value="1">
                            Heading 1
                        </option>

                        <option value="2">
                            Heading 2
                        </option>

                        <option value="3">
                            Heading 3
                        </option>

                    </select>


                    <button
                        type="button"
                        @click="command('header', 'bold')"
                        class="toolbar-btn font-bold"
                        title="Bold"
                    >
                        B
                    </button>


                    <button
                        type="button"
                        @click="command('header', 'italic')"
                        class="toolbar-btn italic"
                        title="Italic"
                    >
                        I
                    </button>


                    <button
                        type="button"
                        @click="command('header', 'underline')"
                        class="toolbar-btn underline"
                        title="Underline"
                    >
                        U
                    </button>


                    <button
                        type="button"
                        @click="command('header', 'strike')"
                        class="toolbar-btn line-through"
                        title="Strike"
                    >
                        S
                    </button>


                    <span class="toolbar-divider"></span>


                    <button
                        type="button"
                        @click="command('header', 'alignLeft')"
                        class="toolbar-btn"
                        title="Rata kiri"
                    >
                        ≡
                    </button>


                    <button
                        type="button"
                        @click="command('header', 'alignCenter')"
                        class="toolbar-btn"
                        title="Rata tengah"
                    >
                        ≡
                    </button>


                    <button
                        type="button"
                        @click="command('header', 'alignRight')"
                        class="toolbar-btn"
                        title="Rata kanan"
                    >
                        ≡
                    </button>


                    <button
                        type="button"
                        @click="command('header', 'alignJustify')"
                        class="toolbar-btn"
                        title="Justify"
                    >
                        ≡
                    </button>


                    <span class="toolbar-divider"></span>


                    <button
                        type="button"
                        @click="command('header', 'bulletList')"
                        class="toolbar-btn"
                        title="Bullet List"
                    >
                        •
                    </button>


                    <button
                        type="button"
                        @click="command('header', 'orderedList')"
                        class="toolbar-btn"
                        title="Numbered List"
                    >
                        1.
                    </button>


                    <button
                        type="button"
                        @click="command('header', 'blockquote')"
                        class="toolbar-btn"
                        title="Quote"
                    >
                        ❝
                    </button>


                    <button
                        type="button"
                        @click="command('header', 'horizontalRule')"
                        class="toolbar-btn"
                        title="Horizontal line"
                    >
                        ―
                    </button>


                    <button
                        type="button"
                        @click="command('header', 'superscript')"
                        class="toolbar-btn"
                        title="Superscript"
                    >
                        X²
                    </button>


                    <button
                        type="button"
                        @click="command('header', 'subscript')"
                        class="toolbar-btn"
                        title="Subscript"
                    >
                        X₂
                    </button>


                    <button
                        type="button"
                        @click="command('header', 'clear')"
                        class="toolbar-btn"
                        title="Clear formatting"
                    >
                        Tx
                    </button>

                </div>


                {{-- EDITOR HEADER --}}

                <div
                    id="header"
                    x-ref="headerEditor"
                    class="tiptap-editor"
                ></div>

            </div>

        </div>


        {{-- ========================================== --}}
        {{-- FOOTER --}}
        {{-- ========================================== --}}

        <div class="mb-5">

            <label class="block text-xs font-medium mb-1.5">
                Footer Dokumen
            </label>


            <div
                class="overflow-hidden rounded-xl border
                border-parchment-300 bg-white
                dark:border-slate-warm-700
                dark:bg-slate-warm-900"
            >


                {{-- TOOLBAR FOOTER --}}

                <div
                    class="flex flex-wrap items-center gap-1
                    border-b border-parchment-300
                    bg-parchment-50 p-2
                    dark:border-slate-warm-700
                    dark:bg-slate-warm-800"
                >

                    <button
                        type="button"
                        @click="command('footer', 'undo')"
                        class="toolbar-btn"
                        title="Undo"
                    >
                        ↶
                    </button>


                    <button
                        type="button"
                        @click="command('footer', 'redo')"
                        class="toolbar-btn"
                        title="Redo"
                    >
                        ↷
                    </button>


                    <span class="toolbar-divider"></span>


                    <button
                        type="button"
                        @click="command('footer', 'bold')"
                        class="toolbar-btn font-bold"
                    >
                        B
                    </button>


                    <button
                        type="button"
                        @click="command('footer', 'italic')"
                        class="toolbar-btn italic"
                    >
                        I
                    </button>


                    <button
                        type="button"
                        @click="command('footer', 'underline')"
                        class="toolbar-btn underline"
                    >
                        U
                    </button>


                    <span class="toolbar-divider"></span>


                    <button
                        type="button"
                        @click="command('footer', 'alignLeft')"
                        class="toolbar-btn"
                    >
                        ≡
                    </button>


                    <button
                        type="button"
                        @click="command('footer', 'alignCenter')"
                        class="toolbar-btn"
                    >
                        ≡
                    </button>


                    <button
                        type="button"
                        @click="command('footer', 'alignRight')"
                        class="toolbar-btn"
                    >
                        ≡
                    </button>


                    <button
                        type="button"
                        @click="command('footer', 'alignJustify')"
                        class="toolbar-btn"
                    >
                        ≡
                    </button>


                    <span class="toolbar-divider"></span>


                    <button
                        type="button"
                        @click="command('footer', 'bulletList')"
                        class="toolbar-btn"
                    >
                        •
                    </button>


                    <button
                        type="button"
                        @click="command('footer', 'orderedList')"
                        class="toolbar-btn"
                    >
                        1.
                    </button>


                    <button
                        type="button"
                        @click="command('footer', 'clear')"
                        class="toolbar-btn"
                    >
                        Tx
                    </button>

                </div>


                {{-- EDITOR FOOTER --}}

                <div
                    id="footer"
                    x-ref="footerEditor"
                    class="tiptap-editor"
                ></div>

            </div>

        </div>


        {{-- ========================================== --}}
        {{-- SUBMIT --}}
        {{-- ========================================== --}}

        <div class="flex justify-end">

            <button
                type="submit"
                class="inline-flex items-center gap-2
                rounded-xl bg-ink-900 px-6 py-3
                text-sm font-semibold text-white
                transition hover:opacity-90
                dark:bg-bronze-500
                dark:text-ink-900"
            >

                Buat Dokumen

                <span>→</span>

            </button>

        </div>

    </form>

</div>


<style>

/* ==========================================
   TIPTAP EDITOR
========================================== */

.tiptap-editor {

    min-height: 160px;

    padding: 20px;

    outline: none;

    font-family: Arial, sans-serif;

    font-size: 14px;

    line-height: 1.7;

    cursor: text;

}


/* ProseMirror adalah editor sebenarnya */

.tiptap-editor .ProseMirror {

    min-height: 120px;

    outline: none;

    cursor: text;

}


/* Supaya area kosong tetap bisa diklik */

.tiptap-editor .ProseMirror p {

    min-height: 24px;

    margin: 0 0 8px;

}


/* Paragraph */

.tiptap-editor p {

    margin: 0 0 8px;

}


/* Heading */

.tiptap-editor h1 {

    font-size: 2em;

    font-weight: 700;

    margin: 16px 0 10px;

}


.tiptap-editor h2 {

    font-size: 1.5em;

    font-weight: 700;

    margin: 14px 0 8px;

}


.tiptap-editor h3 {

    font-size: 1.25em;

    font-weight: 700;

    margin: 12px 0 8px;

}


/* Lists */

.tiptap-editor ul {

    list-style: disc;

    padding-left: 28px;

}


.tiptap-editor ol {

    list-style: decimal;

    padding-left: 28px;

}


/* Blockquote */

.tiptap-editor blockquote {

    border-left: 3px solid #94a3b8;

    padding-left: 14px;

    margin: 12px 0;

}


/* HR */

.tiptap-editor hr {

    border: 0;

    border-top: 1px solid #94a3b8;

    margin: 18px 0;

}


/* Link */

.tiptap-editor a {

    color: #2563eb;

    text-decoration: underline;

}


/* Image */

.tiptap-editor img {

    max-width: 100%;

    height: auto;

}


/* Table */

.tiptap-editor table {

    border-collapse: collapse;

    width: 100%;

}


.tiptap-editor th,
.tiptap-editor td {

    border: 1px solid #94a3b8;

    padding: 8px;

}


/* ==========================================
   TOOLBAR
========================================== */

.toolbar-btn {

    min-width: 34px;

    height: 34px;

    padding: 0 8px;

    border-radius: 7px;

    display: inline-flex;

    align-items: center;

    justify-content: center;

    font-size: 13px;

    transition:
        background-color 0.15s ease,
        transform 0.05s ease;

    user-select: none;

}


.toolbar-btn:hover {

    background: #e7e5e4;

}


.toolbar-btn:active {

    transform: scale(0.96);

}


.dark .toolbar-btn:hover {

    background: #334155;

}


.toolbar-select {

    height: 34px;

    border-radius: 7px;

    border: 1px solid #d6d3d1;

    background: white;

    padding: 0 8px;

    font-size: 12px;

    outline: none;

}


.toolbar-divider {

    width: 1px;

    height: 24px;

    background: #d6d3d1;

    margin: 0 3px;

}

</style>

@endsection
 
