@extends('layouts.app')

@section('content')
<div x-data="{
    loadingTemplate: null,
    isUploadingLogo: false,
    headerHtml: null,
    footerHtml: null,
    bodyHtml: null,

    init() {

        this.$nextTick(() => {

            window.createTiptapEditor(
                'header',
                this.$refs.headerEditor,
                '<p></p>',
                html => {
                    this.headerHtml = html;
                }
            );


            window.createTiptapEditor(
                'footer',
                this.$refs.footerEditor,
                '<p></p>',
                html => {
                    this.footerHtml = html;
                }
            );

        });

    },

    initEditors() {
        this.headerEditor = window.createTiptapEditor(
            this.$refs.headerEditor,
            '',
            (html) => {
                this.headerHtml = html;
            }
        );

        this.footerEditor = window.createTiptapEditor(
            this.$refs.footerEditor,
            '',
            (html) => {
                this.footerHtml = html;
            }
        );
    },

    setHeaderContent(content) {
        this.headerHtml = content;

        if (this.headerEditor) {
            this.headerEditor.commands.setContent(content || '');
        }
    },

    setFooterContent(content) {
        this.footerHtml = content;

        if (this.footerEditor) {
            this.footerEditor.commands.setContent(content || '');
        }
    },

    async loadTemplate(key) {
    this.loadingTemplate = key;
    try {
        const res = await window.axios.get('/documents/template/' + key);
        const t = res.data;

        document.getElementById('header-judul').value = t.title;
        document.getElementById('header-nomor').value = t.header_data.nomorSurat;
    } catch (e) {
        Swal.fire({ icon: 'error', title: 'Gagal', text: 'Gagal memuat template.', confirmButtonColor: '#1B2A4A' });
        console.error(e);
    } finally {
        this.loadingTemplate = null;
    }
},

    uploadLogo(event) {
    const file = event.target.files[0];
    if (!file) return;
    this.isUploadingLogo = true;
    const reader = new FileReader();
    reader.onload = async (e) => {
        try {
            const res = await window.axios.post('/documents/logo', { image: e.target.result });
            const el = document.getElementById('header-editor');
            el.focus();
            document.execCommand(
                'insertHTML',
                false,
                '<img src=\'' + res.data.url + '\' class=\'drag-image\' draggable=\'false\' style=\'position:absolute;left:10px;top:10px;max-height:70px;max-width:150px;cursor:grab;\'>'
            );
        } catch (err) {
            Swal.fire({ icon: 'error', title: 'Gagal', text: 'Gagal mengunggah logo.', confirmButtonColor: '#1B2A4A' });
            console.error(err);
        } finally {
            this.isUploadingLogo = false;
            event.target.value = '';
        }
    };
    reader.readAsDataURL(file);
},

    enableImageDragAndDrop(editorId) {
        this.$nextTick(() => {
            const editor = document.getElementById(editorId);
            if (!editor) return;

            let draggedImage = null;
            let isDraggingImage = false;
            let offsetX = 0;
            let offsetY = 0;

            // KODE DRAG ASLI KAMU (DIKEMBALIKAN 100%)
            editor.addEventListener('mousedown', (e) => {
                if (e.target.tagName === 'IMG' && e.target.classList.contains('drag-image')) {
                    isDraggingImage = true;
                    draggedImage = e.target;
                    const rect = draggedImage.getBoundingClientRect();
                    offsetX = e.clientX - rect.left;
                    offsetY = e.clientY - rect.top;
                    draggedImage.style.cursor = 'grabbing';
                    draggedImage.style.position = 'absolute';
                    draggedImage.style.zIndex = '10';
                }
            });

            document.addEventListener('mousemove', (e) => {
                if (!isDraggingImage || !draggedImage) return;

                const editorRect = editor.getBoundingClientRect();
                const imageRect = draggedImage.getBoundingClientRect();

                const imageWidth = imageRect.width;
                const imageHeight = imageRect.height;

                const maxX = editorRect.width - imageWidth;
                const maxY = editorRect.height - imageHeight;

                let newX = e.clientX - editorRect.left - offsetX;
                let newY = e.clientY - editorRect.top - offsetY;

                newX = Math.max(0, Math.min(newX, maxX));
                newY = Math.max(0, Math.min(newY, maxY));

                draggedImage.style.left = newX + 'px';
                draggedImage.style.top = newY + 'px';
            });

            document.addEventListener('mouseup', (e) => {
                if (isDraggingImage && draggedImage) {
                    isDraggingImage = false;
                    draggedImage.style.cursor = 'grab';
                }
            });
        });
    },

    syncBeforeSubmit() {

        if (this.headerEditor) {
            this.headerHtml = this.headerEditor.getHTML();
        }

        if (this.footerEditor) {
            this.footerHtml = this.footerEditor.getHTML();
        }

        document.getElementById('header-content-input').value =
            this.headerHtml;

        document.getElementById('footer-content-input').value =
            this.footerHtml;
    }
}" class="max-w-4xl mx-auto py-8">

    <div class="mb-6">
        <h1 class="text-2xl font-bold text-ink-900 dark:text-parchment-50">Buat Dokumen Baru</h1>
        <p class="mt-2 text-sm text-slate-warm-600 dark:text-parchment-400">
            Susun kop surat dan footer bebas seperti menulis di Word, atau mulai cepat dari template.
        </p>
    </div>

    {{-- Quick Templates --}}
    <div
        class="mb-6 rounded-2xl border border-parchment-300 bg-parchment-50 p-5 dark:border-slate-warm-700 dark:bg-slate-warm-800">
        <h2 class="text-sm font-semibold text-ink-900 dark:text-parchment-50 mb-3">Mulai Cepat dari Template</h2>
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
            <button type="button" @click="loadTemplate('perjanjian-kerja-sama')" :disabled="loadingTemplate"
                class="rounded-xl border border-parchment-300 bg-white p-3 text-xs font-medium text-ink-900 hover:border-bronze-400 transition-colors dark:bg-slate-warm-900 dark:border-slate-warm-700 dark:text-parchment-100">
                <span x-text="loadingTemplate === 'perjanjian-kerja-sama' ? 'Memuat...' : 'Perjanjian / PKS'"></span>
            </button>
            <button type="button" @click="loadTemplate('kontrak-kerja')" :disabled="loadingTemplate"
                class="rounded-xl border border-parchment-300 bg-white p-3 text-xs font-medium text-ink-900 hover:border-bronze-400 transition-colors dark:bg-slate-warm-900 dark:border-slate-warm-700 dark:text-parchment-100">
                <span x-text="loadingTemplate === 'kontrak-kerja' ? 'Memuat...' : 'Kontrak Kerja'"></span>
            </button>
            <button type="button" @click="loadTemplate('surat-kuasa')" :disabled="loadingTemplate"
                class="rounded-xl border border-parchment-300 bg-white p-3 text-xs font-medium text-ink-900 hover:border-bronze-400 transition-colors dark:bg-slate-warm-900 dark:border-slate-warm-700 dark:text-parchment-100">
                <span x-text="loadingTemplate === 'surat-kuasa' ? 'Memuat...' : 'Surat Kuasa'"></span>
            </button>
            <button type="button" @click="loadTemplate('surat-pernyataan')" :disabled="loadingTemplate"
                class="rounded-xl border border-parchment-300 bg-white p-3 text-xs font-medium text-ink-900 hover:border-bronze-400 transition-colors dark:bg-slate-warm-900 dark:border-slate-warm-700 dark:text-parchment-100">
                <span x-text="loadingTemplate === 'surat-pernyataan' ? 'Memuat...' : 'Surat Pernyataan'"></span>
            </button>
        </div>
    </div>

    <form action="{{ route('documents.store') }}" method="POST" @submit="syncBeforeSubmit()">
        @csrf
        <input type="hidden" id="header-content-input" name="header_data[content]">
        <input type="hidden" id="footer-content-input" name="footer_data[content]">
        <input type="hidden" name="body_html" :value="bodyHtml">

        {{-- Judul & Nomor --}}
        <div
            class="rounded-2xl border border-parchment-300 bg-white p-5 shadow-sm dark:border-slate-warm-700 dark:bg-slate-warm-900 mb-5">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium mb-1.5">Judul Dokumen</label>
                    <input type="text" id="header-judul" name="title" required
                        placeholder="Contoh: Surat Keputusan Direksi"
                        class="w-full rounded-xl border border-parchment-300 bg-white px-4 py-2.5 text-sm outline-none focus:border-bronze-500 dark:border-slate-warm-700 dark:bg-slate-warm-800">
                </div>
                <div>
                    <label class="block text-xs font-medium mb-1.5">Nomor Dokumen</label>
                    <input type="text" id="header-nomor" name="header_data[nomorSurat]" required
                        placeholder="001/SK-DIR/VIII/2026"
                        class="w-full rounded-xl border border-parchment-300 bg-white px-4 py-2.5 text-sm outline-none focus:border-bronze-500 dark:border-slate-warm-700 dark:bg-slate-warm-800">
                </div>
            </div>
        </div>

        {{-- HEADER: TIPTAP --}}
        <div class="mb-5">

            <label class="block text-xs font-medium mb-1.5">
                Header / Kop Surat
            </label>

            <div class="overflow-hidden rounded-xl border
                    border-parchment-300
                    bg-white
                    dark:border-slate-warm-700
                    dark:bg-slate-warm-900">

                {{-- TOOLBAR --}}
                <div class="flex flex-wrap items-center gap-1
                        border-b border-parchment-300
                        bg-parchment-50
                        p-2
                        dark:border-slate-warm-700
                        dark:bg-slate-warm-800">

                    {{-- UNDO --}}
                    <button type="button" @click="tiptapCommand('header', 'undo')" class="toolbar-btn" title="Undo">
                        ↶
                    </button>

                    <button type="button" @click="tiptapCommand('header', 'redo')" class="toolbar-btn" title="Redo">
                        ↷
                    </button>

                    <span class="toolbar-divider"></span>


                    {{-- HEADING --}}
                    <select @change="
                            $event.target.value === 'paragraph'
                                ? tiptapCommand(headerEditor, 'paragraph')
                                : tiptapCommand(headerEditor, 'heading', $event.target.value)
                        " class="toolbar-select">
                        <option value="paragraph">Normal</option>
                        <option value="1">Heading 1</option>
                        <option value="2">Heading 2</option>
                        <option value="3">Heading 3</option>
                    </select>


                    {{-- TEXT --}}
                    <button type="button" @click="tiptapCommand('header', 'bold')" class="toolbar-btn font-bold"
                        title="Bold">
                        B
                    </button>

                    <button type="button" @click="tiptapCommand('header', 'italic')" class="toolbar-btn italic"
                        title="Italic">
                        I
                    </button>

                    <button type="button" @click="tiptapCommand('header', 'underline')" class="toolbar-btn underline"
                        title="Underline">
                        U
                    </button>

                    <button type="button" @click="tiptapCommand('header', 'strike')" class="toolbar-btn line-through"
                        title="Strike">
                        S
                    </button>


                    <span class="toolbar-divider"></span>


                    {{-- ALIGN --}}
                    <button type="button" @click="tiptapCommand('header', 'alignLeft')" class="toolbar-btn"
                        title="Rata kiri">
                        ≡
                    </button>

                    <button type="button" @click="tiptapCommand('header', 'alignCenter')" class="toolbar-btn"
                        title="Rata tengah">
                        ≡
                    </button>

                    <button type="button" @click="tiptapCommand('header', 'alignRight')" class="toolbar-btn"
                        title="Rata kanan">
                        ≡
                    </button>

                    <button type="button" @click="tiptapCommand('header', 'alignJustify')" class="toolbar-btn"
                        title="Justify">
                        ≡
                    </button>


                    <span class="toolbar-divider"></span>


                    {{-- LIST --}}
                    <button type="button" @click="tiptapCommand('header', 'bulletList')" class="toolbar-btn"
                        title="Bullet List">
                        •
                    </button>

                    <button type="button" @click="tiptapCommand('header', 'orderedList')" class="toolbar-btn"
                        title="Numbered List">
                        1.
                    </button>


                    {{-- BLOCK --}}
                    <button type="button" @click="tiptapCommand('header', 'blockquote')" class="toolbar-btn"
                        title="Quote">
                        ❝
                    </button>

                    <button type="button" @click="tiptapCommand('header', 'horizontalRule')" class="toolbar-btn"
                        title="Horizontal line">
                        ―
                    </button>


                    {{-- SCRIPT --}}
                    <button type="button" @click="tiptapCommand('header', 'superscript')" class="toolbar-btn"
                        title="Superscript">
                        X²
                    </button>

                    <button type="button" @click="tiptapCommand('header', 'subscript')" class="toolbar-btn"
                        title="Subscript">
                        X₂
                    </button>


                    {{-- CLEAR --}}
                    <button type="button" @click="tiptapCommand('header', 'clear')" class="toolbar-btn"
                        title="Clear formatting">
                        Tx
                    </button>

                </div>


                {{-- EDITOR --}}
                <div x-ref="headerEditor" class="tiptap-editor min-h-[160px] p-5"></div>

            </div>

        </div>

        {{-- FOOTER: TIPTAP --}}
        <div class="mb-5">

            <label class="block text-xs font-medium mb-1.5">
                Footer Dokumen
            </label>

            <div class="overflow-hidden rounded-xl border
                    border-parchment-300
                    bg-white
                    dark:border-slate-warm-700
                    dark:bg-slate-warm-900">

                <div class="flex flex-wrap items-center gap-1
                        border-b border-parchment-300
                        bg-parchment-50
                        p-2
                        dark:border-slate-warm-700
                        dark:bg-slate-warm-800">

                    <button type="button" @click="tiptapCommand('footer', 'undo')" class="toolbar-btn">
                        ↶
                    </button>

                    <button type="button" @click="tiptapCommand('footer', 'redo')" class="toolbar-btn">
                        ↷
                    </button>

                    <span class="toolbar-divider"></span>

                    <button type="button" @click="tiptapCommand('footer', 'bold')" class="toolbar-btn font-bold">
                        B
                    </button>

                    <button type="button" @click="tiptapCommand('footer', 'italic')" class="toolbar-btn italic">
                        I
                    </button>

                    <button type="button" @click="tiptapCommand('footer', 'underline')" class="toolbar-btn underline">
                        U
                    </button>

                    <span class="toolbar-divider"></span>

                    <button type="button" @click="tiptapCommand('footer', 'alignLeft')" class="toolbar-btn">
                        ≡
                    </button>

                    <button type="button" @click="tiptapCommand('footer', 'alignCenter')" class="toolbar-btn">
                        ≡
                    </button>

                    <button type="button" @click="tiptapCommand('footer', 'alignRight')" class="toolbar-btn">
                        ≡
                    </button>

                    <button type="button" @click="tiptapCommand('footer', 'alignJustify')" class="toolbar-btn">
                        ≡
                    </button>

                    <span class="toolbar-divider"></span>

                    <button type="button" @click="tiptapCommand('footer', 'bulletList')" class="toolbar-btn">
                        •
                    </button>

                    <button type="button" @click="tiptapCommand('footer', 'orderedList')" class="toolbar-btn">
                        1.
                    </button>

                    <button type="button" @click="tiptapCommand('footer', 'clear')" class="toolbar-btn">
                        Tx
                    </button>

                </div>


                <div x-ref="footerEditor" class="tiptap-editor min-h-[160px] p-5"></div>

            </div>

        </div>

        <div class="flex justify-end">
            <button type="submit"
                class="inline-flex items-center gap-2 rounded-xl bg-ink-900 px-6 py-3 text-sm font-semibold text-white transition hover:opacity-90 dark:bg-bronze-500 dark:text-ink-900">
                Buat Dokumen <span>→</span>
            </button>
        </div>
    </form>

