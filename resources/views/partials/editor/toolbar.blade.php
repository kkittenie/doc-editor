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

    <!-- TOMBOL SAVE (BARU) -->
    <button @click="saveDocument()" :disabled="saveStatus === 'saving'"
        class="btn-secondary text-xs px-3 py-1.5 h-8 flex items-center gap-1.5">
        <svg x-show="saveStatus !== 'saving'" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/>
            <polyline points="17 21 17 13 7 13 7 21"/>
            <polyline points="7 3 7 8 15 8"/>
        </svg>
        <span x-text="saveStatus === 'saving' ? 'Menyimpan...' : (saveStatus === 'saved' ? 'Tersimpan ✓' : 'Simpan')"></span>
    </button>

    <button @click="hasSignature = !hasSignature" :class="hasSignature ? 'bg-seal-50 border-seal-200 text-seal-700' : 'bg-parchment-50 border-parchment-300 text-slate-warm-600'" class="btn-secondary text-xs px-3 py-1.5 h-8">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M20 12V8H6a2 2 0 0 1-2-2c0-1.1.9-2 2-2h12v4"/>
            <path d="M4 6v12c0 1.1.9 2 2 2h14v-4"/>
        </svg>
        <span x-text="hasSignature ? 'TTD Alami Tempel' : '+ Tempel TTD'"></span>
    </button>
</div>
</div>
