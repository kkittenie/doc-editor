@extends('layouts.app')

@section('content')

<div x-data="wordDocumentEditor()" x-init="init()" class="min-h-screen">

    {{-- ========================================= --}}
    {{-- TOP BAR --}}
    {{-- ========================================= --}}
    <div
        class="editor-topbar sticky top-0 z-40 border-b border-parchment-300 bg-white/95 backdrop-blur dark:border-slate-warm-700 dark:bg-slate-warm-900/95"
    >
        <div class="flex items-center justify-between px-5 py-3">

            {{-- LEFT --}}
            <div class="flex items-center gap-4">
                <a
                    href="{{ route('documents') }}"
                    class="flex h-9 w-9 items-center justify-center rounded-lg hover:bg-parchment-100 dark:hover:bg-slate-warm-800"
                >
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

                <button
                    type="button"
                    @click="showSignaturePicker = true"
                    class="toolbar-button"
                    title="Pilih Tanda Tangan"
                >
                    <b>TTD</b>
                </button>

                <span
                    x-show="saveStatus === 'saving'"
                    class="text-xs text-slate-warm-500"
                >
                    Menyimpan...
                </span>

                <span
                    x-show="saveStatus === 'saved'"
                    class="text-xs text-green-600"
                >
                    ✓ Tersimpan
                </span>

                <span
                    x-show="saveStatus === 'error'"
                    class="text-xs text-red-600"
                >
                    Gagal menyimpan
                </span>

                <button
                    type="button"
                    @click="saveDocument()"
                    class="rounded-xl bg-ink-900 px-5 py-2.5 text-sm font-semibold text-white hover:opacity-90 dark:bg-bronze-500 dark:text-ink-900"
                >
                    Save
                </button>

            </div>
        </div>
    </div>

    {{-- ========================================= --}}
    {{-- TINYMCE TOOLBAR --}}
    {{-- ========================================= --}}
    <div
        id="body-toolbar-container"
        class="sticky top-[57px] z-30 min-h-[42px] border-b border-parchment-300 bg-white px-2 dark:border-slate-warm-700 dark:bg-slate-warm-900"
    ></div>

    {{-- ========================================= --}}
    {{-- DOCUMENT AREA --}}
    {{-- ========================================= --}}
    <main class="documentPrintArea bg-slate-100 px-4 py-10 dark:bg-slate-warm-950">

        <div class="mx-auto w-full max-w-[794px] space-y-8">

            <template x-for="(page, index) in pages" :key="page.uid">

                <div class="document-page relative w-full bg-white shadow-xl">

                    {{-- PAGE INFO BAR --}}
                    <div
                        class="flex items-center justify-between px-6 pt-3 text-xs text-slate-400 print:hidden"
                    >
                        <span
                            x-text="'Halaman ' + (index + 1) + ' dari ' + pages.length"
                        ></span>

                        <button
                            type="button"
                            x-show="pages.length > 1"
                            @click="removePage(index)"
                            class="text-red-500 hover:underline"
                        >
                            Hapus Halaman Ini
                        </button>
                    </div>

                    {{-- HEADER / KOP SURAT --}}
                    <div
                        x-show="index === 0"
                        class="document-header relative px-[80px] pt-[20px] text-black"
                    >
                        {!! $document->header_data['content'] ?? '' !!}

                        <div class="mt-4 border-b-2 border-black"></div>
                    </div>

                    {{-- PENANDA HALAMAN LANJUTAN --}}
                    <div
                        x-show="index > 0"
                        class="px-[80px] pt-[40px] text-xs italic text-slate-400"
                    >
                        ...lanjutan halaman
                        <span x-text="index + 1"></span>
                    </div>

                    {{-- BODY EDITOR --}}
                    <div class="px-[80px] py-8">
                        <div
                            :id="'document-editor-' + page.uid"
                            contenteditable="true"
                            class="document-body"
                        ></div>
                    </div>

                    {{-- FOOTER --}}
                    <div
                        x-show="index === pages.length - 1"
                        class="px-[80px] pb-20 text-sm text-black"
                    >
                        <div class="ml-auto w-[260px] text-center">

                            <div class="text-left">
                                {!! $document->footer_data['content'] ?? '' !!}
                            </div>

                        </div>
                    </div>

                    {{-- ========================================= --}}
                    {{-- DRAGGABLE SIGNATURE --}}
                    {{-- ========================================= --}}
                    <template x-if="index === pages.length - 1 && selectedSignature">

                        <div
                            class="absolute z-30 cursor-move select-none"
                            :style="`left: ${signatureX}px; top: ${signatureY}px;`"
                            @mousedown="startDragSignature($event)"
                            @mousemove.window="dragSignature($event)"
                            @mouseup.window="stopDragSignature()"
                        >

                            <div
                                class="relative rounded-lg border-2 border-transparent p-1"
                                :class="
                                    isDraggingSignature
                                        ? 'border-blue-500 bg-blue-50/20'
                                        : 'hover:border-blue-300'
                                "
                            >

                                {{-- GAMBAR DARI DATABASE --}}
                                <img
                                    :src="selectedSignature"
                                    alt="Tanda Tangan"
                                    draggable="false"
                                    class="pointer-events-none max-h-20 max-w-[180px] object-contain"
                                >

                                {{-- HAPUS TTD DARI DOKUMEN --}}
                                <button
                                    type="button"
                                    @mousedown.stop
                                    @click.stop="
                                        selectedSignature = null;
                                        selectedSignatureId = null;
                                        signatureX = 500;
                                        signatureY = 650;
                                        markAsChanged();
                                    "
                                    class="absolute -right-3 -top-3 flex h-6 w-6 items-center justify-center rounded-full bg-red-500 text-xs text-white shadow hover:bg-red-600"
                                    title="Hapus Tanda Tangan"
                                >
                                    ×
                                </button>

                            </div>

                        </div>

                    </template>

                </div>

            </template>

            {{-- TAMBAH HALAMAN --}}
            <div class="flex justify-center print:hidden">

                <button
                    type="button"
                    @click="addPage()"
                    class="inline-flex items-center gap-2 rounded-xl border border-dashed border-parchment-400 bg-white px-5 py-2.5 text-sm font-medium text-ink-900 hover:bg-parchment-50 dark:border-slate-warm-600 dark:bg-slate-warm-900 dark:text-parchment-100"
                >
                    + Tambah Halaman
                </button>

            </div>

        </div>
    </main>

    {{-- ========================================= --}}
    {{-- SIGNATURE PICKER --}}
    {{-- ========================================= --}}
    <div
        x-show="showSignaturePicker"
        x-cloak
        class="fixed inset-0 z-[100] flex items-center justify-center bg-black/40 px-4"
        @click.self="showSignaturePicker = false"
    >

        <div
            class="w-full max-w-lg rounded-2xl bg-white p-6 shadow-2xl dark:bg-slate-warm-900"
            @click.stop
        >

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

                <button
                    type="button"
                    @click="showSignaturePicker = false"
                    class="text-xl text-slate-warm-400 hover:text-slate-warm-700"
                >
                    ×
                </button>

            </div>

            {{-- ========================================= --}}
            {{-- TIDAK ADA SIGNATURE --}}
            {{-- ========================================= --}}
            <template x-if="signatures.length === 0">

                <div class="rounded-xl border border-dashed border-parchment-300 p-6 text-center">

                    <p class="text-sm text-slate-warm-500">
                        Belum ada tanda tangan tersimpan.
                    </p>

                    <a
                        href="{{ route('signatures') }}"
                        class="mt-3 inline-block text-xs font-semibold text-ink-900 hover:underline"
                    >
                        Kelola Tanda Tangan
                    </a>

                </div>

            </template>

            {{-- ========================================= --}}
            {{-- SIGNATURE DATABASE LIST --}}
            {{-- ========================================= --}}
            <div
                x-show="signatures.length > 0"
                class="grid max-h-[400px] gap-3 overflow-y-auto"
            >

                <template
                    x-for="signature in signatures"
                    :key="signature.id"
                >

                    <button
                        type="button"
                        @click="
                            selectedSignature = signature.url;
                            selectedSignatureId = signature.id;
                            signatureX = 500;
                            signatureY = 650;
                            showSignaturePicker = false;
                            markAsChanged();
                        "
                        class="group w-full rounded-xl border border-parchment-300 p-4 text-left transition hover:border-ink-900 hover:bg-parchment-50 dark:border-slate-warm-700 dark:hover:bg-slate-warm-800"
                    >

                        <div class="flex items-center gap-4">

                            {{-- PREVIEW TTD DATABASE --}}
                            <div class="flex h-20 w-32 items-center justify-center rounded-lg border bg-white p-2">
                                <img
                                    :src="signature.url"
                                    :alt="signature.name"
                                    class="max-h-full max-w-full object-contain"
                                >
                            </div>

                            {{-- INFO TTD --}}
                            <div class="flex-1">

                                <div
                                    class="text-sm font-semibold text-ink-900 dark:text-parchment-100"
                                    x-text="signature.name"
                                ></div>

                                <div class="mt-1 text-xs text-slate-warm-500">
                                    Klik untuk memilih tanda tangan ini
                                </div>

                            </div>

                            {{-- CHECK --}}
                            <div
                                x-show="selectedSignatureId === signature.id"
                                class="flex h-6 w-6 items-center justify-center rounded-full bg-green-500 text-xs font-bold text-white"
                            >
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

    .document-page {
        position: relative;
        width: 210mm;
        min-height: 297mm;
        max-height: 297mm;
        margin: 0 auto;
        box-sizing: border-box;
    }

    .document-body {
        min-height: 650px;
        font-family: Arial, sans-serif;
        font-size: 14px;
        line-height: 1.8;
    }

    .document-body:focus {
        outline: none;
    }

    .document-body p {
        margin: 0 0 10px 0;
    }

    .document-header {
        position: relative;
        min-height: 110px;
        overflow: hidden;
    }

    .document-header img.drag-image {
        position: absolute !important;
        z-index: 10 !important;
        display: block !important;
        width: auto !important;
        max-width: 150px !important;
        height: auto !important;
        max-height: 70px !important;
    }

    .document-header img.drag-image ~ p,
    .document-header img.drag-image ~ h1,
    .document-header img.drag-image ~ h2,
    .document-header img.drag-image ~ h3,
    .document-header img.drag-image ~ h4,
    .document-header img.drag-image ~ h5,
    .document-header img.drag-image ~ h6 {
        position: relative;
        z-index: 2;
        margin-left: 190px;
    }

    .document-body table {
        width: 100%;
        border-collapse: collapse;
        margin: 10px 0;
    }

    .document-body table,
    .document-body th,
    .document-body td {
        border: 1px solid #cbd5e1;
        padding: 8px;
    }

    .document-body h1,
    .document-body h2,
    .document-body h3 {
        font-weight: bold;
        margin-top: 15px;
        margin-bottom: 10px;
    }

    .document-body ul {
        list-style-type: disc;
        padding-left: 30px;
    }

    .document-body ol {
        list-style-type: decimal;
        padding-left: 30px;
    }

    .document-body blockquote {
        margin: 15px 0;
        padding-left: 15px;
        border-left: 3px solid #ccc;
        font-style: italic;
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
    // =========================================
    // 1. KOMPONEN ALPINE.JS
    // =========================================

    function wordDocumentEditor() {
        return {

            documentId: @js($document->id),

            // Semua TTD berasal dari database
            signatures: @js($signatures ?? []),

            // TTD yang sedang dipakai dokumen
            selectedSignature: @js(
                $document->signature_data['signatureUrl'] ?? null
            ),

            selectedSignatureId: @js(
                $document->signature_data['signatureId'] ?? null
            ),

            signatureX: @js(
                $document->signature_data['signatureX'] ?? 500
            ),

            signatureY: @js(
                $document->signature_data['signatureY'] ?? 650
            ),

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

            // =========================================
            // PAGES
            // =========================================

            pages: (
                @js(
                    $document->body_content['pages']
                    ?? [$document->body_content['content'] ?? '']
                )
            ).map((html, i) => ({
                uid: 'page-' + i,
                html
            })),

            pageSeq: (
                @js(
                    $document->body_content['pages']
                    ?? [$document->body_content['content'] ?? '']
                )
            ).length,

            // =========================================
            // INIT
            // =========================================

            init() {
                this.$nextTick(() => {
                    this.pages.forEach((page) => {
                        this.initPageEditor(
                            page.uid,
                            page.html
                        );
                    });
                });

                document.addEventListener('keydown', (e) => {

                    if (
                        (e.ctrlKey || e.metaKey) &&
                        e.key.toLowerCase() === 's'
                    ) {
                        e.preventDefault();
                        this.saveDocument();
                    }

                });
            },

            // =========================================
            // INIT EDITOR
            // =========================================

            initPageEditor(uid, content) {

                if (typeof window.initBodyEditor !== 'function') {
                    return;
                }

                window.initBodyEditor(
                    '#document-editor-' + uid,
                    content,
                    (html) => {

                        const page = this.pages.find(
                            (p) => p.uid === uid
                        );

                        if (page) {
                            page.html = html;
                        }

                        this.markAsChanged();
                    }
                );
            },

            // =========================================
            // CHANGE STATUS
            // =========================================

            markAsChanged() {
                this.changed = true;
                this.saveStatus = 'idle';
            },

            // =========================================
            // ADD PAGE
            // =========================================

            addPage() {

                const uid = 'page-' + (this.pageSeq++);

                this.pages.push({
                    uid,
                    html: ''
                });

                this.markAsChanged();

                this.$nextTick(() => {

                    this.initPageEditor(uid, '');

                    document
                        .getElementById(
                            'document-editor-' + uid
                        )
                        ?.scrollIntoView({
                            behavior: 'smooth',
                            block: 'center'
                        });

                });
            },

            // =========================================
            // REMOVE PAGE
            // =========================================

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

                window.tinymce
                    ?.get(
                        'document-editor-' +
                        this.pages[index].uid
                    )
                    ?.remove();

                this.pages.splice(index, 1);

                this.markAsChanged();
            },

            // =========================================
            // DRAG SIGNATURE
            // =========================================

            startDragSignature(event) {

                event.preventDefault();

                const page =
                    event.currentTarget.closest(
                        '.document-page'
                    );

                if (!page) {
                    return;
                }

                const pageRect =
                    page.getBoundingClientRect();

                const rect =
                    event.currentTarget.getBoundingClientRect();

                this.isDraggingSignature = true;

                this.dragStartX = event.clientX;
                this.dragStartY = event.clientY;

                this.initialSignatureX =
                    this.signatureX;

                this.initialSignatureY =
                    this.signatureY;

                this.dragOffsetX =
                    event.clientX - rect.left;

                this.dragOffsetY =
                    event.clientY - rect.top;

                this.signaturePageRect =
                    pageRect;

                document.body.style.userSelect =
                    'none';
            },

            dragSignature(event) {

                if (!this.isDraggingSignature) {
                    return;
                }

                const pageRect =
                    this.signaturePageRect ||
                    document
                        .querySelector('.document-page')
                        ?.getBoundingClientRect();

                if (!pageRect) {
                    return;
                }

                const nextX =
                    event.clientX -
                    pageRect.left -
                    this.dragOffsetX;

                const nextY =
                    event.clientY -
                    pageRect.top -
                    this.dragOffsetY;

                const minX = 20;

                const maxX = Math.max(
                    minX,
                    pageRect.width - 200
                );

                const minY = 140;

                const maxY = Math.max(
                    minY,
                    pageRect.height - 140
                );

                this.signatureX = Math.min(
                    Math.max(nextX, minX),
                    maxX
                );

                this.signatureY = Math.min(
                    Math.max(nextY, minY),
                    maxY
                );

                this.markAsChanged();
            },

            stopDragSignature() {

                if (!this.isDraggingSignature) {
                    return;
                }

                this.isDraggingSignature = false;
                this.signaturePageRect = null;

                document.body.style.userSelect = '';

                this.markAsChanged();
            },

            // =========================================
            // KEYDOWN
            // =========================================

            handleKeydown(event) {

                if (
                    (event.ctrlKey || event.metaKey) &&
                    event.key.toLowerCase() === 's'
                ) {
                    event.preventDefault();
                    this.saveDocument();
                }
            },

            // =========================================
            // SAVE DOCUMENT
            // =========================================

            async saveDocument() {

                this.saveStatus = 'saving';

                const pagesHtml =
                    this.pages.map((page) => {

                        const editor =
                            window.tinymce?.get(
                                'document-editor-' +
                                page.uid
                            );

                        return editor
                            ? editor.getContent()
                            : (page.html || '');
                    });

                const payload = {

                    title: @js($document->title),

                    type: @js(
                        $document->type ?? 'surat'
                    ),

                    header_data: @js(
                        $document->header_data ?? []
                    ),

                    body_content: {
                        pages: pagesHtml
                    },

                    footer_data: @js(
                        $document->footer_data ?? []
                    ),

                    // Hanya simpan ID TTD database
                    // + URL untuk kebutuhan render
                    // + posisi TTD
                    signature_data: {

                        signatureId:
                            this.selectedSignatureId,

                        signatureUrl:
                            this.selectedSignature,

                        signatureX:
                            this.signatureX,

                        signatureY:
                            this.signatureY
                    },

                    status: this.selectedSignature
                        ? 'draft'
                        : 'pending'
                };

                try {

                    await window.axios.put(
                        `/documents/${this.documentId}`,
                        payload
                    );

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

                const pagesHtml =
                    this.pages.map((page) => {

                        const editor =
                            window.tinymce?.get(
                                'document-editor-' +
                                page.uid
                            );

                        return editor
                            ? editor.getContent()
                            : (page.html || '');
                    });

                const payload = {

                    title: newTitle,

                    type: @js(
                        $document->type ?? 'surat'
                    ),

                    header_data: @js(
                        $document->header_data ?? []
                    ),

                    body_content: {
                        pages: pagesHtml
                    },

                    footer_data: @js(
                        $document->footer_data ?? []
                    ),

                    signature_data: {

                        signatureId:
                            this.selectedSignatureId,

                        signatureUrl:
                            this.selectedSignature,

                        signatureX:
                            this.signatureX,

                        signatureY:
                            this.signatureY
                    }
                };

                const res =
                    await window.axios.post(
                        '/documents/save-as',
                        payload
                    );

                return res.data.id;
            }
        };
    }


    // =========================================
    // 2. PROTEKSI UNSAVED CHANGES
    // =========================================

    window.hasUnsavedChanges = false;

    window.addEventListener(
        'beforeunload',
        function (e) {

            if (window.hasUnsavedChanges) {
                e.preventDefault();
                e.returnValue = '';
            }

        }
    );


    // =========================================
    // 3. HANDLE LINK NAVIGATION
    // =========================================

    document.addEventListener(
        'DOMContentLoaded',
        function () {

            const editorRoot =
                document.querySelector(
                    '[x-data="wordDocumentEditor()"]'
                );

            if (!editorRoot) {
                return;
            }

            Alpine.effect(() => {

                const data =
                    Alpine.$data(editorRoot);

                window.hasUnsavedChanges =
                    data.changed;
            });

            document.addEventListener(
                'click',
                function (e) {

                    const link =
                        e.target.closest('a[href]');

                    if (!link) {
                        return;
                    }

                    if (!window.hasUnsavedChanges) {
                        return;
                    }

                    const href =
                        link.getAttribute('href');

                    if (
                        !href ||
                        href.startsWith('#') ||
                        link.target === '_blank'
                    ) {
                        return;
                    }

                    e.preventDefault();

                    const destinationUrl =
                        link.href;

                    Swal.fire({

                        icon: 'warning',

                        title:
                            'Perubahan belum disimpan',

                        text:
                            'Kamu punya perubahan yang belum disimpan. Simpan dulu sebelum keluar?',

                        showDenyButton: true,
                        showCancelButton: true,

                        confirmButtonText:
                            'Simpan & Keluar',

                        denyButtonText:
                            'Buang Perubahan',

                        cancelButtonText:
                            'Batal',

                        confirmButtonColor:
                            '#1B2A4A',

                        denyButtonColor:
                            '#dc2626',

                        footer:
                            '<a href="#" id="swal-save-as-link" class="text-xs">' +
                            'atau Simpan Sebagai Dokumen Baru' +
                            '</a>',

                        didOpen: () => {

                            document
                                .getElementById(
                                    'swal-save-as-link'
                                )
                                .addEventListener(
                                    'click',
                                    async (evt) => {

                                        evt.preventDefault();

                                        Swal.close();

                                        const {
                                            value: newTitle
                                        } =
                                            await Swal.fire({

                                                title:
                                                    'Simpan Sebagai',

                                                input:
                                                    'text',

                                                inputLabel:
                                                    'Judul dokumen baru',

                                                inputValue:
                                                    @js(
                                                        ($document->title ?? 'Dokumen') .
                                                        ' (Salinan)'
                                                    ),

                                                showCancelButton:
                                                    true,

                                                confirmButtonText:
                                                    'Simpan Sebagai Baru',

                                                cancelButtonText:
                                                    'Batal',

                                                confirmButtonColor:
                                                    '#1B2A4A'
                                            });

                                        if (!newTitle) {
                                            return;
                                        }

                                        try {

                                            const data =
                                                Alpine.$data(
                                                    editorRoot
                                                );

                                            const newId =
                                                await data.saveAsNewDocument(
                                                    newTitle
                                                );

                                            window.hasUnsavedChanges =
                                                false;

                                            window.location.href =
                                                '/documents/' +
                                                newId +
                                                '/edit';

                                        } catch (err) {

                                            Swal.fire({
                                                icon: 'error',
                                                title: 'Gagal',
                                                text: 'Gagal menyimpan sebagai dokumen baru.',
                                                confirmButtonColor:
                                                    '#1B2A4A'
                                            });

                                            console.error(err);
                                        }
                                    }
                                );
                        }

                    }).then(async (result) => {

                        // SIMPAN & KELUAR
                        if (result.isConfirmed) {

                            try {

                                const data =
                                    Alpine.$data(
                                        editorRoot
                                    );

                                await data.saveDocument();

                                window.hasUnsavedChanges =
                                    false;

                                window.location.href =
                                    destinationUrl;

                            } catch (err) {

                                Swal.fire({
                                    icon: 'error',
                                    title: 'Gagal',
                                    text: 'Gagal menyimpan dokumen.',
                                    confirmButtonColor:
                                        '#1B2A4A'
                                });

                                console.error(err);
                            }

                        // BUANG PERUBAHAN
                        } else if (result.isDenied) {

                            window.hasUnsavedChanges =
                                false;

                            window.location.href =
                                destinationUrl;
                        }
                    });
                }
            );
        }
    );
</script>
@endpush

@endsection