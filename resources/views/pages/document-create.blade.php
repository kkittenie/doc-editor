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
            Isi judul & nomor dokumen, atau pakai template cepat di bawah. Kop surat, isi,
            dan footer ditulis bebas nanti di halaman editor.
        </p>
    </div>


    {{-- ========================================== --}}
    {{-- QUICK TEMPLATE (persis kayak sebelumnya, gak diubah) --}}
    {{-- ========================================== --}}

    <div class="mb-6 rounded-2xl border border-parchment-300 bg-parchment-50 p-5 dark:border-slate-warm-700 dark:bg-slate-warm-800">

        <h2 class="text-sm font-semibold text-ink-900 dark:text-parchment-50 mb-3">
            Mulai Cepat dari Template
        </h2>

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


    <form action="{{ route('documents.store') }}" method="POST">

        @csrf

        {{-- Kop surat & footer sekarang ditulis manual di halaman editor,
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
        {{-- GALERI TEMPLATE (pengganti kotak Header/Footer lama) --}}
        {{-- ========================================== --}}

        <div class="mb-6">

            <h2 class="font-serif font-bold text-lg text-ink-900 dark:text-parchment-50 mb-1">
                Pilih Format Dokumen
            </h2>
            <p class="text-xs text-slate-warm-500 dark:text-parchment-400 mb-4">
                Kop surat & footer ditulis langsung di halaman editor, bukan di sini.
            </p>

            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">

                {{-- Baru ada 1 template buat sekarang: Dokumen Kosong.
                     Tinggal duplikasi <div class="template-card"> ini kalau
                     mau nambah pilihan lain nanti. --}}
                <div class="template-card flex flex-col justify-between p-5 border-2 border-bronze-400 ring-2 ring-bronze-100 dark:ring-bronze-900">

                    <div>
                        <div class="flex items-center justify-between mb-3">
                            <span class="px-2.5 py-0.5 rounded text-[10px] font-mono font-semibold bg-bronze-100 text-bronze-800 dark:bg-bronze-900 dark:text-bronze-300">
                                Paling Sering Dipakai
                            </span>
                            <span class="text-[10px] font-mono text-slate-warm-400">TPL-BLANK-00</span>
                        </div>

                        {{-- Mini Visual Paper Skeleton (kosong, gak ada isi) --}}
                        <div class="template-card-preview rounded p-4 mb-4 flex flex-col justify-center items-center shadow-xs min-h-[120px]">
                            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="text-parchment-300 mb-2">
                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                                <path d="M14 2v6h6"/>
                            </svg>
                            <span class="text-[10px] text-slate-warm-400">Halaman kosong</span>
                        </div>

                        <h3 class="font-serif font-bold text-base text-ink-900 dark:text-parchment-100 mb-2">
                            Dokumen Kosong
                        </h3>
                        <p class="text-xs text-slate-warm-500 dark:text-parchment-400 mb-4 leading-relaxed">
                            Mulai dari halaman kosong dan tulis sesuka hati — kop surat, isi, sampai footer, semuanya bebas diatur nanti di halaman editor.
                        </p>
                    </div>

                    <button type="submit" class="btn-primary w-full text-xs text-center py-2.5">
                        Gunakan Template Ini →
                    </button>

                </div>

            </div>

        </div>

    </form>

</div>
@endsection