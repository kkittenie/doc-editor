@extends('layouts.app')

@section('content')

<div
    x-data="{
        loadingTemplate: null,

        headerHtml: '<p></p>',
        footerHtml: '<p></p>',
        bodyHtml: '<p></p>',

        init() {
            this.$nextTick(() => {
                this.initTinyMCEditors();
            });
        },

        initTinyMCEditors() {

            if (typeof window.initDocumentEditor === 'function') {

                // HEADER (boleh upload & drag logo)
                window.initDocumentEditor(
                    '#header',
                    '<p></p>',
                    true
                );

                // FOOTER (tanpa logo)
                window.initDocumentEditor(
                    '#footer',
                    '<p></p>',
                    false
                );

            }

        },


        // ==========================================
        // SET HEADER
        // ==========================================

        setHeaderContent(content) {

            const html = content || '<p></p>';

            this.headerHtml = html;

            const editor = window.tinymce?.get('header');

            if (editor) {
                editor.setContent(html);
            }

        },


        // ==========================================
        // SET FOOTER
        // ==========================================

        setFooterContent(content) {

            const html = content || '<p></p>';

            this.footerHtml = html;

            const editor = window.tinymce?.get('footer');

            if (editor) {
                editor.setContent(html);
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
        // SYNC BEFORE SUBMIT
        // ==========================================

        syncBeforeSubmit() {

            const headerEditor = window.tinymce?.get('header');
            const footerEditor = window.tinymce?.get('footer');

            if (headerEditor) {
                this.headerHtml = headerEditor.getContent();
            }

            if (footerEditor) {
                this.footerHtml = footerEditor.getContent();
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

                {{-- EDITOR HEADER (TinyMCE) --}}

                <textarea
                    id="header"
                    class="w-full"
                ></textarea>

            </div>

        </div>


        {{-- ========================================== --}}
        {{-- FOOTER --}}
        {{-- ========================================== --}}

        <div class="mb-5">

            <label class="block text-xs font-medium mb-1.5">
                Footer 
            </label>


            <div
                class="overflow-hidden rounded-xl border
                border-parchment-300 bg-white
                dark:border-slate-warm-700
                dark:bg-slate-warm-900"
            >

                {{-- EDITOR FOOTER (TinyMCE) --}}

                <textarea
                    id="footer"
                    class="w-full"
                ></textarea>

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

@endsection