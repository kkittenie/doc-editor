@extends('layouts.app')

@section('content')

<div
    x-data="wordDocumentEditor()"
    class="min-h-screen"
>

 
{{-- ========================================= --}}
{{-- TOP BAR --}}
{{-- ========================================= --}}

<div class="sticky top-0 z-40 border-b border-parchment-300 bg-white/95 backdrop-blur dark:border-slate-warm-700 dark:bg-slate-warm-900/95">

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
                Simpan
            </button>

        </div>

    </div>


{{-- ========================================= --}}
{{-- WORD TOOLBAR --}}
{{-- ========================================= --}}

<div class="border-t border-parchment-200 px-5 py-2 dark:border-slate-warm-700">

 
<div class="flex flex-wrap items-center gap-1">

    {{-- Undo --}}
    <button
        type="button"
        @click="format('undo')"
        class="toolbar-button"
        title="Undo"
    >
        ↶
    </button>

    {{-- Redo --}}
    <button
        type="button"
        @click="format('redo')"
        class="toolbar-button"
        title="Redo"
    >
        ↷
    </button>

    <div class="toolbar-divider"></div>


    {{-- Font --}}
    <select
        @change="format('fontName', $event.target.value)"
        class="toolbar-select"
    >
        <option value="Arial">Arial</option>
        <option value="Times New Roman">Times New Roman</option>
        <option value="Calibri">Calibri</option>
        <option value="Georgia">Georgia</option>
        <option value="Verdana">Verdana</option>
    </select>


    {{-- Font Size --}}
    <select
        @change="format('fontSize', $event.target.value)"
        class="toolbar-select w-20"
    >
        <option value="2">10</option>
        <option value="3" selected>12</option>
        <option value="4">14</option>
        <option value="5">18</option>
        <option value="6">24</option>
        <option value="7">32</option>
    </select>

    <div class="toolbar-divider"></div>


    {{-- Bold --}}
    <button
        type="button"
        @click="format('bold')"
        class="toolbar-button font-bold"
        title="Bold"
    >
        B
    </button>

    {{-- Italic --}}
    <button
        type="button"
        @click="format('italic')"
        class="toolbar-button italic"
        title="Italic"
    >
        I
    </button>

    {{-- Underline --}}
    <button
        type="button"
        @click="format('underline')"
        class="toolbar-button underline"
        title="Underline"
    >
        U
    </button>

    <div class="toolbar-divider"></div>


    {{-- Align Left --}}
    <button
        type="button"
        @click="format('justifyLeft')"
        class="toolbar-button"
        title="Rata kiri"
    >
        ⬅
    </button>

    {{-- Align Center --}}
    <button
        type="button"
        @click="format('justifyCenter')"
        class="toolbar-button"
        title="Rata tengah"
    >
        ↔
    </button>

    {{-- Align Right --}}
    <button
        type="button"
        @click="format('justifyRight')"
        class="toolbar-button"
        title="Rata kanan"
    >
        ➡
    </button>

    {{-- Justify --}}
    <button
        type="button"
        @click="format('justifyFull')"
        class="toolbar-button"
        title="Justify"
    >
        ☰
    </button>

    <div class="toolbar-divider"></div>


    {{-- Bullet --}}
    <button
        type="button"
        @click="format('insertUnorderedList')"
        class="toolbar-button"
        title="Bullet"
    >
        •
    </button>

    {{-- Number --}}
    <button
        type="button"
        @click="format('insertOrderedList')"
        class="toolbar-button"
        title="Numbering"
    >
        1.
    </button>

</div>
 

</div>


{{-- ========================================= --}}
{{-- DOCUMENT AREA --}}
{{-- ========================================= --}}

