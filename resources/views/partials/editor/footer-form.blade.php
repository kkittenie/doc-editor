<!-- TAB 3: FOOTER & SIGNATURE COMPONENT EDITOR -->
<div x-show="activeZone === 'footer'"
    x-transition:enter="transition ease-out duration-300 transform"
    x-transition:enter-start="opacity-0 translate-y-3 scale-[0.98]"
    x-transition:enter-end="opacity-100 translate-y-0 scale-100"
    x-transition:leave="transition ease-in duration-150 transform"
    x-transition:leave-start="opacity-100 translate-y-0 scale-100"
    x-transition:leave-end="opacity-0 -translate-y-2 scale-[0.98]"
    class="rounded-xl border border-parchment-300 bg-white p-5 shadow-theme-sm dark:border-slate-warm-800 dark:bg-slate-warm-900 space-y-4">
    <div class="flex items-center justify-between border-b border-parchment-200 pb-3 dark:border-slate-warm-800">
        <div class="flex items-center gap-2">
            <span class="flex h-6 w-6 items-center justify-center rounded bg-seal-700 text-white font-mono text-xs">3</span>
            <h3 class="font-semibold text-sm text-ink-900 dark:text-parchment-100">Editor Footer & Legal Signature</h3>
        </div>
        <span class="text-[11px] font-mono text-seal-700 dark:text-seal-300">Footer Zone</span>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
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
