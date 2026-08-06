@extends('layouts.app')

@section('content')
<div class="flex flex-col lg:h-[calc(100vh-105px)] lg:overflow-hidden" x-data="{
    // Active Tab in Component Editor
    activeZone: 'header', // 'header', 'body', 'footer', 'signature'
    
    // Header Component Data
    kopInstansi: 'PT NUSANTARA CITRA MEDIA TBBK',
    kopAlamat: 'Gedung Menara Palma Lt. 18, Jl. H.R. Rasuna Said Blok X-2, Jakarta Selatan 12950',
    kopKontrak: 'Telp: (021) 5290-8888 | Email: sekretariat@ncm-media.co.id | Web: www.ncm-media.co.id',
    nomorSurat: '042/SK-DIR/VIII/2026',
    perihalSurat: 'Penetapan Standar Operasional Prosedur Penyusunan Dokumen Resmi Perusahaan',
    tanggalSurat: '05 Agustus 2026',
    sifatSurat: 'Penting / Rahasia',
    
    // Body Component Data
    tujuanSurat: 'Yth. Para Direktur, Kepala Divisi, dan Manager\nDi Tempat',
    menimbang: 'a. Bahwa untuk menjaga keabsahan dan kerapian dokumen hukum serta tata naskah dinas perusahaan;\nb. Bahwa dengan berlakunya sistem Tanda Tangan Elektronik (TTE) terverifikasi, diperlukan format terstandar.',
    mengingat: '1. Peraturan Direksi No. 01/PER-DIR/2024 tentang Tata Naskah Dinas;\n2. UU No. 11 Tahun 2008 tentang Informasi dan Transaksi Elektronik (ITE).',
    isiPasal1: 'Standar Operasional Prosedur Penyusunan Dokumen Resmi sebagaimana tercantum dalam Lampiran Keputusan ini sah dan mengikat seluruh unit kerja di lingkungan PT Nusantara Citra Media Tbk.',
    isiPasal2: 'Setiap dokumen resmi wajib menggunakan format terstruktur (Header, Body, Footer) serta dilengkapi pembubuhan e-Sign atau stempel digital sah.',
    
    // Footer & Signature Component Data
    kotaTtd: 'Jakarta',
    jabatanPenandatangan: 'Direktur Utama',
    namaPenandatangan: 'Drs. H. Aris Budiman, M.B.A.',
    nipPenandatangan: 'NIP: 19780412 200312 1 002',
    tembusan: '1. Dewan Komisaris\n2. Arsip Hukum (Legal Department)',
    
    // Signature & Materai Options
    selectedMaterai: 'materai10k', // 'none', 'materai10k', 'stempel_basah', 'qr_bsre'
    hasSignature: true,
    signatureX: 65, // % position
    signatureY: 78, // % position
    zoomLevel: 100,
    
    // Page count indicator
    currentPage: 1,
    totalPages: 2
}">

    <!-- Editor Toolbar (pinned, never scrolls) -->
    <div class="shrink-0 mb-3">
        @include('partials.editor.toolbar')
    </div>

    <!-- Main Workspace Split: Left (Component Form Editor) vs Right (Live Realistic Paper Stage) -->
    <div class="grid grid-cols-12 gap-5 lg:min-h-0 lg:flex-1 lg:overflow-hidden">
        
        <!-- LEFT COLUMN: Component Input Editor (5 / 12) -->
        <div class="col-span-12 lg:col-span-5 flex flex-col lg:min-h-0 lg:overflow-hidden">
            
            <!-- Component Zone Selector Tabs (pinned, never scrolls) -->
            <div class="shrink-0 mb-3">
                <div class="flex rounded-xl border border-parchment-300 bg-parchment-100 p-1 dark:border-slate-warm-700 dark:bg-slate-warm-800">
                    <button @click="activeZone = 'header'"
                        :class="activeZone === 'header' ? 'bg-white text-ink-900 shadow-xs font-semibold dark:bg-slate-warm-900 dark:text-parchment-50' : 'text-slate-warm-600 hover:text-ink-900 dark:text-parchment-400'"
                        class="flex-1 py-2 text-[11px] sm:text-xs rounded-lg transition-all flex items-center justify-center gap-1 sm:gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-ink-900 dark:bg-bronze-400 shrink-0"></span>
                        1. Kop Surat
                    </button>
                    <button @click="activeZone = 'body'"
                        :class="activeZone === 'body' ? 'bg-white text-ink-900 shadow-xs font-semibold dark:bg-slate-warm-900 dark:text-parchment-50' : 'text-slate-warm-600 hover:text-ink-900 dark:text-parchment-400'"
                        class="flex-1 py-2 text-[11px] sm:text-xs rounded-lg transition-all flex items-center justify-center gap-1 sm:gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-bronze-600 shrink-0"></span>
                        2. Isi Dokumen
                    </button>
                    <button @click="activeZone = 'footer'"
                        :class="activeZone === 'footer' ? 'bg-white text-ink-900 shadow-xs font-semibold dark:bg-slate-warm-900 dark:text-parchment-50' : 'text-slate-warm-600 hover:text-ink-900 dark:text-parchment-400'"
                        class="flex-1 py-2 text-[11px] sm:text-xs rounded-lg transition-all flex items-center justify-center gap-1 sm:gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-seal-700 shrink-0"></span>
                        3. Legalese & TTD
                    </button>
                </div>
            </div>

            <!-- Scrollable form content (only this part scrolls) -->
            <div class="flex-1 lg:overflow-y-auto custom-scrollbar lg:pr-2 space-y-4 pb-8">
                <!-- TAB 1: KOP SURAT / HEADER COMPONENT EDITOR -->
                @include('partials.editor.header-form')

                <!-- TAB 2: ISI DOKUMEN / BODY COMPONENT EDITOR -->
                @include('partials.editor.body-form')

                <!-- TAB 3: FOOTER & SIGNATURE COMPONENT EDITOR -->
                @include('partials.editor.footer-form')
            </div>
        </div>

        <!-- RIGHT COLUMN: Real-Time Multi-Page Realistic A4 Paper Preview Stage (7 / 12) -->
        @include('partials.editor.paper-preview')

    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        initDocumentEditor('#editor-pasal1');
        initDocumentEditor('#editor-pasal2');
    });
</script>
@endpush
@endsection