<main class="bg-slate-100 px-4 py-10 dark:bg-slate-warm-950">

    <div class="mx-auto w-full max-w-[794px]">

        {{-- A4 PAPER --}}
        <div
            class="document-page relative w-full bg-white shadow-xl"
        >

            {{-- ================================= --}}
            {{-- HEADER / KOP SURAT --}}
            {{-- ================================= --}}

            <div class="px-[80px] pt-[65px]">

                {{-- Company --}}
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


                {{-- Garis Kop --}}
                <div class="mt-4 border-b-2 border-black"></div>


                {{-- ================================= --}}
                {{-- DOCUMENT INFO --}}
                {{-- ================================= --}}

                <div class="mt-7 text-sm text-black">

                    <div class="grid grid-cols-[90px_15px_1fr]">
                        <span>Nomor</span>
                        <span>:</span>
                        <span>
                            {{ $document->header_data['nomorSurat'] ?? '' }}
                        </span>
                    </div>

                    <div class="mt-1 grid grid-cols-[90px_15px_1fr]">
                        <span>Tanggal</span>
                        <span>:</span>
                        <span>
                            {{ $document->header_data['tanggalSurat'] ?? '' }}
                        </span>
                    </div>

                    <div class="mt-1 grid grid-cols-[90px_15px_1fr]">
                        <span>Perihal</span>
                        <span>:</span>
                        <span>
                            {{ $document->header_data['perihalSurat'] ?? '' }}
                        </span>
                    </div>

                    @if(!empty($document->header_data['sifatSurat']))
                        <div class="mt-1 grid grid-cols-[90px_15px_1fr]">
                            <span>Sifat</span>
                            <span>:</span>
                            <span>
                                {{ $document->header_data['sifatSurat'] }}
                            </span>
                        </div>
                    @endif

                </div>


                {{-- ================================= --}}
                {{-- BODY EDITOR --}}
                {{-- ================================= --}}

                <div
                    id="document-editor"
                    contenteditable="true"
                    spellcheck="true"
                    @input="markAsChanged()"
                    @keydown="handleKeydown($event)"
                    class="document-body mt-10 pb-20 text-[14px] leading-7 text-black outline-none"
                    >{!! $document->body_content['content'] ?? '' !!}</div>


                {{-- ================================= --}}
                {{-- FOOTER / SIGNATURE --}}
                {{-- ================================= --}}

                <div class="mt-12 pb-20 text-sm text-black">

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
    }

    .toolbar-button:hover {
        background: rgb(245 242 235);
    }

    [contenteditable="true"]:focus {
        outline: none;
    }

    [contenteditable="true"] p {
        margin-bottom: 0.75rem;
    }

    [contenteditable="true"] ul {
        list-style-type: disc;
        padding-left: 2rem;
    }

    [contenteditable="true"] ol {
        list-style-type: decimal;
        padding-left: 2rem;
    }

    .toolbar-divider {
        width: 1px;
        height: 24px;
        margin: 0 6px;
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

        ```
        .toolbar-select {
            min-width: 100px;
        }
        ```

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

        .document-body h1,
        .document-body h2,
        .document-body h3 {
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
        margin: 15px 30px;
        padding-left: 15px;
        border-left: 3px solid #ccc;
        }

        @media print {

        ```
        body {
            background: white !important;
        }

        .document-page {
            width: 210mm;
            min-height: 297mm;
            margin: 0;
            box-shadow: none;
        }
        ```

    }



</style>

@endpush

@push('scripts')

<script>

function wordDocumentEditor() {

    return {

        documentId: @js($document->id),

        saveStatus: 'saved',

        changed: false,


        markAsChanged() {

            this.changed = true;

            this.saveStatus = 'idle';

        },


        format(command, value = null) {

            const editor = document.getElementById('document-editor');
            if (!editor) return;
            editor.focus();
            document.execCommand(command, false, value);
            this.markAsChanged();
        }

        handleKeydown(event) {

            // Ctrl + S
            if ((event.ctrlKey || event.metaKey) && event.key.toLowerCase() === 's') {

                event.preventDefault();

                this.saveDocument();

            }

        },


        async saveDocument() {

            const editor = document.getElementById('document-editor');

            if (!editor) return;


            this.saveStatus = 'saving';


            const payload = {

                title: @js($document->title),

                type: @js($document->type ?? 'surat'),

                header_data: @js($document->header_data ?? []),

                body_content: {

                    content: editor.innerHTML

                },

                footer_data: @js($document->footer_data ?? []),

                signature_data: @js($document->signature_data ?? null),

                status: 'draft'

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

        }

    };

}

</script>

@endpush

@endsection
