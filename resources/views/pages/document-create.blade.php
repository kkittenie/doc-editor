@extends('layouts.app')

@section('content')
<div x-data="{
    loadingTemplate: null,
    isUploadingLogo: false,
    headerHtml: '',
    footerHtml: '',
    bodyHtml: '',

    init() {
        this.$nextTick(() => this.initEditors());
    },

    initEditors() {
        if (typeof window.initDocumentEditor === 'function') {
            window.initDocumentEditor('#header-editor');
            window.initDocumentEditor('#footer-editor', '', false);
        }
    },

    setHeaderContent(content) {
        this.headerHtml = content;
        const editor = window.tinymce?.get('header-editor');

        if (editor) editor.setContent(content);
    },

    setFooterContent(content) {
        this.footerHtml = content;
        const editor = window.tinymce?.get('footer-editor');

        if (editor) editor.setContent(content);
    },

    async loadTemplate(key) {
        this.loadingTemplate = key;
        try {
            const res = await window.axios.get('/documents/template/' + key);
            const t = res.data;

            this.setHeaderContent('<p style=\'text-align:center;font-weight:bold;text-transform:uppercase;\'>' + t.header_data.kopInstansi + '</p>'
                + '<p style=\'text-align:center;font-size:12px;\'>' + t.header_data.kopAlamat + '</p>'
                + '<p style=\'text-align:center;font-size:11px;\'>' + t.header_data.kopKontrak + '</p>');

            document.getElementById('header-nomor').value = t.header_data.nomorSurat;
            document.getElementById('header-judul').value = t.title;

            this.setFooterContent('<p>Sifat: ' + t.header_data.sifatSurat + '</p>');

            const b = t.body_content;
            this.bodyHtml = '<p>' + b.tujuanSurat + '</p>'
                + '<p><strong>MENIMBANG:</strong><br>' + b.menimbang.replace(/\n/g, '<br>') + '</p>'
                + '<p><strong>MENGINGAT:</strong><br>' + b.mengingat.replace(/\n/g, '<br>') + '</p>'
                + '<p>' + b.isiPasal1.replace(/\n/g, '<br>') + '</p>'
                + '<p>' + b.isiPasal2.replace(/\n/g, '<br>') + '</p>';
        } catch (e) {
            Swal.fire({ icon: 'error', title: 'Gagal', text: 'Gagal memuat template.', confirmButtonColor: '#1B2A4A' });
            console.error(e);
        } finally {
            this.loadingTemplate = null;
        }
    },

    formatArea(targetId, command, value = null) {
        const el = document.getElementById(targetId);
        if (!el) return;
        el.focus();
        document.execCommand(command, false, value);
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
        const headerEditor = window.tinymce?.get('header-editor');
        const footerEditor = window.tinymce?.get('footer-editor');
        document.getElementById('header-content-input').value = headerEditor
            ? headerEditor.getContent()
            : document.getElementById('header-editor').value;
        document.getElementById('footer-content-input').value = footerEditor
            ? footerEditor.getContent()
            : document.getElementById('footer-editor').value;
    }
}" class="max-w-4xl mx-auto py-8">

    <div class="mb-6">
        <h1 class="text-2xl font-bold text-ink-900 dark:text-parchment-50">Buat Dokumen Baru</h1>
        <p class="mt-2 text-sm text-slate-warm-600 dark:text-parchment-400">
            Susun kop surat dan footer bebas seperti menulis di Word, atau mulai cepat dari template.
        </p>
    </div>

    {{-- Quick Templates --}}
    <div class="mb-6 rounded-2xl border border-parchment-300 bg-parchment-50 p-5 dark:border-slate-warm-700 dark:bg-slate-warm-800">
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
        <div class="rounded-2xl border border-parchment-300 bg-white p-5 shadow-sm dark:border-slate-warm-700 dark:bg-slate-warm-900 mb-5">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium mb-1.5">Judul Dokumen</label>
                    <input type="text" id="header-judul" name="title" required placeholder="Contoh: Surat Keputusan Direksi"
                        class="w-full rounded-xl border border-parchment-300 bg-white px-4 py-2.5 text-sm outline-none focus:border-bronze-500 dark:border-slate-warm-700 dark:bg-slate-warm-800">
                </div>
                <div>
                    <label class="block text-xs font-medium mb-1.5">Nomor Dokumen</label>
                    <input type="text" id="header-nomor" name="header_data[nomorSurat]" required placeholder="001/SK-DIR/VIII/2026"
                        class="w-full rounded-xl border border-parchment-300 bg-white px-4 py-2.5 text-sm outline-none focus:border-bronze-500 dark:border-slate-warm-700 dark:bg-slate-warm-800">
                </div>
            </div>
        </div>

        {{-- HEADER: TinyMCE --}}
        <div class="mb-5">
            <label class="block text-xs font-medium mb-1.5">Header / Kop Surat</label>
            <textarea id="header-editor" spellcheck="true"
                class="w-full min-h-[140px] rounded-b-xl border border-parchment-300 bg-white p-5 text-sm outline-none focus:border-bronze-500 dark:border-slate-warm-700 dark:bg-slate-warm-800"></textarea>
            </div>

        {{-- FOOTER: contenteditable + toolbar --}}
        <div class="mb-5">
            <label class="block text-xs font-medium mb-1.5">Footer Dokumen</label>
            <textarea id="footer-editor" spellcheck="true"
                class="w-full min-h-[140px] rounded-b-xl border border-parchment-300 bg-white p-5 text-sm outline-none focus:border-bronze-500 dark:border-slate-warm-700 dark:bg-slate-warm-800"></textarea>
        </div>

        <div class="flex justify-end">
            <button type="submit"
                class="inline-flex items-center gap-2 rounded-xl bg-ink-900 px-6 py-3 text-sm font-semibold text-white transition hover:opacity-90 dark:bg-bronze-500 dark:text-ink-900">
                Buat Dokumen <span>→</span>
            </button>
        </div>
    </form>

</div>
@endsection
