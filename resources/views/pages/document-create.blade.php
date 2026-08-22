@extends('layouts.app')

@section('content')

<div
    x-data="{
        loadingTemplate: null,

        async loadTemplate(key) {
            this.loadingTemplate = key;

            try {
                const res = await window.axios.get('/documents/template/' + key);
                const t = res.data;

                if (t.title) {
                    const titleInput = document.getElementById('header-judul');
                    if (titleInput) titleInput.value = t.title;
                }

                if (t.header_data?.nomorSurat) {
                    const nomorInput = document.getElementById('header-nomor');
                    if (nomorInput) nomorInput.value = t.header_data.nomorSurat;
                }

                Swal.fire({
                    icon: 'success',
                    title: 'Template dimuat',
                    text: 'Judul & nomor dokumen sudah diisi otomatis.',
                    confirmButtonColor: '#1B2A4A',
                    timer: 1200,
                    showConfirmButton: false,
                });

            } catch (e) {
                console.error(e);
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: 'Gagal memuat template.',
                    confirmButtonColor: '#1B2A4A',
                });
            } finally {
                this.loadingTemplate = null;
            }
        },
    }"
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
            Isi judul & nomor dokumen, lalu pilih template. Kop surat, isi, dan footer
            ditulis bebas nanti di halaman editor.
        </p>
    </div>

    <form action="{{ route('documents.store') }}" method="POST">

        @csrf

        {{-- Kop surat & footer ditulis manual di halaman editor,
             jadi selalu dikirim kosong dari sini. --}}
        <input type="hidden" name="header_data[content]" value="<p></p>">
        <input type="hidden" name="footer_data[content]" value="<p></p>">

        {{-- ========================================== --}}
        {{-- JUDUL & NOMOR --}}
        {{-- ========================================== --}}

        <div class="rounded-2xl border border-parchment-300 bg-white p-5 shadow-sm dark:border-slate-warm-700 dark:bg-slate-warm-900 mb-6">

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

        {{-- ========================================== --}}
        {{-- 4 TEMPLATE --}}
        {{-- ========================================== --}}

        <div class="mb-6">

            <h2 class="font-serif font-bold text-lg text-ink-900 dark:text-parchment-50 mb-1">
                Pilih Template Dokumen
            </h2>
            <p class="text-xs text-slate-warm-500 dark:text-parchment-400 mb-4">
                Klik template untuk mengisi judul & nomor otomatis, lalu tekan tombol simpan.
            </p>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

                {{-- TEMPLATE 1: PERJANJIAN / PKS --}}
                <div class="template-card flex flex-col justify-between p-5 border border-parchment-300 bg-white rounded-2xl shadow-sm dark:border-slate-warm-700 dark:bg-slate-warm-900">
                    <div>
                        <div class="flex items-center justify-between mb-3">
                            <span class="px-2.5 py-0.5 rounded text-[10px] font-mono font-semibold bg-bronze-100 text-bronze-800 dark:bg-bronze-900 dark:text-bronze-300">
                                Perjanjian / PKS
                            </span>
                            <span class="text-[10px] font-mono text-slate-warm-400">TPL-PKS-01</span>
                        </div>

                        <div class="template-card-preview rounded p-4 mb-4 flex flex-col justify-center items-center shadow-xs min-h-[120px] bg-parchment-50 dark:bg-slate-warm-800">
                            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="text-parchment-300 mb-2">
                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                                <path d="M14 2v6h6"/>
                            </svg>
                            <span class="text-[10px] text-slate-warm-400">Perjanjian Kerja Sama</span>
                        </div>

                        <h3 class="font-serif font-bold text-base text-ink-900 dark:text-parchment-100 mb-2">
                            Perjanjian Kerja Sama
                        </h3>
                        <p class="text-xs text-slate-warm-500 dark:text-parchment-400 mb-4 leading-relaxed">
                            Template perjanjian kerja sama antar pihak, lengkap dengan pasal-pasal.
                        </p>
                    </div>

                    <button type="button" @click="loadTemplate('perjanjian-kerja-sama')" :disabled="loadingTemplate"
                        class="btn-primary w-full text-xs text-center py-2.5">
                        <span x-text="loadingTemplate === 'perjanjian-kerja-sama' ? 'Memuat...' : 'Gunakan Template Ini →'"></span>
                    </button>
                </div>

                {{-- TEMPLATE 2: KONTRAK KERJA --}}
                <div class="template-card flex flex-col justify-between p-5 border border-parchment-300 bg-white rounded-2xl shadow-sm dark:border-slate-warm-700 dark:bg-slate-warm-900">
                    <div>
                        <div class="flex items-center justify-between mb-3">
                            <span class="px-2.5 py-0.5 rounded text-[10px] font-mono font-semibold bg-bronze-100 text-bronze-800 dark:bg-bronze-900 dark:text-bronze-300">
                                Kontrak Kerja
                            </span>
                            <span class="text-[10px] font-mono text-slate-warm-400">TPL-KK-02</span>
                        </div>

                        <div class="template-card-preview rounded p-4 mb-4 flex flex-col justify-center items-center shadow-xs min-h-[120px] bg-parchment-50 dark:bg-slate-warm-800">
                            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="text-parchment-300 mb-2">
                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                                <path d="M14 2v6h6"/>
                            </svg>
                            <span class="text-[10px] text-slate-warm-400">Kontrak Kerja</span>
                        </div>

                        <h3 class="font-serif font-bold text-base text-ink-900 dark:text-parchment-100 mb-2">
                            Kontrak Kerja
                        </h3>
                        <p class="text-xs text-slate-warm-500 dark:text-parchment-400 mb-4 leading-relaxed">
                            Template perjanjian kerja antara perusahaan dan pekerja.
                        </p>
                    </div>

                    <button type="button" @click="loadTemplate('kontrak-kerja')" :disabled="loadingTemplate"
                        class="btn-primary w-full text-xs text-center py-2.5">
                        <span x-text="loadingTemplate === 'kontrak-kerja' ? 'Memuat...' : 'Gunakan Template Ini →'"></span>
                    </button>
                </div>

                {{-- TEMPLATE 3: SURAT KUASA --}}
                <div class="template-card flex flex-col justify-between p-5 border border-parchment-300 bg-white rounded-2xl shadow-sm dark:border-slate-warm-700 dark:bg-slate-warm-900">
                    <div>
                        <div class="flex items-center justify-between mb-3">
                            <span class="px-2.5 py-0.5 rounded text-[10px] font-mono font-semibold bg-bronze-100 text-bronze-800 dark:bg-bronze-900 dark:text-bronze-300">
                                Surat Kuasa
                            </span>
                            <span class="text-[10px] font-mono text-slate-warm-400">TPL-SK-03</span>
                        </div>

                        <div class="template-card-preview rounded p-4 mb-4 flex flex-col justify-center items-center shadow-xs min-h-[120px] bg-parchment-50 dark:bg-slate-warm-800">
                            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="text-parchment-300 mb-2">
                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                                <path d="M14 2v6h6"/>
                            </svg>
                            <span class="text-[10px] text-slate-warm-400">Surat Kuasa</span>
                        </div>

                        <h3 class="font-serif font-bold text-base text-ink-900 dark:text-parchment-100 mb-2">
                            Surat Kuasa
                        </h3>
                        <p class="text-xs text-slate-warm-500 dark:text-parchment-400 mb-4 leading-relaxed">
                            Template surat kuasa dengan ruang lingkup dan masa berlaku.
                        </p>
                    </div>

                    <button type="button" @click="loadTemplate('surat-kuasa')" :disabled="loadingTemplate"
                        class="btn-primary w-full text-xs text-center py-2.5">
                        <span x-text="loadingTemplate === 'surat-kuasa' ? 'Memuat...' : 'Gunakan Template Ini →'"></span>
                    </button>
                </div>

                {{-- TEMPLATE 4: SURAT PERNYATAAN --}}
                <div class="template-card flex flex-col justify-between p-5 border border-parchment-300 bg-white rounded-2xl shadow-sm dark:border-slate-warm-700 dark:bg-slate-warm-900">
                    <div>
                        <div class="flex items-center justify-between mb-3">
                            <span class="px-2.5 py-0.5 rounded text-[10px] font-mono font-semibold bg-bronze-100 text-bronze-800 dark:bg-bronze-900 dark:text-bronze-300">
                                Surat Pernyataan
                            </span>
                            <span class="text-[10px] font-mono text-slate-warm-400">TPL-SP-04</span>
                        </div>

                        <div class="template-card-preview rounded p-4 mb-4 flex flex-col justify-center items-center shadow-xs min-h-[120px] bg-parchment-50 dark:bg-slate-warm-800">
                            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="text-parchment-300 mb-2">
                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                                <path d="M14 2v6h6"/>
                            </svg>
                            <span class="text-[10px] text-slate-warm-400">Surat Pernyataan</span>
                        </div>

                        <h3 class="font-serif font-bold text-base text-ink-900 dark:text-parchment-100 mb-2">
                            Surat Pernyataan
                        </h3>
                        <p class="text-xs text-slate-warm-500 dark:text-parchment-400 mb-4 leading-relaxed">
                            Template surat pernyataan dengan isi dan ketentuan.
                        </p>
                    </div>

                    <button type="button" @click="loadTemplate('surat-pernyataan')" :disabled="loadingTemplate"
                        class="btn-primary w-full text-xs text-center py-2.5">
                        <span x-text="loadingTemplate === 'surat-pernyataan' ? 'Memuat...' : 'Gunakan Template Ini →'"></span>
                    </button>
                </div>

            </div>

        </div>

        {{-- ========================================== --}}
        {{-- TOMBOL SIMPAN --}}
        {{-- ========================================== --}}

        <div class="flex justify-end">
            <button type="submit" class="btn-primary px-8 py-3 text-sm">
                Simpan & Lanjut ke Editor →
            </button>
        </div>

    </form>

</div>
@endsection