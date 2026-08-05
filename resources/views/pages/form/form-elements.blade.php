@extends('layouts.app')

@section('content')
<div x-data="{
    selectedCategory: 'all',
    
    templates: [
        {
            title: 'Surat Keputusan (SK Direksi)',
            code: 'TPL-SK-01',
            category: 'sk',
            categoryLabel: 'Surat Keputusan',
            description: 'Format terstruktur untuk pembentukan panitia, penetapan SOP, atau kebijakan resmi perusahan.',
            badge: 'Paling Populer',
            sections: ['Kop Resmi', 'Konsideran (Menimbang & Mengingat)', 'Diktum (Memutuskan)', 'Blok TTD & e-Materai']
        },
        {
            title: 'Internal Memorandum (Memo)',
            code: 'TPL-MEMO-02',
            category: 'memo',
            categoryLabel: 'Memorandum',
            description: 'Format komunikasi internal antar direksi, divisi, dan cabang kerja perusahaan.',
            badge: 'Standar Cepat',
            sections: ['Header Compact', 'Poin Perihal & Lampiran', 'Isi Instruksi Kerja', 'Catatan & TTD']
        },
        {
            title: 'Perjanjian Kerja Sama (PKS)',
            code: 'TPL-PKS-03',
            category: 'pks',
            categoryLabel: 'Kontrak / PKS',
            description: 'Format Perjanjian Kerja Sama antara dua pihak lengkap dengan klausul & pasal hukum.',
            badge: 'Resmi Legal',
            sections: ['Judul Kontrak', 'Identitas Para Pihak', 'Pasal-Pasal Perjanjian', 'Dual TTD & Materai']
        },
        {
            title: 'Berita Acara Serah Terima (BAST)',
            code: 'TPL-BAST-04',
            category: 'bast',
            categoryLabel: 'Berita Acara',
            description: 'Dokumen bukti penyerahan hasil kerja, inventaris kantor, atau penyelesaian proyek.',
            badge: 'Standar GA/IT',
            sections: ['Header BAST', 'Daftar Barang / Rincian', 'Pernyataan Penyerahan', 'Tanda Tangan 2 Pihak']
        },
        {
            title: 'Surat Perintah Kerja (SPK)',
            code: 'TPL-SPK-05',
            category: 'spk',
            categoryLabel: 'Surat Perintah',
            description: 'Surat penugasan resmi untuk vendor, kontraktor, atau tim pelaksana lapangan.',
            badge: 'Operational',
            sections: ['Kop Instansi', 'Rincian Penugasan & Bobot', 'Batas Waktu', 'Pengesahan Pejabat']
        },
        {
            title: 'Surat Keterangan Resmi',
            code: 'TPL-KET-06',
            category: 'ket',
            categoryLabel: 'Surat Keterangan',
            description: 'Surat keterangan kerja, keaktifan pegawai, atau pengesahan dokumen resmi.',
            badge: 'HR / Personalia',
            sections: ['Kop Resmi', 'Pernyataan Keterangan', 'Maksud & Tujuan', 'Stempel Basah / QR']
        }
    ]
}">

    <!-- Page Header Bar -->
    <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="font-serif font-bold text-xl text-ink-900 dark:text-parchment-50">
                Galeri Template Dokumen Resmi
            </h1>
            <p class="text-xs text-slate-warm-500 dark:text-parchment-400 mt-1">
                Pilih format terstruktur siap pakai yang memenuhi standar tata naskah dinas & verifikasi digital.
            </p>
        </div>
    </div>

    <!-- Category Filter Tabs -->
    <div class="mb-6 flex items-center gap-2 overflow-x-auto no-scrollbar pb-2">
        <button @click="selectedCategory = 'all'" :class="selectedCategory === 'all' ? 'bg-ink-900 text-white font-semibold' : 'bg-white text-slate-warm-600 border border-parchment-300 dark:bg-slate-warm-900 dark:border-slate-warm-800 dark:text-parchment-300'" class="px-4 py-2 rounded-lg text-xs transition-all shrink-0">
            Semua Template
        </button>
        <button @click="selectedCategory = 'sk'" :class="selectedCategory === 'sk' ? 'bg-ink-900 text-white font-semibold' : 'bg-white text-slate-warm-600 border border-parchment-300 dark:bg-slate-warm-900 dark:border-slate-warm-800 dark:text-parchment-300'" class="px-4 py-2 rounded-lg text-xs transition-all shrink-0">
            Surat Keputusan
        </button>
        <button @click="selectedCategory = 'memo'" :class="selectedCategory === 'memo' ? 'bg-ink-900 text-white font-semibold' : 'bg-white text-slate-warm-600 border border-parchment-300 dark:bg-slate-warm-900 dark:border-slate-warm-800 dark:text-parchment-300'" class="px-4 py-2 rounded-lg text-xs transition-all shrink-0">
            Memorandum
        </button>
        <button @click="selectedCategory = 'pks'" :class="selectedCategory === 'pks' ? 'bg-ink-900 text-white font-semibold' : 'bg-white text-slate-warm-600 border border-parchment-300 dark:bg-slate-warm-900 dark:border-slate-warm-800 dark:text-parchment-300'" class="px-4 py-2 rounded-lg text-xs transition-all shrink-0">
            Kontrak / PKS
        </button>
        <button @click="selectedCategory = 'bast'" :class="selectedCategory === 'bast' ? 'bg-ink-900 text-white font-semibold' : 'bg-white text-slate-warm-600 border border-parchment-300 dark:bg-slate-warm-900 dark:border-slate-warm-800 dark:text-parchment-300'" class="px-4 py-2 rounded-lg text-xs transition-all shrink-0">
            Berita Acara
        </button>
    </div>

    <!-- Template Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">
        <template x-for="tpl in templates.filter(t => selectedCategory === 'all' || t.category === selectedCategory)" :key="tpl.code">
            <div class="template-card flex flex-col justify-between p-5">
                <div>
                    <!-- Header Pill & Code -->
                    <div class="flex items-center justify-between mb-3">
                        <span class="px-2.5 py-0.5 rounded text-[10px] font-mono font-semibold bg-bronze-100 text-bronze-800 dark:bg-bronze-900 dark:text-bronze-300" x-text="tpl.badge"></span>
                        <span class="text-[10px] font-mono text-slate-warm-400" x-text="tpl.code"></span>
                    </div>

                    <!-- Mini Visual Paper Skeleton -->
                    <div class="template-card-preview rounded p-4 mb-4 flex flex-col justify-between shadow-xs">
                        <div class="w-full text-center border-b border-parchment-300 pb-2">
                            <div class="w-16 h-2 mx-auto rounded bg-ink-900 mb-1"></div>
                            <div class="w-24 h-1.5 mx-auto rounded bg-parchment-300"></div>
                        </div>
                        <div class="space-y-1.5 my-3">
                            <div class="w-full h-1.5 rounded bg-parchment-300"></div>
                            <div class="w-4/5 h-1.5 rounded bg-parchment-300"></div>
                            <div class="w-2/3 h-1.5 rounded bg-parchment-300"></div>
                        </div>
                        <div class="flex justify-end pt-2 border-t border-parchment-200">
                            <div class="w-10 h-6 rounded border border-seal-300 bg-seal-50"></div>
                        </div>
                    </div>

                    <!-- Title & Description -->
                    <h3 class="font-serif font-bold text-base text-ink-900 dark:text-parchment-100 mb-2" x-text="tpl.title"></h3>
                    <p class="text-xs text-slate-warm-500 dark:text-parchment-400 mb-4 leading-relaxed" x-text="tpl.description"></p>

                    <!-- Section Badges -->
                    <div class="space-y-1 mb-5">
                        <span class="text-[10px] font-mono font-semibold uppercase text-slate-warm-400 block">Struktur Komponen:</span>
                        <div class="flex flex-wrap gap-1">
                            <template x-for="sec in tpl.sections" :key="sec">
                                <span class="px-2 py-0.5 rounded bg-parchment-100 text-[10px] font-sans text-ink-800 dark:bg-slate-warm-800 dark:text-parchment-300" x-text="sec"></span>
                            </template>
                        </div>
                    </div>
                </div>

                <!-- Action Button -->
                <a href="/" class="btn-primary w-full text-xs text-center py-2.5">
                    Gunakan Template Ini →
                </a>
            </div>
        </template>
    </div>

</div>
@endsection
