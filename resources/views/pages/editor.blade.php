@extends('layouts.app')

@section('content')

<div x-data="wordDocumentEditor()" x-init="init()" class="min-h-screen">

    {{-- TOP BAR --}}
    <div
        class="editor-topbar sticky top-0 z-40 border-b border-parchment-300 bg-white/95 backdrop-blur dark:border-slate-warm-700 dark:bg-slate-warm-900/95">
        <div class="flex items-center justify-between px-5 py-3">

            {{-- LEFT --}}
            <div class="flex items-center gap-4">
                <a href="{{ route('documents') }}"
                    class="flex h-9 w-9 items-center justify-center rounded-lg hover:bg-parchment-100 dark:hover:bg-slate-warm-800">
                    ←
                </a>

                <div>
                    <h1 class="text-sm font-semibold text-ink-900 dark:text-parchment-50">
                        {{ $document->title }}
                    </h1>

                    <p class="text-xs text-slate-warm-500">
                        Dokumen #{{ $document->id }}
                    </p>
                </div>
            </div>

            {{-- RIGHT --}}
            <div class="flex items-center gap-3">

                <button type="button" @click="showSignaturePicker = true" class="toolbar-button"
                    title="Pilih Tanda Tangan">
                    <b>TTD</b>
                </button>

                <span x-show="saveStatus === 'saving'" class="text-xs text-slate-warm-500">
                    Menyimpan...
                </span>

                <span x-show="saveStatus === 'saved'" class="text-xs text-green-600">
                    ✓ Tersimpan
                </span>

                <span x-show="saveStatus === 'error'" class="text-xs text-red-600">
                    Gagal menyimpan
                </span>

                <button type="button" @click="saveDocument()"
                    class="rounded-xl bg-ink-900 px-5 py-2.5 text-sm font-semibold text-white hover:opacity-90 dark:bg-bronze-500 dark:text-ink-900">
                    Save
                </button>

            </div>
        </div>
    </div>

    {{-- TINYMCE TOOLBAR (SATU INSTANCE) --}}
    <div id="body-toolbar-container" class="sticky top-[57px] z-30"
        style="display:flex; flex-direction:row; align-items:center;"></div>

    {{-- DOCUMENT AREA --}}
    <main class="documentPrintArea bg-slate-100 px-4 py-10 dark:bg-slate-warm-950">

        <div class="mx-auto w-full max-w-[794px]">

            {{-- SATU EDITOR UNTUK SEMUA KERTAS --}}
            <div id="document-editor" class="document-editor-canvas"></div>

            {{-- TAMBAH HALAMAN --}}
            <div class="mt-6 flex justify-center print:hidden">

                <button type="button" @click="addPage()"
                    class="inline-flex items-center gap-2 rounded-xl border border-dashed border-parchment-400 bg-white px-5 py-2.5 text-sm font-medium text-ink-900 hover:bg-parchment-50 dark:border-slate-warm-600 dark:bg-slate-warm-900 dark:text-parchment-100">
                    + Tambah Halaman
                </button>

            </div>

        </div>
    </main>

    {{-- SIGNATURE PICKER --}}
    <div x-show="showSignaturePicker" x-cloak
        class="fixed inset-0 z-[100] flex items-center justify-center bg-black/40 px-4"
        @click.self="showSignaturePicker = false">

        <div class="w-full max-w-lg rounded-2xl bg-white p-6 shadow-2xl dark:bg-slate-warm-900" @click.stop>

            {{-- HEADER --}}
            <div class="mb-5 flex items-center justify-between">

                <div>
                    <h2 class="text-base font-semibold text-ink-900 dark:text-parchment-50">
                        Pilih Tanda Tangan
                    </h2>

                    <p class="mt-1 text-xs text-slate-warm-500">
                        Pilih tanda tangan yang sudah tersimpan di database.
                    </p>
                </div>

                <button type="button" @click="showSignaturePicker = false"
                    class="text-xl text-slate-warm-400 hover:text-slate-warm-700">
                    ×
                </button>

            </div>

            {{-- TIDAK ADA SIGNATURE --}}
            <template x-if="signatures.length === 0">

                <div class="rounded-xl border border-dashed border-parchment-300 p-6 text-center">

                    <p class="text-sm text-slate-warm-500">
                        Belum ada tanda tangan tersimpan.
                    </p>

                    <a href="{{ route('signatures') }}"
                        class="mt-3 inline-block text-xs font-semibold text-ink-900 hover:underline">
                        Kelola Tanda Tangan
                    </a>

                </div>

            </template>

            {{-- SIGNATURE DATABASE LIST --}}
            <div x-show="signatures.length > 0" class="grid max-h-[400px] gap-3 overflow-y-auto">

                <template x-for="signature in signatures" :key="signature.id">

                    <button type="button" @click="selectSignature(signature)"
                        class="group w-full rounded-xl border border-parchment-300 p-4 text-left transition hover:border-ink-900 hover:bg-parchment-50 dark:border-slate-warm-700 dark:hover:bg-slate-warm-800">

                        <div class="flex items-center gap-4">

                            {{-- PREVIEW TTD DATABASE --}}
                            <div class="flex h-20 w-32 items-center justify-center rounded-lg border bg-white p-2">
                                <img :src="signature.url" :alt="signature.name"
                                    class="max-h-full max-w-full object-contain">
                            </div>

                            {{-- INFO TTD --}}
                            <div class="flex-1">

                                <div class="text-sm font-semibold text-ink-900 dark:text-parchment-100"
                                    x-text="signature.name"></div>

                                <div class="mt-1 text-xs text-slate-warm-500">
                                    Klik untuk memilih tanda tangan ini
                                </div>

                            </div>

                            {{-- CHECK --}}
                            <div x-show="selectedSignatureId === signature.id"
                                class="flex h-6 w-6 items-center justify-center rounded-full bg-green-500 text-xs font-bold text-white">
                                ✓
                            </div>

                        </div>

                    </button>

                </template>

            </div>

        </div>
    </div>

