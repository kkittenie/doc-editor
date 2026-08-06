<!-- TAB 2: ISI DOKUMEN / BODY COMPONENT EDITOR -->
<div x-show="activeZone === 'body'"
    x-transition:enter="transition ease-out duration-300 transform"
    x-transition:enter-start="opacity-0 translate-y-3 scale-[0.98]"
    x-transition:enter-end="opacity-100 translate-y-0 scale-100"
    x-transition:leave="transition ease-in duration-150 transform"
    x-transition:leave-start="opacity-100 translate-y-0 scale-100"
    x-transition:leave-end="opacity-0 -translate-y-2 scale-[0.98]"
    class="rounded-xl border border-parchment-300 bg-white p-5 shadow-theme-sm dark:border-slate-warm-800 dark:bg-slate-warm-900 space-y-4">
    <div class="flex items-center justify-between border-b border-parchment-200 pb-3 dark:border-slate-warm-800">
        <div class="flex items-center gap-2">
            <span
                class="flex h-6 w-6 items-center justify-center rounded bg-bronze-600 text-white font-mono text-xs">2</span>
            <h3 class="font-semibold text-sm text-ink-900 dark:text-parchment-100">Editor Body Content & Sub-Pasal</h3>
        </div>
        <span class="text-[11px] font-mono text-bronze-700 dark:text-bronze-400">Body Zone</span>
    </div>

    <div>
        <label class="block text-xs font-semibold text-ink-800 dark:text-parchment-200 mb-1">Tujuan / Kepada</label>
        <textarea x-model="tujuanSurat" rows="2"
            class="w-full text-xs rounded-lg border border-parchment-300 p-2.5 bg-parchment-25 dark:bg-slate-warm-800 dark:border-slate-warm-700 dark:text-parchment-100 font-medium"></textarea>
    </div>

    <div>
        <label class="block text-xs font-semibold text-ink-800 dark:text-parchment-200 mb-1">Konsideran:
            Menimbang</label>
        <textarea x-model="menimbang" rows="3"
            class="w-full text-xs rounded-lg border border-parchment-300 p-2.5 bg-parchment-25 dark:bg-slate-warm-800 dark:border-slate-warm-700 dark:text-parchment-100 font-serif"></textarea>
    </div>

    <div>
        <label class="block text-xs font-semibold text-ink-800 dark:text-parchment-200 mb-1">Konsideran:
            Mengingat</label>
        <textarea x-model="mengingat" rows="3"
            class="w-full text-xs rounded-lg border border-parchment-300 p-2.5 bg-parchment-25 dark:bg-slate-warm-800 dark:border-slate-warm-700 dark:text-parchment-100 font-serif"></textarea>
    </div>

    <div>
        <label class="block text-xs font-semibold text-ink-800 dark:text-parchment-200 mb-1">Diktum KEPUTUSAN —
            Ketentuan Kesatu</label>
        <textarea id="editor-pasal1" x-ref="pasal1" x-model="isiPasal1" rows="3"
            class="tinymce-target w-full text-xs rounded-lg border border-parchment-300 p-2.5 bg-parchment-25 dark:bg-slate-warm-800 dark:border-slate-warm-700 dark:text-parchment-100 font-serif"></textarea>
    </div>

    <div>
        <label class="block text-xs font-semibold text-ink-800 dark:text-parchment-200 mb-1">Diktum KEPUTUSAN —
            Ketentuan Kedua</label>
        <textarea id="editor-pasal2" x-ref="pasal2" x-model="isiPasal2" rows="2"
            class="tinymce-target w-full text-xs rounded-lg border border-parchment-300 p-2.5 bg-parchment-25 dark:bg-slate-warm-800 dark:border-slate-warm-700 dark:text-parchment-100 font-serif"></textarea>
    </div>
</div>