@extends('layouts.app')

@section('content')

<div x-data="wordDocumentEditor()" class="min-h-screen">

    {{-- ========================================= --}}
    {{-- TOP BAR --}}
    {{-- ========================================= --}}
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
                    Simpan
                </button>
            </div>

        </div>

        {{-- ========================================= --}}
        {{-- WORD TOOLBAR (SUDAH DIPERBARUI) --}}
        {{-- ========================================= --}}
        <div class="editor-toolbar border-t border-parchment-200 px-5 py-2 dark:border-slate-warm-700">

            <div class="flex flex-wrap items-center gap-1">

                {{-- Undo & Redo --}}
                <button type="button" @click="format('undo')" class="toolbar-button" title="Undo">↶</button>
                <button type="button" @click="format('redo')" class="toolbar-button" title="Redo">↷</button>

                <div class="toolbar-divider"></div>

                {{-- Heading / Paragraph Style --}}
                <select @change="format('formatBlock', $event.target.value)" class="toolbar-select w-32">
                    <option value="P">Paragraf Biasa</option>
                    <option value="H1">Heading 1</option>
                    <option value="H2">Heading 2</option>
                    <option value="H3">Heading 3</option>
                    <option value="BLOCKQUOTE">Kutipan</option>
                </select>

                {{-- Font Family --}}
                <select @change="format('fontName', $event.target.value)" class="toolbar-select">
                    <option value="Arial">Arial</option>
                    <option value="Times New Roman">Times New Roman</option>
                    <option value="Calibri">Calibri</option>
                    <option value="Georgia">Georgia</option>
                    <option value="Verdana">Verdana</option>
                    <option value="Cambria">Cambria</option>
                    <option value="Garamond">Garamond</option>
                    <option value="Courier New">Courier New</option>
                    <option value="Tahoma">Tahoma</option>
                    <option value="Trebuchet MS">Trebuchet MS</option>
                    <option value="Palatino Linotype">Palatino Linotype</option>
                </select>

                {{-- Font Size --}}
                <select @change="format('fontSize', $event.target.value)" class="toolbar-select w-20">
                    <option value="2">10</option>
                    <option value="3" selected>12</option>
                    <option value="4">14</option>
                    <option value="5">18</option>
                    <option value="6">24</option>
                    <option value="7">32</option>
                </select>

                <div class="toolbar-divider"></div>

                {{-- Text Formatting --}}
                <button type="button" @click="format('bold')" class="toolbar-button font-bold" title="Bold">B</button>
                <button type="button" @click="format('italic')" class="toolbar-button italic" title="Italic">I</button>
                <button type="button" @click="format('underline')" class="toolbar-button underline"
                    title="Underline">U</button>

                {{-- Colors --}}
                <div class="flex items-center gap-1 pl-1">
                    <input type="color" @change="format('foreColor', $event.target.value)" title="Warna Teks"
                        class="toolbar-color" value="#000000">
                    <input type="color" @change="format('hiliteColor', $event.target.value)" title="Warna Sorot"
                        class="toolbar-color" value="#ffff00">
                </div>

                <div class="toolbar-divider"></div>

                {{-- Text Alignment --}}
                <button type="button" @click="format('justifyLeft')" class="toolbar-button" title="Rata kiri">⬅</button>
                <button type="button" @click="format('justifyCenter')" class="toolbar-button"
                    title="Rata tengah">↔</button>
                <button type="button" @click="format('justifyRight')" class="toolbar-button"
                    title="Rata kanan">➡</button>
                <button type="button" @click="format('justifyFull')" class="toolbar-button" title="Justify">☰</button>

                {{-- Indent / Outdent --}}
                <button type="button" @click="format('outdent')" class="toolbar-button"
                    title="Kurangi Indentasi">⇤</button>
                <button type="button" @click="format('indent')" class="toolbar-button"
                    title="Tambah Indentasi">⇥</button>

                <div class="toolbar-divider"></div>

                {{-- Lists --}}
                <button type="button" @click="format('insertUnorderedList')" class="toolbar-button"
                    title="Bullet">•</button>
                <button type="button" @click="format('insertOrderedList')" class="toolbar-button"
                    title="Numbering">1.</button>

                <div class="toolbar-divider"></div>

                {{-- Inserts --}}
                <button type="button" @click="insertTable()" class="toolbar-button" title="Sisipkan Tabel">⊞</button>
                <button type="button" @click="insertLink()" class="toolbar-button" title="Sisipkan Tautan">🔗</button>
                <button type="button" @click="format('insertHorizontalRule')" class="toolbar-button"
                    title="Garis Pembatas">―</button>

                <div class="toolbar-divider"></div>

                {{-- Clear Formatting --}}
                <button type="button" @click="format('removeFormat')"
                    class="toolbar-button text-xs font-semibold text-red-600" title="Hapus Format">Tx</button>

            </div>

        </div>

    </div>


    {{-- ========================================= --}}
    {{-- DOCUMENT AREA --}}
    {{-- ========================================= --}}
    <main class="documentPrintArea bg-slate-100 px-4 py-10 dark:bg-slate-warm-950">
        <div class="mx-auto w-full max-w-[794px] space-y-8">

            <template x-for="(page, index) in pages" :key="index">
                <div class="document-page relative w-full bg-white shadow-xl">

                    {{-- Page info bar --}}
                    <div class="flex items-center justify-between px-6 pt-3 text-xs text-slate-400 print:hidden">
                        <span x-text="'Halaman ' + (index + 1) + ' dari ' + pages.length"></span>
                        <button type="button" x-show="pages.length > 1" @click="removePage(index)"
                            class="text-red-500 hover:underline">Hapus Halaman Ini</button>
                    </div>

                    {{-- HEADER / KOP SURAT (hanya halaman pertama) --}}
                    <div x-show="index === 0" class="px-[80px] pt-[20px]">
                        <div class="text-center">
                            <div class="text-xl font-bold uppercase tracking-wide text-black">
                                {{ $document->header_data['kopInstansi'] ?? '' }}
                            </div>
                            <div class="mt-1 text-xs leading-relaxed text-black">
                                {{ $document->header_data['kopAlamat'] ?? '' }}
                            </div>
                            @if(!empty($document->header_data['kopKontrak']))
                            <div class="text-xs leading-relaxed text-black">
                                {{ $document->header_data['kopKontrak'] }}
                            </div>
                            @endif
                        </div>

                        <div class="mt-4 border-b-2 border-black"></div>

                        <div class="mt-7 text-sm text-black">
                            <div class="grid grid-cols-[90px_15px_1fr]">
                                <span>Nomor</span><span>:</span><span>{{ $document->header_data['nomorSurat'] ?? ''
                                    }}</span>
                            </div>
                            <div class="mt-1 grid grid-cols-[90px_15px_1fr]">
                                <span>Tanggal</span><span>:</span><span>{{ $document->header_data['tanggalSurat'] ?? ''
                                    }}</span>
                            </div>
                            <div class="mt-1 grid grid-cols-[90px_15px_1fr]">
                                <span>Perihal</span><span>:</span><span>{{ $document->header_data['perihalSurat'] ?? ''
                                    }}</span>
                            </div>
                            @if(!empty($document->header_data['sifatSurat']))
                            <div class="mt-1 grid grid-cols-[90px_15px_1fr]">
                                <span>Sifat</span><span>:</span><span>{{ $document->header_data['sifatSurat'] }}</span>
                            </div>
                            @endif
                        </div>
                    </div>

                    {{-- Penanda halaman lanjutan --}}
                    <div x-show="index > 0" class="px-[80px] pt-[40px] text-xs text-slate-400 italic">
                        ...lanjutan halaman <span x-text="index + 1"></span>
                    </div>

                    {{-- BODY EDITOR (per halaman) --}}
                    <div class="px-[80px]">
                        <div :id="'document-editor-' + index" contenteditable="true" spellcheck="true"
                            @input="markAsChanged()" @focus="activeEditorId = 'document-editor-' + index"
                            @keydown="handleKeydown($event)"
                            class="document-body py-8 text-[14px] leading-7 text-black outline-none" x-html="page">
                        </div>
                    </div>

                    {{-- FOOTER / SIGNATURE (hanya halaman terakhir) --}}
                    <div x-show="index === pages.length - 1" class="px-[80px] pb-20 text-sm text-black">
                        <div class="ml-auto w-[260px] text-center">
                            <div>
                                {{ $document->footer_data['kotaTtd'] ?? '' }},
                                {{ $document->header_data['tanggalSurat'] ?? '' }}
                            </div>
                            <div class="mt-2 font-semibold">
                                {{ $document->footer_data['jabatanPenandatangan'] ?? '' }}
                            </div>
                            <div class="h-24"></div>
                            <div class="font-semibold underline">
                                {{ $document->footer_data['namaPenandatangan'] ?? '' }}
                            </div>
                            @if(!empty($document->footer_data['nipPenandatangan']))
                            <div class="text-xs">
                                {{ $document->footer_data['nipPenandatangan'] }}
                            </div>
                            @endif
                        </div>
                    </div>

                </div>
            </template>

            {{-- Tombol Tambah Halaman --}}
            <div class="flex justify-center print:hidden">
                <button type="button" @click="addPage()"
                    class="inline-flex items-center gap-2 rounded-xl border border-dashed border-parchment-400 bg-white px-5 py-2.5 text-sm font-medium text-ink-900 hover:bg-parchment-50 dark:bg-slate-warm-900 dark:text-parchment-100 dark:border-slate-warm-600">
                    + Tambah Halaman
                </button>
            </div>

        </div>
    </main>

