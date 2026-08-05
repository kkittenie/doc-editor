@extends('layouts.app')

@section('content')
<div x-data="{
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

    <!-- Top Action & View Toolbar -->
    <div class="mb-5 flex flex-wrap items-center justify-between gap-4 rounded-xl border border-parchment-200 bg-white p-3.5 shadow-theme-xs dark:border-slate-warm-800 dark:bg-slate-warm-900">
        <!-- Left: Workspace Breadcrumb & Page Info -->
        <div class="flex items-center gap-3">
            <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-ink-900 text-parchment-100 dark:bg-parchment-100 dark:text-ink-900">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                </svg>
            </div>
            <div>
                <div class="flex items-center gap-2">
                    <h1 class="font-serif font-bold text-base text-ink-900 dark:text-parchment-50">
                        Editor Komponen Dokumen
                    </h1>
                    <span class="doc-status doc-status-pending text-[10px]">Menunggu TTD</span>
                </div>
                <p class="text-xs text-slate-warm-500 dark:text-parchment-400">
                    Otomatis menyusun Kop, Isi, Footer & Tanda Tangan ke lembar A4 real-time.
                </p>
            </div>
        </div>

        <!-- Right: Zoom & Preview Mode Switches -->
        <div class="flex items-center gap-2">
            <div class="flex items-center gap-1 rounded-lg border border-parchment-300 bg-parchment-50 p-1 dark:border-slate-warm-700 dark:bg-slate-warm-800">
                <button @click="zoomLevel = Math.max(75, zoomLevel - 10)" class="px-2 py-1 text-xs font-mono text-ink-700 hover:bg-parchment-200 rounded dark:text-parchment-300 dark:hover:bg-slate-warm-700">
                    -
                </button>
                <span class="px-2 font-mono text-xs text-slate-warm-600 dark:text-parchment-300" x-text="zoomLevel + '%'"></span>
                <button @click="zoomLevel = Math.min(130, zoomLevel + 10)" class="px-2 py-1 text-xs font-mono text-ink-700 hover:bg-parchment-200 rounded dark:text-parchment-300 dark:hover:bg-slate-warm-700">
                    +
                </button>
            </div>

            <button @click="hasSignature = !hasSignature" :class="hasSignature ? 'bg-seal-50 border-seal-200 text-seal-700' : 'bg-parchment-50 border-parchment-300 text-slate-warm-600'" class="btn-secondary text-xs px-3 py-1.5 h-8">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M20 12V8H6a2 2 0 0 1-2-2c0-1.1.9-2 2-2h12v4"/>
                    <path d="M4 6v12c0 1.1.9 2 2 2h14v-4"/>
                </svg>
                <span x-text="hasSignature ? 'TTD Alami Tempel' : '+ Tempel TTD'"></span>
            </button>
        </div>
    </div>

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
            <div x-show="activeZone === 'header'" x-transition class="rounded-xl border border-parchment-300 bg-white p-5 shadow-theme-sm dark:border-slate-warm-800 dark:bg-slate-warm-900 space-y-4">
                <div class="flex items-center justify-between border-b border-parchment-200 pb-3 dark:border-slate-warm-800">
                    <div class="flex items-center gap-2">
                        <span class="flex h-6 w-6 items-center justify-center rounded bg-ink-900 text-parchment-100 font-mono text-xs">1</span>
                        <h3 class="font-semibold text-sm text-ink-900 dark:text-parchment-100">Editor Kop & Metadata Surat</h3>
                    </div>
                    <span class="text-[11px] font-mono text-bronze-700 dark:text-bronze-400">Header Zone</span>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-ink-800 dark:text-parchment-200 mb-1">Nama Instansi / Perusahaan</label>
                    <input type="text" x-model="kopInstansi" class="w-full text-xs rounded-lg border border-parchment-300 p-2.5 bg-parchment-25 focus:border-ink-900 dark:bg-slate-warm-800 dark:border-slate-warm-700 dark:text-parchment-100 font-serif font-bold" />
                </div>

                <div>
                    <label class="block text-xs font-semibold text-ink-800 dark:text-parchment-200 mb-1">Alamat Lengkap Kop</label>
                    <textarea x-model="kopAlamat" rows="2" class="w-full text-xs rounded-lg border border-parchment-300 p-2.5 bg-parchment-25 focus:border-ink-900 dark:bg-slate-warm-800 dark:border-slate-warm-700 dark:text-parchment-100"></textarea>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-ink-800 dark:text-parchment-200 mb-1">Kontak & Website</label>
                    <input type="text" x-model="kopKontrak" class="w-full text-xs rounded-lg border border-parchment-300 p-2.5 bg-parchment-25 focus:border-ink-900 dark:bg-slate-warm-800 dark:border-slate-warm-700 dark:text-parchment-100" />
                </div>

                <div class="grid grid-cols-2 gap-3 pt-2">
                    <div>
                        <label class="block text-xs font-semibold text-ink-800 dark:text-parchment-200 mb-1">Nomor Surat</label>
                        <input type="text" x-model="nomorSurat" class="w-full text-xs rounded-lg border border-parchment-300 p-2 bg-parchment-25 font-mono dark:bg-slate-warm-800 dark:border-slate-warm-700 dark:text-parchment-100" />
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-ink-800 dark:text-parchment-200 mb-1">Tanggal Surat</label>
                        <input type="text" x-model="tanggalSurat" class="w-full text-xs rounded-lg border border-parchment-300 p-2 bg-parchment-25 dark:bg-slate-warm-800 dark:border-slate-warm-700 dark:text-parchment-100" />
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-ink-800 dark:text-parchment-200 mb-1">Perihal Dokumen</label>
                    <input type="text" x-model="perihalSurat" class="w-full text-xs rounded-lg border border-parchment-300 p-2.5 bg-parchment-25 dark:bg-slate-warm-800 dark:border-slate-warm-700 dark:text-parchment-100 font-medium" />
                </div>
            </div>

            <!-- TAB 2: ISI DOKUMEN / BODY COMPONENT EDITOR -->
            <div x-show="activeZone === 'body'" x-transition class="rounded-xl border border-parchment-300 bg-white p-5 shadow-theme-sm dark:border-slate-warm-800 dark:bg-slate-warm-900 space-y-4">
                <div class="flex items-center justify-between border-b border-parchment-200 pb-3 dark:border-slate-warm-800">
                    <div class="flex items-center gap-2">
                        <span class="flex h-6 w-6 items-center justify-center rounded bg-bronze-600 text-white font-mono text-xs">2</span>
                        <h3 class="font-semibold text-sm text-ink-900 dark:text-parchment-100">Editor Body Content & Sub-Pasal</h3>
                    </div>
                    <span class="text-[11px] font-mono text-bronze-700 dark:text-bronze-400">Body Zone</span>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-ink-800 dark:text-parchment-200 mb-1">Tujuan / Kepada</label>
                    <textarea x-model="tujuanSurat" rows="2" class="w-full text-xs rounded-lg border border-parchment-300 p-2.5 bg-parchment-25 dark:bg-slate-warm-800 dark:border-slate-warm-700 dark:text-parchment-100 font-medium"></textarea>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-ink-800 dark:text-parchment-200 mb-1">Konsideran: Menimbang</label>
                    <textarea x-model="menimbang" rows="3" class="w-full text-xs rounded-lg border border-parchment-300 p-2.5 bg-parchment-25 dark:bg-slate-warm-800 dark:border-slate-warm-700 dark:text-parchment-100 font-serif"></textarea>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-ink-800 dark:text-parchment-200 mb-1">Konsideran: Mengingat</label>
                    <textarea x-model="mengingat" rows="3" class="w-full text-xs rounded-lg border border-parchment-300 p-2.5 bg-parchment-25 dark:bg-slate-warm-800 dark:border-slate-warm-700 dark:text-parchment-100 font-serif"></textarea>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-ink-800 dark:text-parchment-200 mb-1">Diktum KEPUTUSAN — Ketentuan Kesatu</label>
                    <textarea x-model="isiPasal1" rows="3" class="w-full text-xs rounded-lg border border-parchment-300 p-2.5 bg-parchment-25 dark:bg-slate-warm-800 dark:border-slate-warm-700 dark:text-parchment-100 font-serif"></textarea>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-ink-800 dark:text-parchment-200 mb-1">Diktum KEPUTUSAN — Ketentuan Kedua</label>
                    <textarea x-model="isiPasal2" rows="2" class="w-full text-xs rounded-lg border border-parchment-300 p-2.5 bg-parchment-25 dark:bg-slate-warm-800 dark:border-slate-warm-700 dark:text-parchment-100 font-serif"></textarea>
                </div>
            </div>

            <!-- TAB 3: FOOTER & SIGNATURE COMPONENT EDITOR -->
            <div x-show="activeZone === 'footer'" x-transition class="rounded-xl border border-parchment-300 bg-white p-5 shadow-theme-sm dark:border-slate-warm-800 dark:bg-slate-warm-900 space-y-4">
                <div class="flex items-center justify-between border-b border-parchment-200 pb-3 dark:border-slate-warm-800">
                    <div class="flex items-center gap-2">
                        <span class="flex h-6 w-6 items-center justify-center rounded bg-seal-700 text-white font-mono text-xs">3</span>
                        <h3 class="font-semibold text-sm text-ink-900 dark:text-parchment-100">Editor Footer & Legal Signature</h3>
                    </div>
                    <span class="text-[11px] font-mono text-seal-700 dark:text-seal-300">Footer Zone</span>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-ink-800 dark:text-parchment-200 mb-1">Kota Penetapan</label>
                        <input type="text" x-model="kotaTtd" class="w-full text-xs rounded-lg border border-parchment-300 p-2 bg-parchment-25 dark:bg-slate-warm-800 dark:border-slate-warm-700 dark:text-parchment-100" />
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-ink-800 dark:text-parchment-200 mb-1">Jabatan Penandatangan</label>
                        <input type="text" x-model="jabatanPenandatangan" class="w-full text-xs rounded-lg border border-parchment-300 p-2 bg-parchment-25 dark:bg-slate-warm-800 dark:border-slate-warm-700 dark:text-parchment-100 font-medium" />
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-ink-800 dark:text-parchment-200 mb-1">Nama Lengkap & Gelar</label>
                    <input type="text" x-model="namaPenandatangan" class="w-full text-xs rounded-lg border border-parchment-300 p-2.5 bg-parchment-25 dark:bg-slate-warm-800 dark:border-slate-warm-700 dark:text-parchment-100 font-semibold" />
                </div>

                <div>
                    <label class="block text-xs font-semibold text-ink-800 dark:text-parchment-200 mb-1">NIP / NIK / ID Pejabat</label>
                    <input type="text" x-model="nipPenandatangan" class="w-full text-xs rounded-lg border border-parchment-300 p-2.5 bg-parchment-25 font-mono dark:bg-slate-warm-800 dark:border-slate-warm-700 dark:text-parchment-100" />
                </div>

                <div>
                    <label class="block text-xs font-semibold text-ink-800 dark:text-parchment-200 mb-1">Jenis Segel / Materai Digital</label>
                    <select x-model="selectedMaterai" class="w-full text-xs rounded-lg border border-parchment-300 p-2.5 bg-parchment-25 dark:bg-slate-warm-800 dark:border-slate-warm-700 dark:text-parchment-100">
                        <option value="materai10k">Materai Elektronik (e-Materai Rp10.000 Peruri)</option>
                        <option value="stempel_basah">Stempel Basah Resmi Perusahaan</option>
                        <option value="qr_bsre">Verifikasi QR Code BSRE / BSSN</option>
                        <option value="none">Tanpa Materai / Stempel</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-ink-800 dark:text-parchment-200 mb-1">Tembusan Dokumen</label>
                    <textarea x-model="tembusan" rows="2" class="w-full text-xs rounded-lg border border-parchment-300 p-2.5 bg-parchment-25 dark:bg-slate-warm-800 dark:border-slate-warm-700 dark:text-parchment-100 font-mono text-[11px]"></textarea>
                </div>
            </div>

            <!-- Quick Template Presets Bar -->
            <div class="rounded-xl border border-parchment-200 bg-parchment-100 p-3 dark:border-slate-warm-800 dark:bg-slate-warm-800">
                <span class="text-[11px] font-mono font-semibold uppercase text-slate-warm-500 dark:text-parchment-400 block mb-2">Preset Komponen Cepat</span>
                <div class="flex flex-wrap gap-2">
                    <button @click="perihalSurat='Surat Keputusan Pembentukan Panitia Kerja'; nomerSurat='055/SK-DIR/VIII/2026'" class="px-2.5 py-1 bg-white border border-parchment-300 rounded text-[11px] text-ink-800 hover:bg-parchment-50 dark:bg-slate-warm-900 dark:border-slate-warm-700 dark:text-parchment-200">
                        SK Direksi
                    </button>
                    <button @click="perihalSurat='Memorandum Penyesuaian Jam Kerja Operasional'; nomerSurat='012/MEMO-HRD/VIII/2026'" class="px-2.5 py-1 bg-white border border-parchment-300 rounded text-[11px] text-ink-800 hover:bg-parchment-50 dark:bg-slate-warm-900 dark:border-slate-warm-700 dark:text-parchment-200">
                        Internal Memo
                    </button>
                    <button @click="perihalSurat='Perjanjian Kerja Sama Kemitraan Digital'; nomerSurat='088/PKS-LEGAL/VIII/2026'" class="px-2.5 py-1 bg-white border border-parchment-300 rounded text-[11px] text-ink-800 hover:bg-parchment-50 dark:bg-slate-warm-900 dark:border-slate-warm-700 dark:text-parchment-200">
                        Perjanjian / PKS
                    </button>
                </div>
            </div>
        </div>

        <!-- RIGHT COLUMN: Real-Time Multi-Page Realistic A4 Paper Preview Stage (7 / 12) -->
        <div class="col-span-12 lg:col-span-7 flex flex-col items-center">
            
            <!-- Paper Canvas Background Container -->
            <div class="document-canvas p-6 md:p-10 rounded-2xl border border-parchment-300/80 dark:border-slate-warm-800 w-full flex flex-col items-center shadow-inner min-h-[850px] relative overflow-hidden"
                :style="'transform: scale(' + (zoomLevel/100) + '); transform-origin: top center; transition: transform 0.2s ease;'">
                
                <!-- Page 1 Header Indicator -->
                <div class="w-full max-w-[595px] flex items-center justify-between mb-3 text-xs font-mono text-slate-warm-500 dark:text-parchment-400">
                    <span class="flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-success-500 animate-pulse"></span>
                        PREVIEW REAL-TIME A4 (210 × 297 mm)
                    </span>
                    <span class="page-number">Lembar 1 dari 2</span>
                </div>

                <!-- REALISTIC A4 SHEET 1 -->
                <div class="paper-sheet paper-a4 paper-margins animate-fade-slide-up mb-8 text-ink-900 font-newsreader">
                    
                    <!-- KOP SURAT / HEADER STAGE -->
                    <div class="text-center border-b-2 border-ink-900 pb-4 mb-6 relative">
                        <!-- Official Crest / Logo Simulation -->
                        <div class="mx-auto mb-2 flex h-12 w-12 items-center justify-center rounded-full bg-ink-900 text-parchment-100 font-serif font-bold text-lg shadow-sm">
                            NC
                        </div>
                        <h2 class="font-serif font-bold text-xl uppercase tracking-wide text-ink-900" x-text="kopInstansi"></h2>
                        <p class="text-xs font-sans text-slate-warm-600 mt-1 leading-snug" x-text="kopAlamat"></p>
                        <p class="text-[10px] font-mono text-slate-warm-500 mt-0.5" x-text="kopKontrak"></p>
                        <!-- Double rule beneath kop -->
                        <div class="border-b border-ink-900 mt-2"></div>
                    </div>

                    <!-- DOKUMEN TITLE & NOMOR -->
                    <div class="text-center mb-6">
                        <h3 class="font-serif font-bold text-base uppercase underline tracking-wider text-ink-900">KEPUTUSAN DIREKSI</h3>
                        <p class="font-mono text-xs text-ink-800 mt-1" x-text="'Nomor: ' + nomorSurat"></p>
                        <p class="font-sans text-xs text-slate-warm-600 mt-1 italic" x-text="'Tentang: ' + perihalSurat"></p>
                    </div>

                    <!-- SALUTATION & TUJUAN -->
                    <div class="mb-5 text-sm font-sans leading-relaxed">
                        <p class="whitespace-pre-line font-medium text-ink-900" x-text="tujuanSurat"></p>
                    </div>

                    <!-- MENIMBANG & MENGINGAT -->
                    <div class="mb-5 space-y-3 text-sm leading-relaxed">
                        <div class="grid grid-cols-12 gap-2">
                            <span class="col-span-3 font-semibold font-sans text-xs uppercase tracking-wide">Menimbang:</span>
                            <div class="col-span-9 whitespace-pre-line text-xs font-newsreader text-justify" x-text="menimbang"></div>
                        </div>
                        <div class="grid grid-cols-12 gap-2">
                            <span class="col-span-3 font-semibold font-sans text-xs uppercase tracking-wide">Mengingat:</span>
                            <div class="col-span-9 whitespace-pre-line text-xs font-newsreader text-justify" x-text="mengingat"></div>
                        </div>
                    </div>

                    <!-- MEMUTUSKAN HEADER -->
                    <div class="text-center my-4">
                        <span class="font-serif font-bold text-xs uppercase tracking-widest px-4 py-1 border-y border-ink-900 inline-block">MEMUTUSKAN</span>
                    </div>

                    <!-- PASAL / DIKTUM KESATU -->
                    <div class="space-y-3 text-xs leading-relaxed text-justify">
                        <div class="grid grid-cols-12 gap-2">
                            <span class="col-span-2 font-bold font-sans">KESATU:</span>
                            <div class="col-span-10 text-ink-900" x-text="isiPasal1"></div>
                        </div>
                    </div>

                    <!-- Page Footer Watermark -->
                    <div class="absolute bottom-4 left-12 right-12 flex items-center justify-between text-[10px] font-mono text-slate-warm-400 border-t border-parchment-200 pt-2">
                        <span>Papercraft Digital Vault • SHA256: e8b9...41c9</span>
                        <span>Halaman 1</span>
                    </div>
                </div>

                <!-- NATURAL VISUAL PAGE BREAK SEPARATOR -->
                <div class="w-full max-w-[595px] page-break my-4">
                    <span class="page-break-label">Otomatik Sambungan Halaman 2 (Auto-Pagination)</span>
                </div>

                <!-- REALISTIC A4 SHEET 2 -->
                <div class="paper-sheet paper-a4 paper-margins animate-fade-slide-up text-ink-900 font-newsreader relative">
                    
                    <!-- CONTINUED BODY CONTENT -->
                    <div class="space-y-4 text-xs leading-relaxed text-justify pt-4 mb-10">
                        <div class="grid grid-cols-12 gap-2">
                            <span class="col-span-2 font-bold font-sans">KEDUA:</span>
                            <div class="col-span-10 text-ink-900" x-text="isiPasal2"></div>
                        </div>
                        <div class="grid grid-cols-12 gap-2">
                            <span class="col-span-2 font-bold font-sans">KETIGA:</span>
                            <div class="col-span-10 text-ink-900">
                                Keputusan ini mulai berlaku sejak tanggal ditetapkan, dengan ketentuan apabila dikemudian hari terdapat kekeliruan akan diubah dan diperbaiki sebagaimana mestinya.
                            </div>
                        </div>
                    </div>

                    <!-- FOOTER & SIGNATURE STAGE -->
                    <div class="mt-12 grid grid-cols-12 gap-4 items-end">
                        
                        <!-- Left: Tembusan & Audit QR Code -->
                        <div class="col-span-6 space-y-3">
                            <div class="text-[11px] font-sans">
                                <span class="font-semibold block mb-1">Tembusan Yth:</span>
                                <p class="whitespace-pre-line text-slate-warm-600 font-mono text-[10px]" x-text="tembusan"></p>
                            </div>
                            
                            <!-- Digital Certificate Seal Stamp -->
                            <div class="flex items-center gap-2.5 p-2 rounded-lg border border-parchment-300 bg-parchment-50 max-w-[220px]">
                                <!-- Simulated QR Code -->
                                <div class="w-10 h-10 bg-ink-900 p-1 shrink-0 rounded flex flex-wrap gap-0.5">
                                    <div class="w-2.5 h-2.5 bg-white"></div>
                                    <div class="w-2.5 h-2.5 bg-transparent"></div>
                                    <div class="w-2.5 h-2.5 bg-white"></div>
                                    <div class="w-2.5 h-2.5 bg-transparent"></div>
                                    <div class="w-2.5 h-2.5 bg-white"></div>
                                    <div class="w-2.5 h-2.5 bg-white"></div>
                                </div>
                                <div class="text-[9px] font-mono leading-tight text-slate-warm-600">
                                    <span class="font-bold text-ink-900 block">TTE BSRE VERIFIED</span>
                                    <span>ID: 9814-BSRE-2026</span>
                                </div>
                            </div>
                        </div>

                        <!-- Right: Signature Block & Seal -->
                        <div class="col-span-6 text-center font-sans relative">
                            <p class="text-xs text-ink-800" x-text="'Ditetapkan di ' + kotaTtd"></p>
                            <p class="text-xs font-mono text-slate-warm-600" x-text="'Pada tanggal ' + tanggalSurat"></p>
                            <p class="text-xs font-bold text-ink-900 uppercase mt-2" x-text="jabatanPenandatangan"></p>
                            
                            <!-- Signature Area Container -->
                            <div class="my-3 min-h-[90px] flex items-center justify-center relative">
                                
                                <!-- MATERAI STAMP (if selected) -->
                                <template x-if="selectedMaterai === 'materai10k'">
                                    <div class="stamp-materai absolute left-2 top-1 shadow-sm">
                                        <div class="text-center">
                                            <span class="block text-[8px]">MATERAI</span>
                                            <span class="block font-mono text-[9px]">10.000</span>
                                        </div>
                                    </div>
                                </template>

                                <!-- STEMPEL BASAH (if selected) -->
                                <template x-if="selectedMaterai === 'stempel_basah'">
                                    <div class="w-16 h-16 border-2 border-seal-700 rounded-full absolute left-3 top-0 flex items-center justify-center text-seal-700 font-bold text-[9px] uppercase tracking-tighter opacity-85 rotate-[-12deg]">
                                        PT NCM TBK<br/>SEKRETARIAT
                                    </div>
                                </template>

                                <!-- REALISTIC CURSIVE SIGNATURE (SVG Vector Ink) -->
                                <template x-if="hasSignature">
                                    <div class="relative z-10">
                                        <svg width="180" height="70" viewBox="0 0 200 80" fill="none" class="text-ink-900 drop-shadow-xs">
                                            <path d="M 20 45 C 40 10, 60 70, 75 35 C 90 10, 80 60, 110 40 C 130 25, 140 55, 170 30 C 180 20, 190 35, 160 60 C 140 75, 100 70, 185 50" 
                                                stroke="#1B2A4A" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                                            <path d="M 50 40 Q 90 20, 130 45" stroke="#1B2A4A" stroke-width="1.8" stroke-linecap="round"/>
                                        </svg>
                                    </div>
                                </template>
                            </div>

                            <!-- Signer Name & NIP -->
                            <p class="font-serif font-bold text-xs underline text-ink-900" x-text="namaPenandatangan"></p>
                            <p class="font-mono text-[10px] text-slate-warm-600 mt-0.5" x-text="nipPenandatangan"></p>
                        </div>
                    </div>

                    <!-- Page 2 Footer Watermark -->
                    <div class="absolute bottom-4 left-12 right-12 flex items-center justify-between text-[10px] font-mono text-slate-warm-400 border-t border-parchment-200 pt-2">
                        <span>Papercraft Studio • Salinan Sah Perusahaan</span>
                        <span>Halaman 2</span>
                    </div>
                </div>

            </div>
        </div>

    </div>
</div>
@endsection