</div>

@push('styles')
<style>
    .toolbar-button {
        display: inline-flex;
        width: 34px;
        height: 34px;
        min-width: 34px;
        align-items: center;
        justify-content: center;
        border-radius: 7px;
        font-size: 14px;
        cursor: pointer;
        transition: background-color 0.2s;
    }

    .toolbar-button:hover {
        background: rgb(245 242 235);
    }

    .toolbar-color {
        width: 30px;
        height: 30px;
        padding: 2px;
        border: 1px solid rgb(214 211 204);
        border-radius: 7px;
        cursor: pointer;
        background: white;
    }

    .toolbar-color::-webkit-color-swatch-wrapper {
        padding: 0;
    }

    .toolbar-color::-webkit-color-swatch {
        border: 1px solid #d1d5db;
        border-radius: 6px;
    }

    [contenteditable="true"]:focus {
        outline: none;
    }

    .toolbar-divider {
        width: 1px;
        height: 24px;
        margin: 0 4px;
        background: rgb(214 211 204);
    }

    .toolbar-select {
        height: 34px;
        min-width: 130px;
        padding: 0 8px;
        border: 1px solid rgb(214 211 204);
        border-radius: 7px;
        background: white;
        font-size: 13px;
        outline: none;
    }

    .toolbar-select:focus {
        border-color: rgb(180 140 80);
    }

    @media (max-width: 768px) {
        .toolbar-select {
            min-width: 100px;
        }
    }

    /* =========================================
       KANVAS EDITOR (SATU INSTANCE)
       ========================================= */
    .document-editor-canvas {
        display: flex;
        flex-direction: column;
        gap: 24px;
    }

    /* Setiap "kertas" di dalam editor */
    .doc-sheet {
        position: relative;
        width: 210mm;
        min-height: 297mm;
        height: 297mm;
        max-height: 297mm;
        margin: 0 auto;
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

    /* Region di dalam satu kertas: header, body, footer */
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

    /* Garis pemisah antar kertas (hanya di layar) */
    .doc-sheet + .doc-sheet {
        margin-top: 24px;
    }

    /* Toolbar hanya satu, selalu tampil */
    #body-toolbar-container .tox-tinymce {
        display: block !important;
    }

    @media print {
        body * {
            visibility: hidden;
        }

        #document-editor,
        #document-editor * {
            visibility: visible;
        }

        #document-editor {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
            margin: 0;
            padding: 0;
            box-shadow: none !important;
            border: none !important;
            background: white !important;
        }

        .doc-sheet {
            box-shadow: none !important;
            margin: 0 !important;
            page-break-after: always;
        }

        @page {
            size: A4;
            margin: 20mm;
        }

        * {
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
    }
</style>
@endpush

