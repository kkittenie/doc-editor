<!-- REAL-TIME MULTI-PAGE REALISTIC A4 PAPER PREVIEW STAGE -->
<div class="col-span-12 lg:col-span-7 flex flex-col items-center lg:min-h-0 lg:overflow-y-auto custom-scrollbar pb-8">
    
    <!-- Paper Canvas Background Container -->
    <div class="document-canvas p-6 md:p-10 rounded-2xl border border-parchment-300/80 dark:border-slate-warm-800 w-full flex flex-col items-center shadow-inner relative"
        :style="'transform: scale(' + (zoomLevel/100) + '); transform-origin: top center; transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);'">
        
        <!-- Page 1 Header Indicator -->
        <div class="w-full max-w-[595px] flex items-center justify-between mb-3 text-xs font-mono text-slate-warm-500 dark:text-parchment-400">
            <span class="flex items-center gap-1.5">
                PREVIEW A4 (210 × 297 mm)
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
                <div class="grid grid-cols-1 sm:grid-cols-12 gap-1 sm:gap-2">
                    <span class="sm:col-span-3 font-semibold font-sans text-xs uppercase tracking-wide">Menimbang:</span>
                    <div class="sm:col-span-9 whitespace-pre-line text-xs font-newsreader text-justify" x-text="menimbang"></div>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-12 gap-1 sm:gap-2">
                    <span class="sm:col-span-3 font-semibold font-sans text-xs uppercase tracking-wide">Mengingat:</span>
                    <div class="sm:col-span-9 whitespace-pre-line text-xs font-newsreader text-justify" x-text="mengingat"></div>
                </div>
            </div>

            <!-- MEMUTUSKAN HEADER -->
            <div class="text-center my-4">
                <span class="font-serif font-bold text-xs uppercase tracking-widest px-4 py-1 border-y border-ink-900 inline-block">MEMUTUSKAN</span>
            </div>

            <!-- PASAL / DIKTUM KESATU -->
            <div class="space-y-3 text-xs leading-relaxed text-justify">
                <div class="grid grid-cols-1 sm:grid-cols-12 gap-1 sm:gap-2">
                    <span class="sm:col-span-2 font-bold font-sans">KESATU:</span>
                    <div class="sm:col-span-10 text-ink-900" x-text="isiPasal1"></div>
                </div>
            </div>

            <!-- Page Footer Watermark -->
            <div class="absolute bottom-4 left-4 sm:left-12 right-4 sm:right-12 flex items-center justify-between text-[10px] font-mono text-slate-warm-400 border-t border-parchment-200 pt-2">
                <span class="truncate max-w-[200px] sm:max-w-none">Papercraft Digital Vault • SHA256: e8b9...41c9</span>
                <span class="shrink-0 ml-2">1</span>
            </div>
        </div>

        <!-- NATURAL VISUAL PAGE BREAK SEPARATOR -->
        <div class="w-full max-w-[595px] page-break my-4">
            <span class="page-break-label">Halaman 2</span>
        </div>

        <!-- REALISTIC A4 SHEET 2 -->
        <div class="paper-sheet paper-a4 paper-margins animate-fade-slide-up text-ink-900 font-newsreader relative">
            
            <!-- CONTINUED BODY CONTENT -->
            <div class="space-y-4 text-xs leading-relaxed text-justify pt-4 mb-10">
                <div class="grid grid-cols-1 sm:grid-cols-12 gap-1 sm:gap-2">
                    <span class="sm:col-span-2 font-bold font-sans">KEDUA:</span>
                    <div class="sm:col-span-10 text-ink-900" x-text="isiPasal2"></div>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-12 gap-1 sm:gap-2">
                    <span class="sm:col-span-2 font-bold font-sans">KETIGA:</span>
                    <div class="sm:col-span-10 text-ink-900">
                        Keputusan ini mulai berlaku sejak tanggal ditetapkan, dengan ketentuan apabila dikemudian hari terdapat kekeliruan akan diubah dan diperbaiki sebagaimana mestinya.
                    </div>
                </div>
            </div>

            <!-- FOOTER & SIGNATURE STAGE -->
            <div class="mt-8 sm:mt-12 grid grid-cols-1 sm:grid-cols-12 gap-4 items-end">
                
                <!-- Left: Tembusan & Audit QR Code -->
                <div class="sm:col-span-6 space-y-3">
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
                <div class="sm:col-span-6 text-center font-sans relative">
                    <p class="text-xs text-ink-800" x-text="'Ditetapkan di ' + kotaTtd"></p>
                    <p class="text-xs font-mono text-slate-warm-600" x-text="'Pada tanggal ' + tanggalSurat"></p>
                    <p class="text-xs font-bold text-ink-900 uppercase mt-2" x-text="jabatanPenandatangan"></p>
                    
                    <!-- Signature Area Container -->
                    <div class="my-3 min-h-[80px] sm:min-h-[90px] flex items-center justify-center relative">
                        
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
                                <svg width="180" height="70" viewBox="0 0 200 80" fill="none" class="text-ink-900 drop-shadow-xs max-w-full">
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
                <span>2</span>
            </div>
        </div>

    </div>
</div>
