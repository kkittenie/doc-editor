<!-- TAB 1: KOP SURAT / HEADER COMPONENT EDITOR -->
<div x-show="activeZone === 'header'"
    x-transition:enter="transition ease-out duration-300 transform"
    x-transition:enter-start="opacity-0 translate-y-3 scale-[0.98]"
    x-transition:enter-end="opacity-100 translate-y-0 scale-100"
    x-transition:leave="transition ease-in duration-150 transform"
    x-transition:leave-start="opacity-100 translate-y-0 scale-100"
    x-transition:leave-end="opacity-0 -translate-y-2 scale-[0.98]"
    class="rounded-xl border border-parchment-300 bg-white p-5 shadow-theme-sm dark:border-slate-warm-800 dark:bg-slate-warm-900 space-y-4">
    <div class="flex items-center justify-between border-b border-parchment-200 pb-3 dark:border-slate-warm-800">
        <div class="flex items-center gap-2">
            <span class="flex h-6 w-6 items-center justify-center rounded bg-ink-900 text-parchment-100 font-mono text-xs">1</span>
            <h3 class="font-semibold text-sm text-ink-900 dark:text-parchment-100">Editor Kop & Metadata Surat</h3>
        </div>
        <span class="text-[11px] font-mono text-bronze-700 dark:text-bronze-400">Header Zone</span>
    </div>

    <div>
    <label class="block text-xs font-semibold text-ink-800 dark:text-parchment-200 mb-1">Logo Perusahaan</label>
    <div class="flex items-center gap-3">
        <div class="h-14 w-14 rounded-full border border-parchment-300 dark:border-slate-warm-700 flex items-center justify-center overflow-hidden bg-parchment-25 dark:bg-slate-warm-800 shrink-0">
            <img x-show="companyLogoUrl" :src="companyLogoUrl" class="h-full w-full object-cover" alt="Logo">
            <span x-show="!companyLogoUrl" class="text-[10px] text-slate-warm-400">No Logo</span>
        </div>
        <div class="flex flex-col gap-1.5">
            <button type="button" @click="$refs.logoInput.click()" :disabled="isUploadingLogo" class="btn-secondary text-[11px] px-3 py-1.5 h-7">
                <span x-text="isUploadingLogo ? 'Mengunggah...' : (companyLogoUrl ? 'Ganti Logo' : 'Unggah Logo')"></span>
            </button>
            <button type="button" x-show="companyLogoUrl" @click="companyLogoUrl = null" class="text-[11px] text-error-600 hover:underline text-left">
                Hapus Logo
            </button>
        </div>
        <input type="file" x-ref="logoInput" accept="image/png,image/jpeg,image/svg+xml" class="hidden" @change="uploadLogo($event)">
    </div>
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

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-2">
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
