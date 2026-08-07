@extends('layouts.app')

@section('content')
<div x-data="{
    // Active Tab in Component Editor
    activeZone: 'header',

    // Document Save State
    documentId: @js($document->id ?? null),
    saveStatus: 'idle',

    async saveDocument() {
        this.saveStatus = 'saving';
        const payload = {
            title: this.perihalSurat || 'Dokumen Tanpa Judul',
            type: 'umum',
            header_data: {
                kopInstansi: this.kopInstansi,
                kopAlamat: this.kopAlamat,
                kopKontrak: this.kopKontrak,
                nomorSurat: this.nomorSurat,
                perihalSurat: this.perihalSurat,
                tanggalSurat: this.tanggalSurat,
                sifatSurat: this.sifatSurat,
            },
            body_content: {
                tujuanSurat: this.tujuanSurat,
                menimbang: this.menimbang,
                mengingat: this.mengingat,
                isiPasal1: document.querySelector('#editor-pasal1')?.value ?? this.isiPasal1,
                isiPasal2: document.querySelector('#editor-pasal2')?.value ?? this.isiPasal2,
            },
            footer_data: {
                kotaTtd: this.kotaTtd,
                jabatanPenandatangan: this.jabatanPenandatangan,
                namaPenandatangan: this.namaPenandatangan,
                nipPenandatangan: this.nipPenandatangan,
                tembusan: this.tembusan,
            },
            signature_data: {
                selectedMaterai: this.selectedMaterai,
                signatureX: this.signatureX,
                signatureY: this.signatureY,
            },
            status: 'draft',
        };

        try {
            if (this.documentId) {
                await window.axios.put(`/documents/${this.documentId}`, payload);
            } else {
                const res = await window.axios.post('/documents', payload);
                this.documentId = res.data.id;
                window.history.replaceState({}, '', `/documents/${this.documentId}/edit`);
            }
            this.saveStatus = 'saved';
        } catch (e) {
            this.saveStatus = 'error';
            console.error(e);
        }
    },
    
    // Header Component Data
    kopInstansi: @js($document->header_data['kopInstansi'] ?? 'PT NUSANTARA CITRA MEDIA TBBK'),
    kopAlamat: @js($document->header_data['kopAlamat'] ?? 'Gedung Menara Palma Lt. 18, Jl. H.R. Rasuna Said Blok X-2, Jakarta Selatan 12950'),
    kopKontrak: @js($document->header_data['kopKontrak'] ?? 'Telp: (021) 5290-8888 | Email: sekretariat@ncm-media.co.id | Web: www.ncm-media.co.id'),
    nomorSurat: @js($document->header_data['nomorSurat'] ?? '042/SK-DIR/VIII/2026'),
    perihalSurat: @js($document->header_data['perihalSurat'] ?? 'Penetapan Standar Operasional Prosedur Penyusunan Dokumen Resmi Perusahaan'),
    tanggalSurat: @js($document->header_data['tanggalSurat'] ?? '05 Agustus 2026'),
    sifatSurat: @js($document->header_data['sifatSurat'] ?? 'Penting / Rahasia'),
    
    // Body Component Data
    tujuanSurat: @js($document->body_content['tujuanSurat'] ?? 'Yth. Para Direktur, Kepala Divisi, dan Manager\\nDi Tempat'),
    menimbang: @js($document->body_content['menimbang'] ?? 'a. Bahwa untuk menjaga keabsahan dan kerapian dokumen hukum serta tata naskah dinas perusahaan;\\nb. Bahwa dengan berlakunya sistem Tanda Tangan Elektronik (TTE) terverifikasi, diperlukan format terstandar.'),
    mengingat: @js($document->body_content['mengingat'] ?? '1. Peraturan Direksi No. 01/PER-DIR/2024 tentang Tata Naskah Dinas;\\n2. UU No. 11 Tahun 2008 tentang Informasi dan Transaksi Elektronik (ITE).'),
    isiPasal1: @js($document->body_content['isiPasal1'] ?? 'Standar Operasional Prosedur Penyusunan Dokumen Resmi sebagaimana tercantum dalam Lampiran Keputusan ini sah dan mengikat seluruh unit kerja di lingkungan PT Nusantara Citra Media Tbk.'),
    isiPasal2: @js($document->body_content['isiPasal2'] ?? 'Setiap dokumen resmi wajib menggunakan format terstruktur (Header, Body, Footer) serta dilengkapi pembubuhan e-Sign atau stempel digital sah.'),
    
    // Footer & Signature Component Data
    kotaTtd: @js($document->footer_data['kotaTtd'] ?? 'Jakarta'),
    jabatanPenandatangan: @js($document->footer_data['jabatanPenandatangan'] ?? 'Direktur Utama'),
    namaPenandatangan: @js($document->footer_data['namaPenandatangan'] ?? 'Drs. H. Aris Budiman, M.B.A.'),
    nipPenandatangan: @js($document->footer_data['nipPenandatangan'] ?? 'NIP: 19780412 200312 1 002'),
    tembusan: @js($document->footer_data['tembusan'] ?? '1. Dewan Komisaris\\n2. Arsip Hukum (Legal Department)'),
    
    // Signature & Materai Options
    selectedMaterai: @js($document->signature_data['selectedMaterai'] ?? 'materai10k'),
    hasSignature: true,
    signatureX: @js($document->signature_data['signatureX'] ?? 65),
    signatureY: @js($document->signature_data['signatureY'] ?? 78),
    zoomLevel: 100,
    
    // Page count indicator
    currentPage: 1,
    totalPages: 2
}">

    <!-- Editor Toolbar -->
    @include('partials.editor.toolbar')

    <!-- Main Workspace Split: Left (Component Form Editor) vs Right (Live Realistic Paper Stage) -->
    <div class="grid grid-cols-12 gap-5 items-start">
        
        <!-- LEFT COLUMN: Component Input Editor (5 / 12) -->
        <div class="col-span-12 lg:col-span-5 space-y-4">
            
            <!-- Component Zone Selector Tabs -->
            <div class="flex rounded-xl border border-parchment-300 bg-parchment-100 p-1 dark:border-slate-warm-700 dark:bg-slate-warm-800">
                <button @click="activeZone = 'header'"
                    :class="activeZone === 'header' ? 'bg-white text-ink-900 shadow-xs font-semibold dark:bg-slate-warm-900 dark:text-parchment-50' : 'text-slate-warm-600 hover:text-ink-900 dark:text-parchment-400'"
                    class="flex-1 py-2 text-xs rounded-lg transition-all flex items-center justify-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-ink-900 dark:bg-bronze-400"></span>
                    1. Kop Surat
                </button>
                <button @click="activeZone = 'body'"
                    :class="activeZone === 'body' ? 'bg-white text-ink-900 shadow-xs font-semibold dark:bg-slate-warm-900 dark:text-parchment-50' : 'text-slate-warm-600 hover:text-ink-900 dark:text-parchment-400'"
                    class="flex-1 py-2 text-xs rounded-lg transition-all flex items-center justify-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-bronze-600"></span>
                    2. Isi Dokumen
                </button>
                <button @click="activeZone = 'footer'"
                    :class="activeZone === 'footer' ? 'bg-white text-ink-900 shadow-xs font-semibold dark:bg-slate-warm-900 dark:text-parchment-50' : 'text-slate-warm-600 hover:text-ink-900 dark:text-parchment-400'"
                    class="flex-1 py-2 text-xs rounded-lg transition-all flex items-center justify-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-seal-700"></span>
                    3. Legalese & TTD
                </button>
            </div>

            <!-- TAB 1: KOP SURAT / HEADER COMPONENT EDITOR -->
            @include('partials.editor.header-form')

            <!-- TAB 2: ISI DOKUMEN / BODY COMPONENT EDITOR -->
            @include('partials.editor.body-form')

            <!-- TAB 3: FOOTER & SIGNATURE COMPONENT EDITOR -->
            @include('partials.editor.footer-form')
        </div>

        <!-- RIGHT COLUMN: Real-Time Multi-Page Realistic A4 Paper Preview Stage (7 / 12) -->
        @include('partials.editor.paper-preview')

    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        if (typeof initDocumentEditor === 'function') {
            initDocumentEditor('#editor-pasal1');
            initDocumentEditor('#editor-pasal2');
        }
    });
</script>
@endpush
@endsection