@push('scripts')
<script>

    function wordDocumentEditor() {
        return {

            documentId: @js($document -> id),

            // Semua TTD berasal dari database
            signatures: @js($signatures ?? []),

            // TTD yang sedang dipakai dokumen
            selectedSignature: @js($document -> signature_data['signatureUrl'] ?? null),
            selectedSignatureId: @js($document -> signature_data['signatureId'] ?? null),
            signatureX: @js($document -> signature_data['signatureX'] ?? 500),
            signatureY: @js($document -> signature_data['signatureY'] ?? 650),
            headerHtml: @js($document -> header_data['content'] ?? ''),
            footerHtml: @js($document -> footer_data['content'] ?? ''),

            showSignaturePicker: false,

            isDraggingSignature: false,

            dragStartX: 0,
            dragStartY: 0,

            initialSignatureX: 0,
            initialSignatureY: 0,

            dragOffsetX: 0,
            dragOffsetY: 0,

            signaturePageRect: null,

            saveStatus: 'saved',
            changed: false,

            // PAGES (hanya data, dirender sebagai sheet di dalam satu editor)
            pages: (
                @js(
                    $document -> body_content['pages']
                    ?? [$document -> body_content['content'] ?? '']
                )
            ).map((html, i) => ({
                uid: 'page-' + i,
                html
            })),

            pageSeq: (
                @js(
                    $document -> body_content['pages']
                    ?? [$document -> body_content['content'] ?? '']
                )
            ).length,

            // INIT
            init() {

                this.$nextTick(() => {
                    this.initSingleEditor();
                });

                // CTRL + S
                document.addEventListener('keydown', (e) => {
                    if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 's') {
                        e.preventDefault();
                        this.saveDocument();
                    }
                });
            },

            // SATU INSTANCE TINYMCE UNTUK SEMUA KERTAS
            initSingleEditor() {

                const editorEl = document.getElementById('document-editor');
                if (!editorEl) return;

                // Bangun konten awal: setiap kertas berisi header + body + footer
                let html = '';

                this.pages.forEach((page, i) => {
                    html += '<div class="doc-sheet" data-sheet-type="page" data-page-uid="' + page.uid + '">';

                    // Header (hanya di halaman pertama)
                    if (i === 0) {
                        html += '<div class="doc-sheet-header" data-region="header">';
                        html += this.headerHtml || '<p></p>';
                        html += '</div>';
                    }

                    // Body
                    html += '<div class="doc-sheet-body" data-region="body">';
                    html += page.html || '<p></p>';
                    html += '</div>';

                    // Footer (hanya di halaman terakhir)
                    if (i === this.pages.length - 1) {
                        html += '<div class="doc-sheet-footer" data-region="footer">';
                        html += this.footerHtml || '<p></p>';
                        html += '</div>';
                    }

                    html += '</div>';
                });

                editorEl.innerHTML = html;

                this.setupSignatureEvents();

                window.initBodyEditor('#document-editor', '', () => {
                    this.markAsChanged();
                    this.renderSignature();
                });
            },

            // Event delegation untuk tanda tangan di dalam editor TinyMCE
            setupSignatureEvents() {
                const editorEl = document.getElementById('document-editor');
                if (!editorEl) return;

                editorEl.addEventListener('mousedown', (e) => {
                    const sigEl = e.target.closest('.doc-signature');
                    if (!sigEl) return;
                    if (e.target.closest('.doc-signature-remove')) return;
                    this.startDragSignature(e, sigEl);
                });

                editorEl.addEventListener('click', (e) => {
                    if (e.target.closest('.doc-signature-remove')) {
                        e.preventDefault();
                        e.stopPropagation();
                        this.removeSignature();
                    }
                });

                // Drag global supaya tetap jalan walau kursor keluar dari elemen
                document.addEventListener('mousemove', (e) => {
                    this.dragSignature(e);
                });

                document.addEventListener('mouseup', () => {
                    this.stopDragSignature();
                });
            },

            // TAMBAH HALAMAN (tambah sheet baru di akhir, sebelum footer)
            addPage() {

                const uid = 'page-' + (this.pageSeq++);

                this.pages.push({
                    uid,
                    html: ''
                });

                const editor = tinymce.get('document-editor');
                if (!editor) return;

                const body = editor.getBody();

                // Pindahkan footer dari halaman terakhir lama ke halaman baru
                const oldFooter = body.querySelector('.doc-sheet-footer');
                const oldFooterHtml = oldFooter ? oldFooter.innerHTML : '';

                // Hapus footer lama
                if (oldFooter) {
                    editor.dom.remove(oldFooter);
                }

                // Buat sheet baru (dengan footer)
                const sheet = editor.dom.create('div', {
                    'class': 'doc-sheet',
                    'data-sheet-type': 'page',
                    'data-page-uid': uid
                });

                const bodyRegion = editor.dom.create('div', {
                    'class': 'doc-sheet-body',
                    'data-region': 'body'
                }, '<p></p>');

                const footerRegion = editor.dom.create('div', {
                    'class': 'doc-sheet-footer',
                    'data-region': 'footer'
                });

                footerRegion.innerHTML = oldFooterHtml || '<p></p>';

                sheet.appendChild(bodyRegion);
                sheet.appendChild(footerRegion);

                body.appendChild(sheet);

                // Pindahkan tanda tangan ke halaman baru
                this.renderSignature();

                this.markAsChanged();

                this.$nextTick(() => {
                    sheet.scrollIntoView({ behavior: 'smooth', block: 'center' });
                });
            },

            // HAPUS HALAMAN
            async removePage(index) {

                if (this.pages.length <= 1) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Gak bisa dihapus',
                        text: 'Dokumen minimal harus punya 1 halaman.',
                        confirmButtonColor: '#1B2A4A'
                    });
                    return;
                }

                const result = await Swal.fire({
                    icon: 'warning',
                    title: 'Hapus halaman ' + (index + 1) + '?',
                    text: 'Isi di halaman ini akan hilang.',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, hapus',
                    cancelButtonText: 'Batal',
                    confirmButtonColor: '#dc2626'
                });

                if (!result.isConfirmed) {
                    return;
                }

                const removedUid = this.pages[index].uid;

                const editor = tinymce.get('document-editor');
                if (editor) {
                    const sheet = editor.getBody().querySelector('[data-page-uid="' + removedUid + '"]');
                    if (sheet) {
                        editor.dom.remove(sheet);
                    }
                }

                this.pages.splice(index, 1);
                this.markAsChanged();
            },

            markAsChanged() {
                this.changed = true;
                this.saveStatus = 'idle';
            },

            // =========================================
            // SIGNATURE
            // =========================================

            selectSignature(signature) {
                this.selectedSignature = signature.url;
                this.selectedSignatureId = signature.id;
                this.signatureX = 500;
                this.signatureY = 650;
                this.showSignaturePicker = false;
                this.renderSignature();
                this.markAsChanged();
            },

            renderSignature() {
                const editor = tinymce.get('document-editor');
                if (!editor) return;

                const body = editor.getBody();

                // Hapus signature lama
                body.querySelectorAll('.doc-signature').forEach((el) => el.remove());

                if (!this.selectedSignature) return;

                // Tampilkan di halaman terakhir
                const sheets = body.querySelectorAll('.doc-sheet[data-sheet-type="page"]');
                const lastSheet = sheets[sheets.length - 1];
                if (!lastSheet) return;

                const sig = editor.dom.create('div', {
                    'class': 'doc-signature',
                    'data-signature': '1',
                    style: 'position:absolute;left:' + this.signatureX + 'px;top:' + this.signatureY + 'px;z-index:30;cursor:move;'
                });

                sig.innerHTML =
                    '<img src="' + this.selectedSignature + '" style="max-height:80px;max-width:180px;pointer-events:none;display:block;" />' +
                    '<button type="button" class="doc-signature-remove" style="position:absolute;top:-8px;right:-8px;width:20px;height:20px;border-radius:50%;background:#dc2626;color:#fff;font-size:12px;line-height:20px;text-align:center;border:none;cursor:pointer;">×</button>';

                lastSheet.appendChild(sig);
            },

            removeSignature() {
                this.selectedSignature = null;
                this.selectedSignatureId = null;
                this.signatureX = 500;
                this.signatureY = 650;

                const editor = tinymce.get('document-editor');
                if (editor) {
                    editor.getBody().querySelectorAll('.doc-signature').forEach((el) => el.remove());
                }

                this.markAsChanged();
            },

            // =========================================
            // SAVE DOCUMENT
            // =========================================

            async saveDocument() {

                this.saveStatus = 'saving';

                const editor = tinymce.get('document-editor');
                const body = editor ? editor.getBody() : null;

                // Baca header (region di halaman pertama)
                const headerRegion = body?.querySelector('.doc-sheet-header[data-region="header"]');
                const headerContent = headerRegion ? headerRegion.innerHTML : (this.headerHtml || '');

                // Baca footer (region di halaman terakhir)
                const footerRegion = body?.querySelector('.doc-sheet-footer[data-region="footer"]');
                const footerContent = footerRegion ? footerRegion.innerHTML : (this.footerHtml || '');

                // Baca semua halaman body
                const pagesHtml = [];
                if (body) {
                    body.querySelectorAll('.doc-sheet[data-sheet-type="page"]').forEach((sheet) => {
                        const bodyRegion = sheet.querySelector('.doc-sheet-body[data-region="body"]');
                        pagesHtml.push(bodyRegion ? bodyRegion.innerHTML : '');
                    });
                }

                // Sinkronkan ke data pages
                if (pagesHtml.length > 0) {
                    this.pages = pagesHtml.map((html, i) => ({
                        uid: 'page-' + i,
                        html
                    }));
                }

                const payload = {
                    title: @js($document -> title),
                    type: @js($document -> type ?? 'surat'),
                    header_data: {
                        nomorSurat: @js($document -> header_data['nomorSurat'] ?? ''),
                        content: headerContent,
                    },
                    body_content: {
                        pages: pagesHtml
                    },
                    footer_data: {
                        content: footerContent,
                    },
                    signature_data: {
                        signatureId: this.selectedSignatureId,
                        signatureUrl: this.selectedSignature,
                        signatureX: this.signatureX,
                        signatureY: this.signatureY
                    },
                    status: this.selectedSignature ? 'draft' : 'pending'
                };

                try {
                    await window.axios.put(`/documents/${this.documentId}`, payload);
                    this.saveStatus = 'saved';
                    this.changed = false;
                } catch (error) {
                    console.error(error);
                    this.saveStatus = 'error';
                }
            },

            // =========================================
            // SAVE AS NEW DOCUMENT
            // =========================================

            async saveAsNewDocument(newTitle) {

                const editor = tinymce.get('document-editor');
                const body = editor ? editor.getBody() : null;

                const headerRegion = body?.querySelector('.doc-sheet-header[data-region="header"]');
                const footerRegion = body?.querySelector('.doc-sheet-footer[data-region="footer"]');

                const pagesHtml = [];
                if (body) {
                    body.querySelectorAll('.doc-sheet[data-sheet-type="page"]').forEach((sheet) => {
                        const bodyRegion = sheet.querySelector('.doc-sheet-body[data-region="body"]');
                        pagesHtml.push(bodyRegion ? bodyRegion.innerHTML : '');
                    });
                }

                const payload = {
                    title: newTitle,
                    type: @js($document -> type ?? 'surat'),
                    header_data: {
                        nomorSurat: @js($document -> header_data['nomorSurat'] ?? ''),
                        content: headerRegion ? headerRegion.innerHTML : (this.headerHtml || ''),
                    },
                    body_content: {
                        pages: pagesHtml
                    },
                    footer_data: {
                        content: footerRegion ? footerRegion.innerHTML : (this.footerHtml || ''),
                    },
                    signature_data: {
                        signatureId: this.selectedSignatureId,
                        signatureUrl: this.selectedSignature,
                        signatureX: this.signatureX,
                        signatureY: this.signatureY
                    }
                };

                const res = await window.axios.post('/documents/save-as', payload);
                return res.data.id;
            },

            // =========================================
            // DRAG SIGNATURE
            // =========================================

            startDragSignature(event, sigEl) {

                event.preventDefault();

                const page = sigEl.closest('.doc-sheet');
                if (!page) return;

                const pageRect = page.getBoundingClientRect();
                const rect = sigEl.getBoundingClientRect();

                this.isDraggingSignature = true;
                this.dragStartX = event.clientX;
                this.dragStartY = event.clientY;
                this.initialSignatureX = this.signatureX;
                this.initialSignatureY = this.signatureY;
                this.dragOffsetX = event.clientX - rect.left;
                this.dragOffsetY = event.clientY - rect.top;
                this.signaturePageRect = pageRect;

                document.body.style.userSelect = 'none';
            },

            dragSignature(event) {

                if (!this.isDraggingSignature) return;

                const pageRect = this.signaturePageRect ||
                    document.querySelector('.doc-sheet')?.getBoundingClientRect();

                if (!pageRect) return;

                const nextX = event.clientX - pageRect.left - this.dragOffsetX;
                const nextY = event.clientY - pageRect.top - this.dragOffsetY;

                const minX = 20;
                const maxX = Math.max(minX, pageRect.width - 200);
                const minY = 140;
                const maxY = Math.max(minY, pageRect.height - 140);

                this.signatureX = Math.min(Math.max(nextX, minX), maxX);
                this.signatureY = Math.min(Math.max(nextY, minY), maxY);

                // Update posisi visual elemen tanda tangan
                const editor = tinymce.get('document-editor');
                if (editor) {
                    const sigEl = editor.getBody().querySelector('.doc-signature');
                    if (sigEl) {
                        sigEl.style.left = this.signatureX + 'px';
                        sigEl.style.top = this.signatureY + 'px';
                    }
                }

                this.markAsChanged();
            },

            stopDragSignature() {

                if (!this.isDraggingSignature) return;

                this.isDraggingSignature = false;
                this.signaturePageRect = null;
                document.body.style.userSelect = '';
                this.markAsChanged();
            }
        };
    }

    // =========================================
    // 2. PROTEKSI UNSAVED CHANGES
    // =========================================

    window.hasUnsavedChanges = false;

    window.addEventListener('beforeunload', function (e) {
        if (window.hasUnsavedChanges) {
            e.preventDefault();
            e.returnValue = '';
        }
    });

    // =========================================
    // 3. HANDLE LINK NAVIGATION
    // =========================================

    document.addEventListener('DOMContentLoaded', function () {

        const editorRoot = document.querySelector('[x-data="wordDocumentEditor()"]');
        if (!editorRoot) return;

        Alpine.effect(() => {
            const data = Alpine.$data(editorRoot);
            window.hasUnsavedChanges = data.changed;
        });

        document.addEventListener('click', function (e) {

            const link = e.target.closest('a[href]');
            if (!link) return;
            if (!window.hasUnsavedChanges) return;

            const href = link.getAttribute('href');
            if (!href || href.startsWith('#') || link.target === '_blank') return;

            e.preventDefault();

            const destinationUrl = link.href;

            Swal.fire({
                icon: 'warning',
                title: 'Perubahan belum disimpan',
                text: 'Kamu punya perubahan yang belum disimpan. Simpan dulu sebelum keluar?',
                showDenyButton: true,
                showCancelButton: true,
                confirmButtonText: 'Simpan & Keluar',
                denyButtonText: 'Buang Perubahan',
                cancelButtonText: 'Batal',
                confirmButtonColor: '#1B2A4A',
                denyButtonColor: '#dc2626',
                footer: '<a href="#" id="swal-save-as-link" class="text-xs">' +
                    'atau Simpan Sebagai Dokumen Baru' +
                    '</a>',

                didOpen: () => {
                    document.getElementById('swal-save-as-link').addEventListener('click', async (evt) => {
                        evt.preventDefault();
                        Swal.close();

                        const { value: newTitle } = await Swal.fire({
                            title: 'Simpan Sebagai',
                            input: 'text',
                            inputLabel: 'Judul dokumen baru',
                            inputValue: @js(($document -> title ?? 'Dokumen') . ' (Salinan)'),
                            showCancelButton: true,
                            confirmButtonText: 'Simpan Sebagai Baru',
                            cancelButtonText: 'Batal',
                            confirmButtonColor: '#1B2A4A'
                        });

                        if (!newTitle) return;

                        try {
                            const data = Alpine.$data(editorRoot);
                            const newId = await data.saveAsNewDocument(newTitle);
                            window.hasUnsavedChanges = false;
                            window.location.href = '/documents/' + newId + '/edit';
                        } catch (err) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal',
                                text: 'Gagal menyimpan sebagai dokumen baru.',
                                confirmButtonColor: '#1B2A4A'
                            });
                            console.error(err);
                        }
                    });
                }

            }).then(async (result) => {

                if (result.isConfirmed) {
                    try {
                        const data = Alpine.$data(editorRoot);
                        await data.saveDocument();
                        window.hasUnsavedChanges = false;
                        window.location.href = destinationUrl;
                    } catch (err) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: 'Gagal menyimpan dokumen.',
                            confirmButtonColor: '#1B2A4A'
                        });
                        console.error(err);
                    }
                } else if (result.isDenied) {
                    window.hasUnsavedChanges = false;
                    window.location.href = destinationUrl;
                }
            });
        });
    });
</script>
@endpush

@endsection