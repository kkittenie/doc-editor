@extends('layouts.app')

@section('content')
<div class="rounded-xl border border-parchment-300 bg-white p-6 shadow-theme-sm dark:border-slate-warm-800 dark:bg-slate-warm-900">
    <div class="mb-6 border-b border-parchment-200 pb-4 dark:border-slate-warm-800">
        <h1 class="font-serif font-bold text-xl text-ink-900 dark:text-parchment-50">
            Pengaturan Workspace Papercraft
        </h1>
        <p class="text-xs text-slate-warm-500 dark:text-parchment-400 mt-1">
            Konfigurasi standar tata naskah dinas, integrasi BSRE, dan preferensi aplikasi.
        </p>
    </div>

    <div class="space-y-6 max-w-2xl text-xs">
        <div>
            <label class="block font-semibold text-ink-900 dark:text-parchment-200 mb-1">Nama Organisasi / Perusahaan Default</label>
            <input type="text" value="PT NUSANTARA CITRA MEDIA TBK" class="w-full text-xs rounded-lg border border-parchment-300 p-2.5 bg-parchment-25 focus:border-ink-900 dark:bg-slate-warm-800 dark:border-slate-warm-700 dark:text-parchment-100 font-serif font-bold" />
        </div>

        <div>
            <label class="block font-semibold text-ink-900 dark:text-parchment-200 mb-1">Batas Margin Standar A4 (mm)</label>
            <div class="grid grid-cols-4 gap-3">
                <div>
                    <span class="text-[10px] text-slate-warm-500 block mb-0.5">Atas</span>
                    <input type="text" value="20 mm" class="w-full text-xs rounded-lg border border-parchment-300 p-2 bg-parchment-25 font-mono dark:bg-slate-warm-800 dark:border-slate-warm-700 dark:text-parchment-100" />
                </div>
                <div>
                    <span class="text-[10px] text-slate-warm-500 block mb-0.5">Bawah</span>
                    <input type="text" value="20 mm" class="w-full text-xs rounded-lg border border-parchment-300 p-2 bg-parchment-25 font-mono dark:bg-slate-warm-800 dark:border-slate-warm-700 dark:text-parchment-100" />
                </div>
                <div>
                    <span class="text-[10px] text-slate-warm-500 block mb-0.5">Kiri</span>
                    <input type="text" value="25 mm" class="w-full text-xs rounded-lg border border-parchment-300 p-2 bg-parchment-25 font-mono dark:bg-slate-warm-800 dark:border-slate-warm-700 dark:text-parchment-100" />
                </div>
                <div>
                    <span class="text-[10px] text-slate-warm-500 block mb-0.5">Kanan</span>
                    <input type="text" value="20 mm" class="w-full text-xs rounded-lg border border-parchment-300 p-2 bg-parchment-25 font-mono dark:bg-slate-warm-800 dark:border-slate-warm-700 dark:text-parchment-100" />
                </div>
            </div>
        </div>

        <div class="pt-4 border-t border-parchment-200 dark:border-slate-warm-800">
            <button class="btn-primary text-xs shadow-sm">
                Simpan Perubahan
            </button>
        </div>
    </div>
</div>
@endsection