</div>
<style>
    .tiptap-editor {
        outline: none;
        min-height: 140px;
        font-family: Arial, sans-serif;
        font-size: 14px;
        line-height: 1.7;
    }

    .tiptap-editor:focus {
        outline: none;
    }

    .tiptap-editor p {
        margin: 0 0 8px;
    }

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

    .tiptap-editor ul {
        list-style: disc;
        padding-left: 24px;
    }

    .tiptap-editor ol {
        list-style: decimal;
        padding-left: 24px;
    }

    .tiptap-editor blockquote {
        border-left: 3px solid #94a3b8;
        padding-left: 14px;
        margin: 12px 0;
    }

    .tiptap-editor hr {
        border: 0;
        border-top: 1px solid #94a3b8;
        margin: 16px 0;
    }

    .tiptap-editor img {
        max-width: 100%;
        height: auto;
    }

    .toolbar-btn {
        min-width: 34px;
        height: 34px;
        padding: 0 8px;
        border-radius: 7px;

        display: inline-flex;
        align-items: center;
        justify-content: center;

        font-size: 13px;

        transition: background-color 0.15s ease;
    }

    .toolbar-btn:hover {
        background: #e7e5e4;
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

    .tiptap-editor {
        outline: none;

        font-family: Arial, sans-serif;
        font-size: 14px;
        line-height: 1.7;
    }

    .tiptap-editor p {
        margin: 0 0 8px;
    }

    .tiptap-editor h1 {
        font-size: 2em;
        font-weight: 700;
    }

    .tiptap-editor h2 {
        font-size: 1.5em;
        font-weight: 700;
    }

    .tiptap-editor h3 {
        font-size: 1.25em;
        font-weight: 700;
    }

    .tiptap-editor ul,
    .tiptap-editor ol {
        padding-left: 28px;
    }

    .tiptap-editor blockquote {
        border-left: 3px solid #94a3b8;
        padding-left: 14px;
    }

    .tiptap-editor hr {
        border: 0;
        border-top: 1px solid #94a3b8;
        margin: 18px 0;
    }

    .tiptap-editor a {
        color: #2563eb;
        text-decoration: underline;
    }

    .tiptap-editor img {
        max-width: 100%;
    }

    .tiptap-editor table {
        border-collapse: collapse;
        width: 100%;
    }

    .tiptap-editor th,
    .tiptap-editor td {
        border: 1px solid #94a3b8;
        padding: 8px;
    }
</style>
@endsection