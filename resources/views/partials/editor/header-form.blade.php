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
