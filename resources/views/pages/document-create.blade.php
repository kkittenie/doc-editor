@extends('layouts.app')

@section('content')
<div x-data="{
    loadingTemplate: null,
    isUploadingLogo: false,
    headerHtml: '',
    footerHtml: '',
    bodyHtml: '',

    async loadTemplate(key) {
    this.loadingTemplate = key;
    try {
        const res = await window.axios.get('/documents/template/' + key);
        const t = res.data;

        this.headerHtml = '<p style=\'text-align:center;font-weight:bold;text-transform:uppercase;\'>' + t.header_data.kopInstansi + '</p>'
            + '<p style=\'text-align:center;font-size:12px;\'>' + t.header_data.kopAlamat + '</p>'
            + '<p style=\'text-align:center;font-size:11px;\'>' + t.header_data.kopKontrak + '</p>';

        document.getElementById('header-nomor').value = t.header_data.nomorSurat;
        document.getElementById('header-judul').value = t.title;

        this.footerHtml = '<p>Sifat: ' + t.header_data.sifatSurat + '</p>';

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
            document.execCommand('insertHTML', false, '<img src=\'' + res.data.url + '\' style=\'max-height:70px;display:block;margin:0 auto 8px;\'>');
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

    syncBeforeSubmit() {
        document.getElementById('header-content-input').value = document.getElementById('header-editor').innerHTML;
        document.getElementById('footer-content-input').value = document.getElementById('footer-editor').innerHTML;
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

        {{-- Judul & Nomor (compact, bekas posisi logo) --}}
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

        {{-- HEADER: contenteditable + toolbar --}}
        <div class="mb-5">
            <label class="block text-xs font-medium mb-1.5">Header / Kop Surat</label>
            <div class="flex flex-wrap items-center gap-1 rounded-t-xl border border-b-0 border-parchment-300 bg-parchment-50 p-2 dark:border-slate-warm-700 dark:bg-slate-warm-800">
                <button type="button" @click="formatArea('header-editor', 'bold')" class="px-2.5 py-1.5 rounded-md text-xs font-bold hover:bg-white dark:hover:bg-slate-warm-700">B</button>
                <button type="button" @click="formatArea('header-editor', 'italic')" class="px-2.5 py-1.5 rounded-md text-xs italic hover:bg-white dark:hover:bg-slate-warm-700">I</button>
                <button type="button" @click="formatArea('header-editor', 'underline')" class="px-2.5 py-1.5 rounded-md text-xs underline hover:bg-white dark:hover:bg-slate-warm-700">U</button>
                <div class="w-px h-5 bg-parchment-300 dark:bg-slate-warm-600 mx-1"></div>
                <button type="button" @click="formatArea('header-editor', 'justifyLeft')" class="px-2.5 py-1.5 rounded-md text-xs hover:bg-white dark:hover:bg-slate-warm-700">Kiri</button>
                <button type="button" @click="formatArea('header-editor', 'justifyCenter')" class="px-2.5 py-1.5 rounded-md text-xs hover:bg-white dark:hover:bg-slate-warm-700">Tengah</button>
                <button type="button" @click="formatArea('header-editor', 'justifyRight')" class="px-2.5 py-1.5 rounded-md text-xs hover:bg-white dark:hover:bg-slate-warm-700">Kanan</button>
                <div class="w-px h-5 bg-parchment-300 dark:bg-slate-warm-600 mx-1"></div>
                <button type="button" @click="$refs.logoInput.click()" :disabled="isUploadingLogo" class="px-2.5 py-1.5 rounded-md text-xs hover:bg-white dark:hover:bg-slate-warm-700">
                    <span x-text="isUploadingLogo ? 'Mengunggah...' : '🖼 Sisipkan Logo'"></span>
                </button>
                <input type="file" x-ref="logoInput" accept="image/png,image/jpeg,image/svg+xml" class="hidden" @change="uploadLogo($event)">
            </div>
            <div id="header-editor" contenteditable="true" spellcheck="true"
                class="min-h-[140px] rounded-b-xl border border-parchment-300 bg-white p-5 text-sm outline-none focus:border-bronze-500 dark:border-slate-warm-700 dark:bg-slate-warm-800"
                x-html="headerHtml"></div>
        </div>

        {{-- FOOTER: contenteditable + toolbar --}}
        <div class="mb-5">
            <label class="block text-xs font-medium mb-1.5">Footer Dokumen</label>
            <div class="flex flex-wrap items-center gap-1 rounded-t-xl border border-b-0 border-parchment-300 bg-parchment-50 p-2 dark:border-slate-warm-700 dark:bg-slate-warm-800">
                <button type="button" @click="formatArea('footer-editor', 'bold')" class="px-2.5 py-1.5 rounded-md text-xs font-bold hover:bg-white dark:hover:bg-slate-warm-700">B</button>
                <button type="button" @click="formatArea('footer-editor', 'italic')" class="px-2.5 py-1.5 rounded-md text-xs italic hover:bg-white dark:hover:bg-slate-warm-700">I</button>
                <button type="button" @click="formatArea('footer-editor', 'underline')" class="px-2.5 py-1.5 rounded-md text-xs underline hover:bg-white dark:hover:bg-slate-warm-700">U</button>
                <div class="w-px h-5 bg-parchment-300 dark:bg-slate-warm-600 mx-1"></div>
                <button type="button" @click="formatArea('footer-editor', 'justifyLeft')" class="px-2.5 py-1.5 rounded-md text-xs hover:bg-white dark:hover:bg-slate-warm-700">Kiri</button>
                <button type="button" @click="formatArea('footer-editor', 'justifyCenter')" class="px-2.5 py-1.5 rounded-md text-xs hover:bg-white dark:hover:bg-slate-warm-700">Tengah</button>
                <button type="button" @click="formatArea('footer-editor', 'justifyRight')" class="px-2.5 py-1.5 rounded-md text-xs hover:bg-white dark:hover:bg-slate-warm-700">Kanan</button>
            </div>
            <div id="footer-editor" contenteditable="true" spellcheck="true"
                class="min-h-[100px] rounded-b-xl border border-parchment-300 bg-white p-5 text-sm outline-none focus:border-bronze-500 dark:border-slate-warm-700 dark:bg-slate-warm-800"
                x-html="footerHtml"></div>
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