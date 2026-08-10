<!-- Top Action & View Toolbar -->
<div
    class="flex flex-wrap items-center justify-between gap-4 rounded-xl border border-parchment-200 bg-white p-3.5 shadow-theme-xs dark:border-slate-warm-800 dark:bg-slate-warm-900">
    <!-- Left: Workspace Breadcrumb & Page Info -->
    <div class="flex items-center gap-3">
        <div
            class="flex h-9 w-9 items-center justify-center rounded-lg bg-ink-900 text-parchment-100 dark:bg-parchment-100 dark:text-ink-900">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7" />
                <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z" />
            </svg>
        </div>
        <div>
            <div class="flex items-center gap-2">
                <h1 class="font-serif font-bold text-base text-ink-900 dark:text-parchment-50">
                    Editor Komponen Dokumen
                </h1>
                <span class="doc-status text-[10px]" :class="hasSignature ? 'doc-status-verified' : 'doc-status-pending'" x-text="hasSignature ? 'TTD Terpasang' : 'Menunggu TTD'"></span>
            </div>
        </div>
    </div>

    <!-- Right: Zoom, Save & TTD Actions -->
    <div class="flex flex-wrap sm:flex-nowrap items-center gap-2 w-full sm:w-auto justify-between sm:justify-end">
        <div
            class="flex items-center gap-1 rounded-lg border border-parchment-300 bg-parchment-50 p-1 dark:border-slate-warm-700 dark:bg-slate-warm-800">
            <button @click="zoomLevel = Math.max(75, zoomLevel - 10)"
                class="px-2 py-1 text-xs font-mono text-ink-700 hover:bg-parchment-200 rounded dark:text-parchment-300 dark:hover:bg-slate-warm-700">
                -
            </button>
            <span class="px-2 font-mono text-xs text-slate-warm-600 dark:text-parchment-300"
                x-text="zoomLevel + '%'"></span>
            <button @click="zoomLevel = Math.min(130, zoomLevel + 10)"
                class="px-2 py-1 text-xs font-mono text-ink-700 hover:bg-parchment-200 rounded dark:text-parchment-300 dark:hover:bg-slate-warm-700">
                +
            </button>
        </div>

        <a :href="documentId ? '/documents/' + documentId + '/export' : '#'"
            @click="if (!documentId) { $event.preventDefault(); alert('Simpan dokumen dulu sebelum export PDF.'); }"
            class="btn-secondary text-xs px-3 py-1.5 h-8 flex items-center gap-1.5 shrink-0" target="_blank">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                <polyline points="7 10 12 15 17 10" />
                <line x1="12" y1="15" x2="12" y2="3" />
            </svg>
            <span>Unduh PDF</span>
        </a>

        <button @click="saveDocument()" :disabled="saveStatus === 'saving'"
            class="btn-secondary text-xs px-3 py-1.5 h-8 flex items-center gap-1.5 shrink-0">
            <svg x-show="saveStatus !== 'saving'" width="14" height="14" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2">
                <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z" />
                <polyline points="17 21 17 13 7 13 7 21" />
                <polyline points="7 3 7 8 15 8" />
            </svg>
            <span
                x-text="saveStatus === 'saving' ? 'Menyimpan...' : (saveStatus === 'saved' ? 'Tersimpan ✓' : 'Simpan')"></span>
        </button>

        <<div class="relative shrink-0">
            <button @click="showSignaturePicker = !showSignaturePicker"
                :class="hasSignature ? 'bg-seal-50 border-seal-200 text-seal-700' : 'bg-parchment-50 border-parchment-300 text-slate-warm-600'"
                class="btn-secondary text-xs px-3 py-1.5 h-8">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M20 12V8H6a2 2 0 0 1-2-2c0-1.1.9-2 2-2h12v4" />
                    <path d="M4 6v12c0 1.1.9 2 2 2h14v-4" />
                </svg>
                <span x-text="hasSignature ? 'TTD Terpasang' : '+ Tempel TTD'"></span>
            </button>

            <div x-show="showSignaturePicker" @click.outside="showSignaturePicker = false" x-transition
                class="absolute right-0 mt-2 w-64 rounded-xl border border-parchment-300 bg-white shadow-lg dark:bg-slate-warm-900 dark:border-slate-warm-700 z-50 p-3 space-y-2">
                <p class="text-xs font-semibold text-ink-900 dark:text-parchment-100 mb-1">Pilih Tanda Tangan</p>

                <div x-show="availableSignatures.length === 0" class="text-[11px] text-slate-warm-500 py-2">
                    Belum ada tanda tangan. <a href="{{ route('signatures') }}" class="text-bronze-600 underline">Buat
                        di Studio TTD →</a>
                </div>

                <div class="max-h-52 overflow-y-auto space-y-1.5">
                    <template x-for="sig in availableSignatures" :key="sig.id">
                        <button
                            @click="selectedSignatureUrl = sig.url; hasSignature = true; showSignaturePicker = false"
                            class="w-full flex items-center gap-2 p-2 rounded-lg border border-parchment-200 hover:border-bronze-400 hover:bg-parchment-25 dark:border-slate-warm-700 dark:hover:bg-slate-warm-800 transition-colors">
                            <img :src="sig.url" class="h-8 w-16 object-contain shrink-0" alt="Tanda tangan">
                            <span class="text-[11px] text-ink-900 dark:text-parchment-100 truncate"
                                x-text="sig.name"></span>
                        </button>
                    </template>
                </div>

                <button x-show="hasSignature"
                    @click="hasSignature = false; selectedSignatureUrl = null; showSignaturePicker = false"
                    class="w-full text-[11px] text-error-600 hover:underline pt-1 border-t border-parchment-200 dark:border-slate-warm-700">
                    Hapus TTD dari dokumen
                </button>
            </div>
    </div>
</div>
</div>