@extends('layouts.app')

@section('content')

<div x-data="wordDocumentEditor()" class="min-h-screen">

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

                {{-- CHIP SESI EDIT HEADER/FOOTER --}}
                <span x-show="editSection" x-cloak
                    class="flex items-center gap-2 rounded-full bg-amber-100 px-3 py-1.5 text-xs font-semibold text-amber-800 dark:bg-amber-500/20 dark:text-amber-200">
                    <span x-text="editSection === 'header' ? '✏️ Mengedit Header' : '✏️ Mengedit Footer'"></span>
                    <button type="button" @click="exitEditSection()"
                        class="rounded-full bg-amber-800 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide text-white hover:bg-amber-900">
                        Tutup
                    </button>
                </span>

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

    {{-- QUILL TOOLBAR (satu toolbar bersama untuk semua region) --}}
    <div id="body-toolbar-container"
        class="sticky top-[57px] z-30 flex flex-wrap items-center gap-1.5 border-b border-parchment-300 bg-white/95 px-4 py-2.5 shadow-sm backdrop-blur dark:border-slate-warm-700 dark:bg-slate-warm-900/95 print:hidden">

        <button type="button" class="toolbar-button" data-cmd="undo" title="Urungkan"><b>↺</b></button>
        <button type="button" class="toolbar-button" data-cmd="redo" title="Ulangi"><b>↻</b></button>

        <span class="toolbar-divider"></span>

        <select id="tb-block" class="toolbar-select" title="Gaya paragraf">
            <option value="">Paragraf</option>
            <option value="1">Judul 1</option>
            <option value="2">Judul 2</option>
            <option value="3">Judul 3</option>
        </select>

        <select id="tb-font" class="toolbar-select" title="Jenis font">
            <option value="">Font</option>
            <option value="Arial">Arial</option>
            <option value="Georgia">Georgia</option>
            <option value="Times New Roman">Times New Roman</option>
            <option value="Courier New">Courier New</option>
            <option value="Verdana">Verdana</option>
        </select>

        <select id="tb-size" class="toolbar-select" title="Ukuran font">
            <option value="">Ukuran</option>
            <option value="10px">10</option>
            <option value="12px">12</option>
            <option value="14px">14</option>
            <option value="16px">16</option>
            <option value="18px">18</option>
            <option value="20px">20</option>
            <option value="24px">24</option>
            <option value="28px">28</option>
            <option value="32px">32</option>
        </select>

        <div class="toolbar-size-arrows" title="Perbesar/perkecil font">
            <button type="button" class="toolbar-size-arrow" data-size-step="1" title="Perbesar font">▲</button>
            <button type="button" class="toolbar-size-arrow" data-size-step="-1" title="Perkecil font">▼</button>
        </div>

        <select id="tb-lineheight" class="toolbar-select" title="Spasi baris">
            <option value="">Spasi</option>
            <option value="1">1.0</option>
            <option value="1.15">1.15</option>
            <option value="1.5">1.5</option>
            <option value="2">2.0</option>
            <option value="2.5">2.5</option>
        </select>

        <span class="toolbar-divider"></span>

        <span class="toolbar-divider"></span>

        <button type="button" class="toolbar-button font-bold" data-cmd="bold" title="Tebal">B</button>
        <button type="button" class="toolbar-button italic" data-cmd="italic" title="Miring">I</button>
        <button type="button" class="toolbar-button underline" data-cmd="underline" title="Garis bawah">U</button>
        <button type="button" class="toolbar-button line-through" data-cmd="strike" title="Coret">S</button>

        <span class="toolbar-divider"></span>

        <input type="color" id="tb-color" class="toolbar-color" value="#111827" title="Warna teks (klik kanan = hapus)">
        <input type="color" id="tb-bgcolor" class="toolbar-color" value="#ffffff"
            title="Warna stabilo (klik kanan = hapus)">

        <span class="toolbar-divider"></span>

        <button type="button" class="toolbar-button" data-align="left" title="Rata kiri"><b>⯇</b></button>
        <button type="button" class="toolbar-button" data-align="center" title="Rata tengah"><b>≡</b></button>
        <button type="button" class="toolbar-button" data-align="right" title="Rata kanan"><b>⯈</b></button>
        <button type="button" class="toolbar-button" data-align="justify" title="Rata kiri-kanan"><b>☰</b></button>

        <span class="toolbar-divider"></span>

        <button type="button" class="toolbar-button" data-cmd="outdent" title="Kurangi indentasi"><b>⇤</b></button>
        <button type="button" class="toolbar-button" data-cmd="indent" title="Tambah indentasi"><b>⇥</b></button>
        <button type="button" class="toolbar-button" data-cmd="bullist" title="Daftar poin"><b>•</b></button>
        <button type="button" class="toolbar-button" data-cmd="numlist" title="Daftar nomor"><b>1.</b></button>
        <button type="button" class="toolbar-button" data-cmd="alphalist" title="Daftar huruf"><b>a.</b></button>

        <span class="toolbar-divider"></span>

        <button type="button" class="toolbar-button" data-cmd="superscript" title="Superscript"><b>A²</b></button>
        <button type="button" class="toolbar-button" data-cmd="subscript" title="Subscript"><b>A₂</b></button>

        <span class="toolbar-divider"></span>

        <button type="button" class="toolbar-button" data-cmd="link" title="Sisipkan link"><b>🔗</b></button>
        <button type="button" class="toolbar-button" data-cmd="image" title="Sisipkan gambar"><b>🖼</b></button>
        <button type="button" class="toolbar-button" data-cmd="hr" title="Garis pembatas kop"><b>⎯</b></button>
        <button type="button" class="toolbar-button" data-cmd="removeformat" title="Hapus format"><b>⌫</b></button>

        <span class="toolbar-divider"></span>

        {{-- TOOL TABEL: dropdown grid picker + aksi baris/kolom --}}
        <div class="toolbar-dropdown" id="tb-table-dd">
            <button type="button" class="toolbar-button toolbar-dropdown-toggle" data-cmd="table"
                title="Sisipkan / kelola tabel">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-table" viewBox="0 0 16 16">
                    <path d="M0 2a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2zm15 2h-4v3h4zm0 4h-4v3h4zm0 4h-4v3h3a1 1 0 0 0 1-1zm-5 3v-3H6v3zm-5 0v-3H1v2a1 1 0 0 0 1 1zm-4-4h4V8H1zm0-4h4V4H1zm5-3v3h4V4zm4 4H6v3h4z"/>
                </svg>
            </button>

            <div class="toolbar-dropdown-menu">
                <div class="table-grid-picker" role="grid"></div>
                <div class="table-grid-label">Sisipkan tabel</div>

                <div class="table-dd-divider"></div>

                <button type="button" class="table-dd-action" data-table-action="insert-row">+ Sisip baris di bawah</button>
                <button type="button" class="table-dd-action" data-table-action="insert-column">+ Sisip kolom di kanan</button>
                <button type="button" class="table-dd-action table-dd-danger" data-table-action="delete-row">− Hapus baris</button>
                <button type="button" class="table-dd-action table-dd-danger" data-table-action="delete-column">− Hapus kolom</button>
                <button type="button" class="table-dd-action table-dd-danger" data-table-action="delete-table">✕ Hapus tabel</button>
            </div>
        </div>
    </div>

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
        border-radius: 8px;
        font-size: 14px;
        color: #44403c;
        cursor: pointer;
        transition: background-color 0.15s, box-shadow 0.15s, color 0.15s;
    }

    .toolbar-button:hover {
        background: rgb(240 236 227);
    }

    .toolbar-button:active {
        background: rgb(231 224 210);
    }

    .toolbar-button.active {
        background: #1B2A4A;
        color: #fff;
        box-shadow: inset 0 0 0 1px rgba(255, 255, 255, 0.15);
    }

    .dark .toolbar-button.active {
        background: rgb(180 140 80);
        color: #1c1917;
    }

    .toolbar-color {
        width: 30px;
        height: 30px;
        padding: 2px;
        border: 1px solid rgb(214 211 204);
        border-radius: 8px;
        cursor: pointer;
        background: white;
        transition: border-color 0.15s, box-shadow 0.15s;
    }

    .toolbar-color:hover {
        border-color: rgb(180 140 80);
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
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
        height: 22px;
        margin: 0 6px;
        background: rgb(214 211 204);
        border-radius: 1px;
    }

    .toolbar-select {
        height: 34px;
        min-width: 130px;
        padding: 0 10px;
        border: 1px solid rgb(214 211 204);
        border-radius: 8px;
        background: white;
        font-size: 13px;
        color: #292524;
        cursor: pointer;
        transition: border-color 0.15s, box-shadow 0.15s;
    }

    .toolbar-select:hover {
        border-color: rgb(180 140 80);
    }

    .toolbar-select:focus {
        outline: none;
        border-color: rgb(180 140 80);
        box-shadow: 0 0 0 3px rgba(180, 140, 80, 0.15);
    }

    .toolbar-size-arrows {
        display: flex;
        flex-direction: column;
        height: 34px;
        width: 22px;
        border: 1px solid rgb(214 211 204);
        border-radius: 8px;
        overflow: hidden;
    }

    .toolbar-size-arrow {
        flex: 1;
        font-size: 8px;
        line-height: 1;
        color: #78716c;
        cursor: pointer;
        background: white;
    }

    .toolbar-size-arrow:hover {
        background: rgb(240 236 227);
    }

    .toolbar-size-arrow:first-child {
        border-bottom: 1px solid rgb(214 211 204);
    }

    @media (max-width: 768px) {
        .toolbar-select {
            min-width: 100px;
        }
    }

    .doc-editable-body img,
    .doc-editable-header img,
    .doc-editable-footer img {
        cursor: pointer;
    }

    /* ─── TOOL TABEL: dropdown + grid picker ─── */
    .toolbar-dropdown {
        position: relative;
        display: inline-flex;
    }

    .toolbar-dropdown-toggle {
        width: auto;
        min-width: 74px;
        padding: 0 10px;
        gap: 4px;
        font-size: 13px;
        font-weight: 600;
    }

    .toolbar-dropdown-menu {
        display: none;
        position: absolute;
        top: calc(100% + 6px);
        left: 0;
        z-index: 60;
        min-width: 200px;
        padding: 10px;
        background: white;
        border: 1px solid rgb(214 211 204);
        border-radius: 12px;
        box-shadow: 0 12px 30px rgba(0, 0, 0, 0.14);
    }

    .toolbar-dropdown-menu.open {
        display: block;
    }

    .dark .toolbar-dropdown-menu {
        background: rgb(28 25 23);
        border-color: rgb(68 64 60);
    }

    .table-grid-picker {
        display: grid;
        grid-template-columns: repeat(10, 16px);
        gap: 2px;
    }

    .table-grid-cell {
        width: 16px;
        height: 16px;
        padding: 0;
        border: 1px solid rgb(214 211 204);
        background: white;
        cursor: pointer;
    }

    .table-grid-cell.hovered {
        background: #1B2A4A;
        border-color: #1B2A4A;
    }

    .dark .table-grid-cell {
        background: rgb(41 37 36);
        border-color: rgb(87 83 78);
    }

    .dark .table-grid-cell.hovered {
        background: rgb(180 140 80);
        border-color: rgb(180 140 80);
    }

    .table-grid-label {
        margin-top: 8px;
        font-size: 11px;
        color: rgb(120 113 108);
        text-align: center;
    }

    .dark .table-grid-label {
        color: rgb(168 162 158);
    }

    .table-dd-divider {
        height: 1px;
        margin: 8px 0;
        background: rgb(214 211 204);
    }

    .dark .table-dd-divider {
        background: rgb(68 64 60);
    }

    .table-dd-action {
        display: block;
        width: 100%;
        padding: 7px 10px;
        border: 0;
        border-radius: 8px;
        background: transparent;
        text-align: left;
        font-size: 13px;
        color: #292524;
        cursor: pointer;
    }

    .table-dd-action:hover {
        background: rgb(240 236 227);
    }

    .dark .table-dd-action {
        color: rgb(231 229 228);
    }

    .dark .table-dd-action:hover {
        background: rgb(41 37 36);
    }

    .table-dd-action.disabled {
        opacity: 0.4;
        pointer-events: none;
    }

    .table-dd-danger {
        color: #b91c1c;
    }

    .dark .table-dd-danger {
        color: #fca5a5;
    }

    /* ─── TABEL DI DALAM EDITOR ─── */
    .ql-editor table {
        border-collapse: collapse;
        table-layout: fixed;
        width: 100%;
        margin: 8px 0;
    }

    .ql-editor td {
        border: 1px solid rgb(120 113 108);
        padding: 5px 8px;
        vertical-align: top;
        position: relative;
    }

    .ql-editor td.selected-cell {
        background: rgba(27, 42, 74, 0.12);
    }

    .dark .ql-editor td.selected-cell,
    .ql-editor td.selected-cell.selected-cell {
        background: rgba(180, 140, 80, 0.25);
    }

    /*KANVAS EDITOR (SATU INSTANCE)*/
    .document-editor-canvas {
        display: flex;
        flex-direction: column;
        gap: 24px;
    }

    /* Setiap "kertas" di dalam editor */
    .doc-sheet {
        position: relative;
        width: 210mm;
        height: 297mm;               /* KERTAS TETAP: tidak memanjang lagi */
        min-height: 297mm;
        margin: 0 auto;
        box-sizing: border-box;
        overflow: visible;
        background: white;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
        padding: 20mm 20mm;
        font-family: Arial, sans-serif;
        font-size: 14px;
        line-height: 1.5;
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
        overflow: hidden;            /* konten yang meluap DIKELUARKAN otomatis
                                        ke kertas berikutnya oleh paginasi */
    }

    .doc-sheet-footer {
        position: relative;
        min-height: 40px;
        padding-top: 8px;
    }

    /* Tombol hapus halaman (muncul di semua kertas, kecuali kertas pertama) */
    .page-remove-btn {
        position: absolute;
        top: 10px;
        right: 10px;
        z-index: 30;
        width: 26px;
        height: 26px;
        line-height: 1;
        border: 1px solid rgb(220 38 38 / 0.4);
        border-radius: 9999px;
        background: rgba(255, 255, 255, 0.92);
        color: #dc2626;
        font-size: 13px;
        font-weight: 700;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: background 0.15s ease, color 0.15s ease;
        opacity: 0;
    }
    .doc-sheet:hover .page-remove-btn {
        opacity: 1;
    }
    .page-remove-btn:hover {
        background: #dc2626;
        color: #fff;
    }

    /* Jadikan tiap region stacking context, agar gambar dengan z-index negatif
       (mode "Di Belakang Teks") tetap tampil di atas kertas putih
       tapi berada di bawah teks. */
    .doc-sheet-header,
    .doc-sheet-body,
    .doc-sheet-footer {
        position: relative;
        z-index: 0;
    }

    .doc-sheet img.doc-image-front,
    .doc-sheet img.doc-image-behind {
        cursor: grab !important;
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

    .doc-sheet .ql-editor li[data-list=ordered].ql-indent-1>.ql-ui:before {
        content: counter(list-1, decimal) '. ';
    }

    .doc-sheet .ql-editor li[data-list=ordered].ql-indent-2>.ql-ui:before {
        content: counter(list-2, decimal) '. ';
    }

    .doc-sheet .ql-editor li[data-list=ordered].ql-indent-4>.ql-ui:before {
        content: counter(list-4, decimal) '. ';
    }

    .doc-sheet .ql-editor li[data-list=ordered].ql-indent-5>.ql-ui:before {
        content: counter(list-5, decimal) '. ';
    }

    .doc-sheet .ql-editor li[data-list=ordered].ql-indent-7>.ql-ui:before {
        content: counter(list-7, decimal) '. ';
    }

    .doc-sheet .ql-editor li[data-list=ordered].ql-indent-8>.ql-ui:before {
        content: counter(list-8, decimal) '. ';
    }

    .doc-sheet .ql-editor li[data-list=ordered].ql-liststyle-alpha>.ql-ui:before {
        content: counter(list-0, lower-alpha) '. ';
    }

    .doc-sheet .ql-editor li[data-list=ordered].ql-liststyle-alpha.ql-indent-1>.ql-ui:before {
        content: counter(list-1, lower-alpha) '. ';
    }

    .doc-sheet .ql-editor li[data-list=ordered].ql-liststyle-alpha.ql-indent-2>.ql-ui:before {
        content: counter(list-2, lower-alpha) '. ';
    }

    .doc-sheet .ql-editor li[data-list=ordered].ql-liststyle-alpha.ql-indent-3>.ql-ui:before {
        content: counter(list-3, lower-alpha) '. ';
    }



    /* Garis pemisah antar kertas (hanya di layar) */
    .doc-sheet+.doc-sheet {
        margin-top: 24px;
    }

    /* Toolbar Quill: tombol aktif diberi latar lembut */
    .toolbar-button.active {
        background: #e7e0d2;
    }

    .dark .toolbar-button.active {
        background: #3b3428;
    }

    /* Editor Quill menyatu dengan gaya kertas */
    .doc-sheet .ql-container {
        border: none !important;
        font-family: inherit;
        font-size: 14px;
        height: auto !important;
        /* jangan kunci 100% — biarkan ikut aliran flex */
    }

    .doc-sheet .ql-editor {
        padding: 0;
        min-height: 40px;
        height: auto !important;
        overflow: visible !important;
        font-family: inherit;
        font-size: 14px;
        line-height: 1.5;
        color: #111827;
    }

    .doc-sheet .doc-sheet-body .ql-container {
        height: 100% !important;
    }

    .doc-sheet .doc-sheet-body .ql-editor {
        min-height: 100%;
        height: auto !important;
    }

    .doc-sheet .ql-editor p {
        margin: 0 0 10px 0;
    }

    .doc-sheet .ql-editor img {
        cursor: pointer !important;
        max-width: 100%;
    }

    /* Sembunyikan chrome bawaan tema snow yang tidak dipakai */
    .doc-sheet .ql-clipboard {
        position: absolute;
        left: -9999px;
    }

    /*
        HEADER/FOOTER ALA WORD
       - zona default INERT + hint saat hover
       - sesi edit: area lain redup & terkunci*/
    .doc-sheet-header,
    .doc-sheet-footer {
        cursor: default;
    }

    .doc-sheet-header::after,
    .doc-sheet-footer::after {
        position: absolute;
        right: 0;
        z-index: 5;
        font-size: 10px;
        font-weight: 600;
        letter-spacing: 0.02em;
        color: #a8a29e;
        background: rgba(255, 255, 255, 0.88);
        border: 1px dashed #d6d3cc;
        border-radius: 999px;
        padding: 2px 10px;
        opacity: 0;
        transition: opacity 0.15s;
        pointer-events: none;
        white-space: nowrap;
    }

    .doc-sheet-header::after {
        content: 'Klik dua kali untuk mengedit Header';
        top: -14px;
    }

    .doc-sheet-footer::after {
        content: 'Klik dua kali untuk mengedit Footer';
        bottom: -14px;
    }

    .dark .doc-sheet-header::after,
    .dark .doc-sheet-footer::after {
        background: rgba(15, 23, 42, 0.85);
        border-color: #57534e;
    }

    .doc-sheet-header:hover::after,
    .doc-sheet-footer:hover::after {
        opacity: 1;
    }

    /* Redupkan area lain selama sesi edit aktif.
       MURNI VISUAL — tidak memblokir interaksi:
       satu klik pada isi dokumen langsung mengakhiri sesi. */
    .editing-header .doc-sheet-body,
    .editing-header .doc-sheet-footer,
    .editing-footer .doc-sheet-body,
    .editing-footer .doc-sheet-header {
        opacity: 0.35;
        transition: opacity 0.2s;
    }

    /* Petunjuk saat menyentuh area yang terkunci */
    .editing-header .doc-sheet-body:hover::after,
    .editing-header .doc-sheet-footer:hover::after,
    .editing-footer .doc-sheet-body:hover::after,
    .editing-footer .doc-sheet-header:hover::after {
        opacity: 1;
    }

    .editing-header .doc-sheet-body::after,
    .editing-footer .doc-sheet-body::after {
        content: 'Klik di sini untuk kembali ke dokumen';
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        z-index: 30;
        font-size: 12px;
        font-weight: 600;
        color: #78716c;
        background: rgba(255, 255, 255, 0.92);
        border: 1px dashed #d6d3cc;
        border-radius: 999px;
        padding: 6px 16px;
        white-space: nowrap;
        pointer-events: none;
        opacity: 0;
        transition: opacity 0.15s;
    }

    .dark .editing-header .doc-sheet-body::after,
    .dark .editing-footer .doc-sheet-body::after {
        color: #a8a29e;
        background: rgba(15, 23, 42, 0.88);
        border-color: #57534e;
    }

    .editing-header .doc-sheet-body:hover::after,
    .editing-footer .doc-sheet-body:hover::after {
        opacity: 1;
    }

    /* Garis pembatas ala Word: pemisah zona aktif vs konten utama.
       Dipasang via .zone-editing (langsung di elemen zona) DAN
       .editing-* (induk) sebagai lapisan ganda. */
    .editing-header .doc-sheet-header,
    .doc-sheet-header.zone-editing {
        border-bottom: 1px dashed rgb(180 140 80);
    }

    .editing-footer .doc-sheet-footer,
    .doc-sheet-footer.zone-editing {
        border-top: 1px dashed rgb(180 140 80);
    }

    @media print {

        .doc-sheet-header::after,
        .doc-sheet-footer::after {
            display: none !important;
        }

        /* Garis pembatas hanya milik layar */
        .editing-header .doc-sheet-header,
        .editing-footer .doc-sheet-footer,
        .doc-sheet-header.zone-editing,
        .doc-sheet-footer.zone-editing {
            border-color: transparent !important;
        }

        .editing-header .doc-sheet-body,
        .editing-header .doc-sheet-footer,
        .editing-footer .doc-sheet-body,
        .editing-footer .doc-sheet-header {
            opacity: 1 !important;
        }

        .editing-header .doc-sheet-body::before,
        .editing-header .doc-sheet-footer::before,
        .editing-footer .doc-sheet-body::before,
        .editing-footer .doc-sheet-header::before {
            display: none !important;
        }
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
    /* Contract table cell styling — specificity lebih tinggi (.ql-editor table td)
       dibanding .ql-editor th quill-table-better (0,2,1 vs 0,1,1) agar tetap
       menang walau CSS quill-table-better dimuat setelah tag <style> ini. */
    .ql-editor table {
        border-collapse: collapse;
        width: 100%;
        margin: 0.5rem 0;
    }

    /* Tabel kontrak: layout kolom tetap (fixed). Lebar % eksplisit per sel
       (diset di server & preprocessor) membuat fragmen tabel yang terpotong
       halaman tetap memiliki grid kolom yang segaris, seperti di Word. */
    .ql-editor table[data-class*="contract-table"] {
        table-layout: fixed;
        /* Jaring pengaman: quill-table-better bisa menghilangkan style
           table-level saat konversi — tanpa ini tabel menyusut & kolom
           berantakan. */
        width: 100% !important;
    }

    .ql-editor table td,
    .ql-editor table th {
        border: 1px solid #000;
        padding: 4px 6px;
        vertical-align: top;
    }

    /* Header <th> cell: latar belakang abu-abu lembut, rata-tengah, tebalkan */
    .ql-editor table th {
        background: rgba(0, 0, 0, 0.03);
        font-weight: 600;
        text-align: center;
    }

    /* Override: tabel border pakai warna proyek (#374151) */
    .ql-editor table[data-class*="contract-table-bordered"] td,
    .ql-editor table[data-class*="contract-table-bordered"] th {
        border: 1px solid #374151;
    }

    /* Override: tabel tanpa border (mis. tabel tanda tangan) */
    .ql-editor table[data-class*="contract-table-unstyled"] td,
    .ql-editor table[data-class*="contract-table-unstyled"] th {
        border: none;
        background: transparent;
    }

    /* Print: pastikan border & background tetap terlihat */
    @media print {
        .ql-editor table td,
        .ql-editor table th {
            border: 1px solid #000 !important;
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

            // Sesi edit header/footer ala Word ('header' | 'footer' | null)
            editSection: null,

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

            // Riwayat UNDO/REDO tingkat dokumen (struktur halaman).
            // Dipakai supaya tombol Undo bisa memulihkan halaman yang dihapus.
            undoStack: [],
            redoStack: [],

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

            // Penanda halaman sampul (cover) bawaan template: N halaman
            // pertama dikunci dari paginasi BALIK, supaya isi kontrak tidak
            // ditarik naik ke dalam sampul (dulu bikin urutan pasal berantakan).
            coverPages: @js($document -> body_content['coverPages'] ?? 0),

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

                    // Deteksi aset JS usang: fitur zona butuh API DocQuill baru.
                    if (typeof window.DocQuill?.setZonesEnabled === 'function') {
                        this.initZoneEditMode();
                    } else {
                        console.error(
                            '[ZoneEdit] editor.js yang dimuat TERSOLETE ' +
                            '(DocQuill.setZonesEnabled hilang, versi: ' +
                            (window.DocQuill?.__version || 'tidak diketahui') + '). ' +
                            'Jalankan ulang "npm run dev", atau "npm run build", ' +
                            'lalu hard-refresh browser (Ctrl+F5).'
                        );
                    }

                    // Paginasi otomatis: kertas tetap 297mm, konten penuh
                    // mengalir ke kertas baru di bawahnya (aman di-skip
                    // kalau bundle editor.js belum dibangun ulang).
                    this.initAutoPagination();
                });

                // Bridge UNDO/REDO tingkat dokumen buat toolbar Quill.
                // Editor.js akan memanggil ini dulu; kalau ada snapshot struktur
                // halaman, dipakai (mis. memulihkan halaman yang dihapus).
                window.__docUndoBridge = {
                    undo: () => this.undoDocument(),
                    redo: () => this.redoDocument(),
                };

                // Delegasi klik tombol hapus halaman (muncul di semua kertas,
                // kecuali kertas pertama). Didelegasikan ke container supaya
                // tombol yang dibuat lewat buildSheet/addPage/createPageAfter
                // tetap berfungsi tanpa perlu listener per tombol.
                const editorRootEl = document.getElementById('document-editor');
                editorRootEl?.addEventListener('click', (e) => {
                    const btn = e.target.closest('.page-remove-btn');
                    if (!btn) return;
                    e.preventDefault();
                    e.stopPropagation();
                    const uid = btn.getAttribute('data-page-uid');
                    const index = this.pages.findIndex((p) => p.uid === uid);
                    if (index < 0) return;
                    this.removePage(index);
                });

                // CTRL + S
                document.addEventListener('keydown', (e) => {
                    if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 's') {
                        e.preventDefault();
                        this.saveDocument();
                    }
                });

                // Backspace pada halaman (selain halaman pertama) yang ISINYA KOSONG
                // = hapus halaman tersebut (tekan backspace lagi setelah isi habis).
                // Pakai phase CAPTURE supaya cek isi terjadi SEBELUM Quill menghapus
                // karakter pada tombol ini -> hapus isi butuh 1 tekkan, penghapusan
                // halaman butuh 1 tekkan berikutnya (isi sudah kosong duluan).
                document.addEventListener('keydown', (e) => {
                    if (e.key !== 'Backspace') return;

                    // Hanya berlaku saat mengedit isi utama (di luar sesi header/footer)
                    if (this.editSection) return;

                    const bodyRegion = e.target.closest?.('.doc-sheet-body');
                    if (!bodyRegion) return;

                    const sheet = bodyRegion.closest('.doc-sheet');
                    if (!sheet) return;

                    const uid = sheet.getAttribute('data-page-uid');
                    const index = this.pages.findIndex((p) => p.uid === uid);
                    if (index < 0) return;

                    // Halaman pertama (utama) TIDAK boleh dihapus
                    if (index === 0) return;

                    // Kosong = tidak ada teks sekaligus tidak ada gambar.
                    const kosong =
                        (bodyRegion.textContent || '').trim() === '' &&
                        bodyRegion.querySelectorAll('img').length === 0;

                    if (!kosong) return;

                    e.preventDefault();
                    e.stopPropagation();
                    this.removePageSilent(index);
                }, true); // capture
            },

            // INISIALISASI QUILL UNTUK SEMUA KERTAS
            initSingleEditor() {

                const editorEl = document.getElementById('document-editor');
                if (!editorEl) return;

                // Bangun konten awal: setiap kertas berisi header + body + footer
                let html = '';

                this.pages.forEach((page, i) => {
                    html += this.buildSheet(page, i);
                });

                editorEl.innerHTML = html;

                this.markLockedSheets();

                this.setupSignatureEvents();

                // Hook global "dokumen berubah" untuk sistem gambar
                window.__docEditorDirty = () => this.markAsChanged();

                // Pasang Quill pada setiap region kertas + toolbar bersama
                window.initBodyEditor('#document-editor', () => {
                    this.markAsChanged();
                });

                // Jaminan: isi dokumen selalu aktif saat halaman dibuka
                window.DocQuill.ensureBodyEditable?.();

                this.renderSignature();
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

            // TAMBAH HALAMAN (sheet baru di akhir, footer ikut pindah)
            addPage() {

                const uid = 'page-' + (this.pageSeq++);

                this.pages.push({
                    uid,
                    html: ''
                });

                const root = document.getElementById('document-editor');
                if (!root) return;

                // Sheet baru lengkap dengan zona header & footer (ala Word)
                const sheet = document.createElement('div');
                sheet.className = 'doc-sheet';
                sheet.setAttribute('data-sheet-type', 'page');
                sheet.setAttribute('data-page-uid', uid);

                const mkRegion = (cls, region, inner) => {
                    const el = document.createElement('div');
                    el.className = cls;
                    el.setAttribute('data-region', region);
                    el.innerHTML = inner;
                    return el;
                };

                // Zona header & footer baru = cermin konten halaman lain
                const firstHeader = root.querySelector('.doc-sheet-header[data-region="header"]');
                const firstFooter = root.querySelector('.doc-sheet-footer[data-region="footer"]');

                const headerRegion = mkRegion('doc-sheet-header', 'header',
                    firstHeader ? window.DocQuill.getHtml(firstHeader) : '<p></p>');
                const bodyRegion = mkRegion('doc-sheet-body', 'body', '<p></p>');
                const footerRegion = mkRegion('doc-sheet-footer', 'footer',
                    firstFooter ? window.DocQuill.getHtml(firstFooter) : '<p></p>');

                sheet.appendChild(headerRegion);
                sheet.appendChild(bodyRegion);
                sheet.appendChild(footerRegion);

                // Tombol hapus halaman (halaman baru selalu bukan yang pertama).
                const rmBtn = document.createElement('button');
                rmBtn.type = 'button';
                rmBtn.className = 'page-remove-btn print:hidden';
                rmBtn.setAttribute('data-page-uid', uid);
                rmBtn.title = 'Hapus halaman ini';
                rmBtn.textContent = '✕';
                sheet.appendChild(rmBtn);

                root.appendChild(sheet);

                // Pasang editor pada ketiga region baru
                window.DocQuill.attachRegion(headerRegion);
                window.DocQuill.attachRegion(bodyRegion);
                window.DocQuill.attachRegion(footerRegion);

                // Zona baru mengikuti status sesi edit yang sedang berjalan
                window.DocQuill.setZonesEnabled('header', this.editSection === 'header');
                window.DocQuill.setZonesEnabled('footer', this.editSection === 'footer');

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

                // Simpan state sebelum menghapus (untuk tombol Undo).
                this.pushHistory();

                const removedUid = this.pages[index].uid;

                const root = document.getElementById('document-editor');
                if (root) {
                    const sheet = root.querySelector('[data-page-uid="' + removedUid + '"]');
                    if (sheet) {
                        // Lepaskan instance editor zona milik halaman yang dihapus
                        sheet.querySelectorAll('.doc-sheet-header, .doc-sheet-body, .doc-sheet-footer')
                            .forEach((rg) => window.DocQuill.forgetRegion(rg));
                        sheet.remove();
                    }
                }

                this.pages.splice(index, 1);
                this.markAsChanged();
            },

            // HAPUS HALAMAN TANPA KONFIRMASI — dipakai saat halaman kosong di-backspace.
            // Menghindari dialog, lalu pindah caret ke halaman sebelum halaman yang dibuang.
            removePageSilent(index) {

                const removedUid = this.pages[index]?.uid;
                if (!removedUid) return;

                // Simpan state sebelum menghapus (untuk tombol Undo).
                this.pushHistory();

                const root = document.getElementById('document-editor');
                let prevBody = null;

                if (root) {
                    const sheet = root.querySelector('[data-page-uid="' + removedUid + '"]');
                    if (sheet) {
                        // Lepaskan instance editor zona milik halaman yang dihapus
                        sheet.querySelectorAll('.doc-sheet-header, .doc-sheet-body, .doc-sheet-footer')
                            .forEach((rg) => window.DocQuill.forgetRegion(rg));
                        sheet.remove();
                    }

                    // Sasar halaman baru terakhir (halaman sebelumnya)
                    const prev = this.pages[index - 1];
                    const prevSheet = prev && root.querySelector('[data-page-uid="' + prev.uid + '"]');
                    prevBody = prevSheet?.querySelector('.doc-sheet-body');
                    if (prevBody) prevBody.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }

                this.pages.splice(index, 1);
                this.markAsChanged();

                // Fokuskan caret di akhir halaman sebelumnya
                if (prevBody && typeof window.DocQuill?.focusBodyEnd === 'function') {
                    window.DocQuill.focusBodyEnd(prevBody);
                }
            },

            // Susun inner-HTML satu kertas (header + body + footer),
            // lengkap dengan tombol hapus (kecuali kertas pertama).
            buildSheet(page, index) {
                let html = '<div class="doc-sheet" data-sheet-type="page" data-page-uid="' + page.uid + '">';

                // Tombol hapus halaman: semua kertas KECUALI yang pertama.
                if (index > 0) {
                    html += '<button type="button" class="page-remove-btn print:hidden" ' +
                        'data-page-uid="' + page.uid + '" title="Hapus halaman ini">✕</button>';
                }

                // Header & footer di SEMUA halaman (ala Microsoft Word).
                // Semua zona dengan role sama berbagi SATU konten.
                html += '<div class="doc-sheet-header" data-region="header">';
                html += this.headerHtml || '<p></p>';
                html += '</div>';

                // Body
                html += '<div class="doc-sheet-body" data-region="body">';
                html += page.html || '<p></p>';
                html += '</div>';

                html += '<div class="doc-sheet-footer" data-region="footer">';
                html += this.footerHtml || '<p></p>';
                html += '</div>';

                html += '</div>';
                return html;
            },

            // Tandai N halaman pertama sebagai sampul template (data-flow-lock).
            // editor.js memakai atribut ini untuk melarang paginasi BALIK
            // menyeret isi halaman berikutnya ke dalam sampul.
            markLockedSheets() {
                const root = document.getElementById('document-editor');
                if (!root) return;
                const n = Number(this.coverPages) || 0;
                root.querySelectorAll('.doc-sheet').forEach((sheet, i) => {
                    if (i < n) sheet.setAttribute('data-flow-lock', 'cover');
                    else sheet.removeAttribute('data-flow-lock');
                });
            },

            // ------------- UNDO/REDO TINGKAT DOKUMEN -------------
            // Snapshot = salinan penuh state (halaman + header + footer).
            // Ini memungkinkan tombol Undo memulihkan halaman yang dihapus.

            snapshot() {
                return JSON.stringify({
                    pages: this.pages.map((p) => ({ uid: p.uid, html: p.html })),
                    headerHtml: this.headerHtml,
                    footerHtml: this.footerHtml,
                });
            },

            // Simpan state SAAT INI ke tumpukan undo (dipanggil SEBELUM
            // perubahan struktur halaman: tambah / hapus / pindah).
            pushHistory() {
                this.undoStack.push(this.snapshot());
                if (this.undoStack.length > 60) this.undoStack.shift();
                this.redoStack = [];
            },

            applySnapshot(s) {
                this.pages = s.pages;
                this.headerHtml = s.headerHtml || '<p></p>';
                this.footerHtml = s.footerHtml || '<p></p>';
                this.rebuildSheets();
            },

            // Bangun ulang seluruh DOM kertas dari this.pages + header/footer.
            // Aman dipanggil kapan saja (pasca undo/redo struktural): lepaskan
            // instance Quill lama, lalu pasang ulang untuk setiap region baru.
            rebuildSheets() {
                const editorEl = document.getElementById('document-editor');
                if (!editorEl) return;

                editorEl.querySelectorAll('.doc-sheet-header, .doc-sheet-body, .doc-sheet-footer')
                    .forEach((rg) => window.DocQuill.forgetRegion(rg));

                editorEl.innerHTML = this.pages
                    .map((p, i) => this.buildSheet(p, i))
                    .join('');

                this.markLockedSheets();

                editorEl.querySelectorAll('.doc-sheet-header, .doc-sheet-body, .doc-sheet-footer')
                    .forEach((rg) => window.DocQuill.attachRegion(rg));

                window.DocQuill.ensureBodyEditable?.();
                this.renderSignature();
                this.markAsChanged();
            },

            // Undo tingkat dokumen. Mengembalikan true kalau ada snapshot
            // struktur yang mau dipulihkan (supaya editor.js TIDAK meneruskan
            // ke undo teks Quill); false kalau tidak ada -> biar Quill handle.
            undoDocument() {
                if (!this.undoStack.length) return false;
                this.redoStack.push(this.snapshot());
                const prev = JSON.parse(this.undoStack.pop());
                this.applySnapshot(prev);
                return true;
            },

            redoDocument() {
                if (!this.redoStack.length) return false;
                this.undoStack.push(this.snapshot());
                const next = JSON.parse(this.redoStack.pop());
                this.applySnapshot(next);
                return true;
            },

            // =========================================
            // PAGINASI OTOMATIS (bridge Alpine <-> DocQuill)
            // Dipanggil editor.js saat satu kertas sudah penuh:
            // buat kertas baru tepat SETELAH kertas `uid`.
            // Return elemen .doc-sheet-body milik kertas baru.
            // =========================================
            async createPageAfter(uid) {

                const pos = this.pages.findIndex((p) => p.uid === uid);
                if (pos < 0) return null;

                const newUid = 'page-' + (++this.pageSeq) + '-' +
                    Date.now().toString(36);

                // Simpan state sebelum menambah halaman (untuk tombol Undo).
                this.pushHistory();

                // Jaga daftar halaman (dipakai saat init / hitung) tetap selaras.
                this.pages.splice(pos + 1, 0, { uid: newUid, html: '<p></p>' });

                const root = document.getElementById('document-editor');
                const target = root && uid
                    ? root.querySelector('[data-page-uid="' + uid + '"]')
                    : null;
                if (!root || !target) return null;

                // Penting: kertas di sini TIDAK dirender dengan x-for — halaman
                // dibuat secara IMPERATIF (pola sama dengan addPage()). Jadi hanya
                // memutasi this.pages tidak akan memunculkan DOM baru. Kita harus
                // membangun elemen <div class="doc-sheet"> baru secara eksplisit dan
                // menyelipkannya TEPAT SETELAH kertas `uid`.
                const sheet = document.createElement('div');
                sheet.className = 'doc-sheet';
                sheet.setAttribute('data-sheet-type', 'page');
                sheet.setAttribute('data-page-uid', newUid);

                const mkRegion = (cls, region, inner) => {
                    const el = document.createElement('div');
                    el.className = cls;
                    el.setAttribute('data-region', region);
                    el.innerHTML = inner;
                    return el;
                };

                // Header & footer baru = cermin konten halaman lain (ala Word).
                const firstHeader = root.querySelector('.doc-sheet-header[data-region="header"]');
                const firstFooter = root.querySelector('.doc-sheet-footer[data-region="footer"]');

                const headerRegion = mkRegion('doc-sheet-header', 'header',
                    firstHeader ? window.DocQuill.getHtml(firstHeader) : '<p></p>');
                const bodyRegion = mkRegion('doc-sheet-body', 'body', '<p></p>');
                const footerRegion = mkRegion('doc-sheet-footer', 'footer',
                    firstFooter ? window.DocQuill.getHtml(firstFooter) : '<p></p>');

                sheet.appendChild(headerRegion);
                sheet.appendChild(bodyRegion);
                sheet.appendChild(footerRegion);

                // Tombol hapus halaman pada kertas baru (selalu bukan yang pertama).
                const rmBtn = document.createElement('button');
                rmBtn.type = 'button';
                rmBtn.className = 'page-remove-btn print:hidden';
                rmBtn.setAttribute('data-page-uid', newUid);
                rmBtn.title = 'Hapus halaman ini';
                rmBtn.textContent = '✕';
                sheet.appendChild(rmBtn);

                // Selipkan di belakang kertas `uid`.
                if (target.nextSibling) {
                    target.parentNode.insertBefore(sheet, target.nextSibling);
                } else {
                    target.parentNode.appendChild(sheet);
                }

                // Pasang editor pada ketiga region kertas baru.
                window.DocQuill.attachRegion(headerRegion);
                window.DocQuill.attachRegion(bodyRegion);
                window.DocQuill.attachRegion(footerRegion);

                // Zona baru mengikuti status sesi edit yang sedang berjalan.
                window.DocQuill.setZonesEnabled('header', this.editSection === 'header');
                window.DocQuill.setZonesEnabled('footer', this.editSection === 'footer');

                this.markAsChanged();

                return bodyRegion;
            },

            initAutoPagination() {
                if (typeof window.DocQuill?.enableAutoPagination !== 'function') {
                    return; // bundel usang — fitur lain tetap jalan
                }

                window.DocQuill.enableAutoPagination({
                    createPageAfter: (uid) => this.createPageAfter(uid),
                });
            },

            initZoneEditMode() {
                const rootEl = document.getElementById('document-editor');
                if (!rootEl || rootEl.dataset.zoneEditBound === '1') return;
                rootEl.dataset.zoneEditBound = '1';

                rootEl.addEventListener('dblclick', (e) => {
                    console.log('[dblclick] jalan', { target: e.target, editSection: this.editSection });
                    const headerZone = e.target.closest('.doc-sheet-header');
                    if (headerZone) {
                        this.enterEditSection('header', headerZone);
                        return;
                    }

                    const footerZone = e.target.closest('.doc-sheet-footer');
                    if (footerZone) {
                        this.enterEditSection('footer', footerZone);
                        return;
                    }

                    if (e.target.closest('.doc-sheet-body') && this.editSection) {
                        this.exitEditSection();
                        return;
                    }

                    const bodyZone = e.target.closest('.doc-sheet-body');
                    if (bodyZone && !this.editSection) {
                        // BUGFIX blok teks double-click: bila titik klik ada di
                        // ATAS teks yang sudah ada, biarkan browser menyeleksi
                        // kata secara native (fitur blok kata). clickAndType
                        // (sisip baris/spasi + pindah caret) HANYA untuk titik
                        // klik di area KOSONG kertas. Dulu SEMUA dblclick di body
                        // menjalankan clickAndType -> seleksi kata selalu
                        // terhapus (removeAllRanges) dan caret melompat ke ekor
                        // teks secara tidak wajar.
                        let overText = false;
                        try {
                            const cr = document.caretRangeFromPoint
                                ? document.caretRangeFromPoint(e.clientX, e.clientY)
                                : null;
                            const cp = !cr && document.caretPositionFromPoint
                                ? document.caretPositionFromPoint(e.clientX, e.clientY)
                                : null;
                            const node = cr ? cr.startContainer : (cp ? cp.offsetNode : null);
                            overText = !!(
                                node &&
                                node.nodeType === Node.TEXT_NODE &&
                                bodyZone.contains(node)
                            );
                        } catch (err) { /* noop */ }

                        if (!overText) {
                            const handled = window.DocQuill.clickAndType?.(bodyZone, e.clientX, e.clientY);
                            if (handled) {
                                e.preventDefault();
                                try { window.getSelection()?.removeAllRanges(); } catch (err) { /* noop */ }
                            }
                        }
                    }
                });

                // SATU KLIK pada isi dokumen saat sesi aktif -> langsung kembali
                // ke konten utama (caret otomatis mengikuti posisi klik).
                rootEl.addEventListener(
                    'mousedown',
                    (e) => {
                        if (!this.editSection) return;
                        if (e.target.closest('.doc-sheet-body')) {
                            this.exitEditSection();
                            // tanpa preventDefault: caret menempel di titik klik
                        }
                    },
                    true
                );

                // Tombol Esc juga mengakhiri sesi edit
                document.addEventListener('keydown', (e) => {
                    if (e.key === 'Escape' && this.editSection) {
                        this.exitEditSection();
                    }
                });
            },

            enterEditSection(section, zoneEl = null) {
                if (section !== 'header' && section !== 'footer') return;

                try {
                    // Pastikan sesi role lain benar-benar mati
                    // (supaya pindah Header <-> Footer tidak meninggalkan sesuatu aktif)
                    const other = section === 'header' ? 'footer' : 'header';
                    window.DocQuill.setZonesEnabled(other, false);

                    this.editSection = section;

                    const rootEl = document.getElementById('document-editor');
                    rootEl?.classList.remove('editing-header', 'editing-footer');
                    rootEl?.classList.add('editing-' + section);

                    window.DocQuill.setZonesEnabled(section, true);
                    window.DocQuill.syncAllMirrors();

                    const target = zoneEl ||
                        document.querySelector('.doc-sheet-' + section + '[data-region="' + section + '"]');
                    if (target) window.DocQuill.focusZone(target);
                } catch (err) {
                    console.error('[ZoneEdit] Gagal masuk sesi:', err);
                }
            },

            exitEditSection() {
                this.editSection = null;

                try {
                    const rootEl = document.getElementById('document-editor');
                    rootEl?.classList.remove('editing-header', 'editing-footer');

                    // Kunci KEDUA role sekaligus + bersihkan jejak pembatas.
                    // Mode konten utama dijamin selalu meninggalkan semua zona terkunci.
                    ['header', 'footer'].forEach((role) => {
                        window.DocQuill.setZonesEnabled(role, false);
                        document.querySelectorAll('.doc-sheet-' + role + '.zone-editing')
                            .forEach((el) => el.classList.remove('zone-editing'));
                    });

                    // Blur hanya bila fokus masih berada di dalam zona
                    const ae = document.activeElement;
                    if (ae && ae.closest?.('.doc-sheet-header, .doc-sheet-footer')) {
                        ae.blur?.();
                    }

                    // Bersihkan sisa kunci seleksi dari interaksi lain (resize gambar dll.)
                    document.body.style.userSelect = '';

                    // Jaminan terakhir: konten utama pasti bisa diketik kembali
                    const revived = window.DocQuill.ensureBodyEditable?.() || 0;
                    console.info(
                        '[ZoneEdit] Sesi ' + (section || '-') + ' diakhiri. ' +
                        'Body dipulihkan: ' + revived + '. Mode konten utama aktif.'
                    );
                } catch (err) {
                    console.error('[ZoneEdit] Gagal keluar sesi:', err);
                }
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
                const root = document.getElementById('document-editor');
                if (!root) return;

                // Hapus signature lama
                root.querySelectorAll('.doc-signature').forEach((el) => el.remove());

                if (!this.selectedSignature) return;

                // Tampilkan di halaman terakhir
                const sheets = root.querySelectorAll('.doc-sheet[data-sheet-type="page"]');
                const lastSheet = sheets[sheets.length - 1];
                if (!lastSheet) return;

                const sig = document.createElement('div');
                sig.className = 'doc-signature';
                sig.setAttribute('data-signature', '1');
                sig.style.cssText =
                    'position:absolute;left:' + this.signatureX + 'px;top:' + this.signatureY +
                    'px;z-index:30;cursor:move;';

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

                document.getElementById('document-editor')
                    ?.querySelectorAll('.doc-signature').forEach((el) => el.remove());

                this.markAsChanged();
            },

            // =========================================
            // SAVE DOCUMENT
            // =========================================

            async saveDocument() {

                // Akhiri sesi edit header/footer sebelum menyimpan
                this.exitEditSection();
                this.saveStatus = 'saving';

                // Setelah tersimpan, riwayat undo dokumen direset supaya tidak
                // muncul snapshot lama yang sudah tidak relevan lagi.
                this.undoStack = [];
                this.redoStack = [];

                const root = document.getElementById('document-editor');

                // Baca header (region di halaman pertama)
                const headerRegion = root?.querySelector('.doc-sheet-header[data-region="header"]');
                const headerContent = headerRegion
                    ? window.DocQuill.getHtml(headerRegion)
                    : (this.headerHtml || '');

                // Baca footer (region di halaman terakhir)
                const footerRegion = root?.querySelector('.doc-sheet-footer[data-region="footer"]');
                const footerContent = footerRegion
                    ? window.DocQuill.getHtml(footerRegion)
                    : (this.footerHtml || '');

                // Baca semua halaman body
                const pagesHtml = [];
                if (root) {
                    root.querySelectorAll('.doc-sheet[data-sheet-type="page"]').forEach((sheet) => {
                        const bodyRegion = sheet.querySelector('.doc-sheet-body[data-region="body"]');
                        pagesHtml.push(bodyRegion ? window.DocQuill.getHtml(bodyRegion) : '');
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

                const root = document.getElementById('document-editor');

                const headerRegion = root?.querySelector('.doc-sheet-header[data-region="header"]');
                const footerRegion = root?.querySelector('.doc-sheet-footer[data-region="footer"]');

                const pagesHtml = [];
                if (root) {
                    root.querySelectorAll('.doc-sheet[data-sheet-type="page"]').forEach((sheet) => {
                        const bodyRegion = sheet.querySelector('.doc-sheet-body[data-region="body"]');
                        pagesHtml.push(bodyRegion ? window.DocQuill.getHtml(bodyRegion) : '');
                    });
                }

                const payload = {
                    title: newTitle,
                    type: @js($document -> type ?? 'surat'),
                    header_data: {
                        nomorSurat: @js($document -> header_data['nomorSurat'] ?? ''),
                        content: headerRegion ? window.DocQuill.getHtml(headerRegion) : (this.headerHtml || ''),
                    },
                    body_content: {
                        pages: pagesHtml
                    },
                    footer_data: {
                        content: footerRegion
                            ? window.DocQuill.getHtml(footerRegion)
                            : (this.footerHtml || ''),
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
                const sigEl = document.querySelector('#document-editor .doc-signature');
                if (sigEl) {
                    sigEl.style.left = this.signatureX + 'px';
                    sigEl.style.top = this.signatureY + 'px';
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
                            inputValue: @js(($document -> title ?? 'Dokumen'). ' (Salinan)'),
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