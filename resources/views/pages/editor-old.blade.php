@extends('layouts.app')

@section('content')
<div x-data="{
    // Active Tab in Component Editor
    activeZone: 'header',

    // Document Save State
    documentId: @js($document->id ?? null),
    saveStatus: 'idle',
    showSignaturePicker: false,
    availableSignatures: @js($signatures ?? []),
    selectedSignatureUrl: @js($document->signature_data['signatureUrl'] ?? null),

    // Logo Upload State (TAMBAHAN BARU 1)
    companyLogoUrl: @js($templateData['header_data']['logoUrl'] ?? $document->header_data['logoUrl'] ?? null),
    isUploadingLogo: false,

    // Upload Logo Method (TAMBAHAN BARU 2)
    uploadLogo(event) {
        const file = event.target.files[0];
        if (!file) return;
        this.isUploadingLogo = true;
        const reader = new FileReader();
        reader.onload = async (e) => {
            try {
                const res = await window.axios.post('/documents/logo', { image: e.target.result });
                this.companyLogoUrl = res.data.url;
            } catch (err) {
                alert('Gagal mengunggah logo.');
                console.error(err);
            } finally {
                this.isUploadingLogo = false;
            }
        };
        reader.readAsDataURL(file);
    },

    async saveDocument() {
        this.saveStatus = 'saving';
        const payload = {
            title: this.perihalSurat || 'Dokumen Tanpa Judul',
            type: @js($templateData['type'] ?? $document->type ?? 'surat'),
            header_data: {
                kopInstansi: this.kopInstansi,
                kopAlamat: this.kopAlamat,
                kopKontrak: this.kopKontrak,
                nomorSurat: this.nomorSurat,
                perihalSurat: this.perihalSurat,
                tanggalSurat: this.tanggalSurat,
                sifatSurat: this.sifatSurat,
                logoUrl: this.companyLogoUrl,
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
                signatureUrl: this.selectedSignatureUrl,
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
    kopInstansi: @js($templateData['header_data']['kopInstansi'] ?? $document->header_data['kopInstansi'] ?? 'PT NUSANTARA CITRA MEDIA TBBK'),
    kopAlamat: @js($templateData['header_data']['kopAlamat'] ?? $document->header_data['kopAlamat'] ?? 'Gedung Menara Palma Lt. 18, Jl. H.R. Rasuna Said Blok X-2, Jakarta Selatan 12950'),
    kopKontrak: @js($templateData['header_data']['kopKontrak'] ?? $document->header_data['kopKontrak'] ?? 'Telp: (021) 5290-8888 | Email: sekretariat@ncm-media.co.id | Web: www.ncm-media.co.id'),
    nomorSurat: @js($templateData['header_data']['nomorSurat'] ?? $document->header_data['nomorSurat'] ?? '042/SK-DIR/VIII/2026'),
    perihalSurat: @js($templateData['header_data']['perihalSurat'] ?? $document->header_data['perihalSurat'] ?? 'Penetapan Standar Operasional Prosedur Penyusunan Dokumen Resmi Perusahaan'),
    tanggalSurat: @js($templateData['header_data']['tanggalSurat'] ?? $document->header_data['tanggalSurat'] ?? '05 Agustus 2026'),
    sifatSurat: @js($templateData['header_data']['sifatSurat'] ?? $document->header_data['sifatSurat'] ?? 'Penting / Rahasia'),   

    // Body Component Data
    tujuanSurat: @js($templateData['body_content']['tujuanSurat'] ?? $document->body_content['tujuanSurat'] ?? ''),
    menimbang: @js($templateData['body_content']['menimbang'] ?? $document->body_content['menimbang'] ?? ''),
    mengingat: @js($templateData['body_content']['mengingat'] ?? $document->body_content['mengingat'] ?? ''),
    isiPasal1: @js($templateData['body_content']['isiPasal1'] ?? $document->body_content['isiPasal1'] ?? ''),
    isiPasal2: @js($templateData['body_content']['isiPasal2'] ?? $document->body_content['isiPasal2'] ?? ''),    

    // Footer & Signature Component Data
    kotaTtd: @js($templateData['footer_data']['kotaTtd'] ?? $document->footer_data['kotaTtd'] ?? 'Jakarta'),
    jabatanPenandatangan: @js($templateData['footer_data']['jabatanPenandatangan'] ?? $document->footer_data['jabatanPenandatangan'] ?? 'Direktur Utama'),
    namaPenandatangan: @js($templateData['footer_data']['namaPenandatangan'] ?? $document->footer_data['namaPenandatangan'] ?? 'Drs. H. Aris Budiman, M.B.A.'),
    nipPenandatangan: @js($templateData['footer_data']['nipPenandatangan'] ?? $document->footer_data['nipPenandatangan'] ?? 'NIP: 19780412 200312 1 002'),
    tembusan: @js($templateData['footer_data']['tembusan'] ?? $document->footer_data['tembusan'] ?? '1. Dewan Komisaris\\n2. Arsip Hukum (Legal Department)'),
    
    // Signature & Materai Options
    selectedMaterai: @js($templateData['signature_data']['selectedMaterai'] ?? $document->signature_data['selectedMaterai'] ?? 'materai10k'),
    hasSignature: @js(!empty($templateData['signature_data']['signatureUrl'] ?? $document->signature_data['signatureUrl'] ?? null)),
    signatureX: @js($templateData['signature_data']['signatureX'] ?? $document->signature_data['signatureX'] ?? 65),
    signatureY: @js($templateData['signature_data']['signatureY'] ?? $document->signature_data['signatureY'] ?? 78),
    zoomLevel: 100,
    
    // Page count indicator
    currentPage: 1,
    totalPages: 2
}">

    <!-- Editor Toolbar -->
    @include('partials.editor.toolbar')

    <!-- Main Workspace Split -->
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