</div>

@push('styles')
<style>
    .toolbar-button {
        min-width: 34px;
        height: 34px;
        display: inline-flex;
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
        width: 210mm;
        min-height: 297mm;
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

        /* Sembunyikan semua elemen UI */
        body * {
            visibility: hidden;
        }

        /* Tampilkan hanya area dokumen */
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

        /* Hilangkan margin browser */
        @page {
            size: A4;
            margin: 20mm;
        }

        /* Warna/background tetap dicetak */
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
            saveStatus: 'saved',
            changed: false,
            activeEditorId: 'document-editor-0',
            pages: @js($document -> body_content['pages'] ?? [$document -> body_content['content'] ?? '']),

            markAsChanged() {
                this.changed = true;
                this.saveStatus = 'idle';
            },

            addPage() {
                this.pages.push('');
                this.markAsChanged();
                this.$nextTick(() => {
                    const idx = this.pages.length - 1;
                    document.getElementById('document-editor-' + idx)?.focus();
                    document.getElementById('document-editor-' + idx)?.scrollIntoView({ behavior: 'smooth', block: 'center' });
                });
            },

            async removePage(index) {
                if (this.pages.length <= 1) {
                    Swal.fire({ icon: 'warning', title: 'Gak bisa dihapus', text: 'Dokumen minimal harus punya 1 halaman.', confirmButtonColor: '#1B2A4A' });
                    return;
                }
                const result = await Swal.fire({
                    icon: 'warning',
                    title: 'Hapus halaman ' + (index + 1) + '?',
                    text: 'Isi di halaman ini akan hilang.',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, hapus',
                    cancelButtonText: 'Batal',
                    confirmButtonColor: '#dc2626',
                });
                if (!result.isConfirmed) return;
                this.pages.splice(index, 1);
                this.markAsChanged();
            },

            format(command, value = null) {
                const editor = document.getElementById(this.activeEditorId) || document.getElementById('document-editor-0');
                if (!editor) return;
                editor.focus();
                document.execCommand(command, false, value);
                this.markAsChanged();
            },

            async insertTable() {
                const { value: formValues } = await Swal.fire({
                    title: 'Sisipkan Tabel',
                    html:
                        '<input id="swal-rows" type="number" min="1" value="2" class="swal2-input" placeholder="Jumlah baris">' +
                        '<input id="swal-cols" type="number" min="1" value="2" class="swal2-input" placeholder="Jumlah kolom">',
                    focusConfirm: false,
                    showCancelButton: true,
                    confirmButtonText: 'Sisipkan',
                    cancelButtonText: 'Batal',
                    confirmButtonColor: '#1B2A4A',
                    preConfirm: () => {
                        const rows = document.getElementById('swal-rows').value;
                        const cols = document.getElementById('swal-cols').value;
                        if (!rows || !cols || rows < 1 || cols < 1) {
                            Swal.showValidationMessage('Isi jumlah baris & kolom dengan benar');
                            return false;
                        }
                        return { rows: parseInt(rows), cols: parseInt(cols) };
                    }
                });

                if (!formValues) return;

                let tableHTML = '<table style="width: 100%; border-collapse: collapse; margin: 10px 0;"><tbody>';
                for (let i = 0; i < formValues.rows; i++) {
                    tableHTML += '<tr>';
                    for (let j = 0; j < formValues.cols; j++) {
                        tableHTML += '<td style="border: 1px solid #000; padding: 6px;">Teks</td>';
                    }
                    tableHTML += '</tr>';
                }
                tableHTML += '</tbody></table><p></p>';
                this.format('insertHTML', tableHTML);
            },

            async insertLink() {
                const { value: url } = await Swal.fire({
                    title: 'Sisipkan Tautan',
                    input: 'url',
                    inputPlaceholder: 'https://',
                    inputValue: 'https://',
                    showCancelButton: true,
                    confirmButtonText: 'Sisipkan',
                    cancelButtonText: 'Batal',
                    confirmButtonColor: '#1B2A4A',
                });
                if (url) this.format('createLink', url);
            },

            handleKeydown(event) {
                if ((event.ctrlKey || event.metaKey) && event.key.toLowerCase() === 's') {
                    event.preventDefault();
                    this.saveDocument();
                }
            },

            async saveDocument() {
                this.saveStatus = 'saving';

                const pagesHtml = this.pages.map((_, index) => {
                    const el = document.getElementById('document-editor-' + index);
                    return el ? el.innerHTML : '';
                });

                const payload = {
                    title: @js($document -> title),
                    type: @js($document -> type ?? 'surat'),
                    header_data: @js($document -> header_data ?? []),
                    body_content: { pages: pagesHtml },
                    footer_data: @js($document -> footer_data ?? []),
                    signature_data: @js($document -> signature_data ?? null),
                    status: 'draft'
                };

                try {
                    await window.axios.put(`/documents/${this.documentId}`, payload);
                    this.saveStatus = 'saved';
                    this.changed = false;
                } catch (error) {
                    console.error(error);
                    this.saveStatus = 'error';
                }
            }
        };
    }
</script>
@endpush

@endsection