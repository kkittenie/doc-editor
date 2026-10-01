@php
    // Class dasar & ikon disalin literal dari partials.customer.row-actions
    // (Tabel Dokumen Saya) agar tombol S.O.F identik bentuk, ikon, dan tulisannya.
    $btn = 'inline-flex h-8 items-center gap-1.5 rounded-lg border border-parchment-300 px-2.5 text-[11px] font-semibold text-ink-900 transition hover:border-ink-900 hover:bg-ink-900 hover:text-white dark:border-slate-warm-700 dark:text-parchment-200 dark:hover:border-bronze-500 dark:hover:bg-bronze-500 dark:hover:text-ink-900';

    // Tanpa berkas: tombol tetap tampil sebagai penanda, tapi nonaktif.
    $btnDisabled = 'inline-flex h-8 cursor-not-allowed items-center gap-1.5 rounded-lg border border-dashed border-parchment-300 px-2.5 text-[11px] font-semibold text-slate-warm-400 transition dark:border-slate-warm-700 dark:text-slate-warm-500';
@endphp

<div class="flex items-center gap-2 whitespace-nowrap">
    <a href="{{ route('sof.show', $sof) }}" class="{{ $btn }}" title="Lihat S.O.F">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" />
            <circle cx="12" cy="12" r="3" />
        </svg>
        Lihat
    </a>

    <a href="{{ route('sof.edit', $sof) }}" class="{{ $btn }}" title="Edit S.O.F">
        {{-- Ikon pensil: sama dengan ikon "Lanjut" di Tabel Dokumen Saya. --}}
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M12 20h9" />
            <path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4Z" />
        </svg>
        Edit
    </a>

    @if ($sof->hasFile())
        <a href="{{ route('sof.download', $sof) }}" class="{{ $btn }}" title="Unduh berkas PDF">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                <polyline points="7 10 12 15 17 10" />
                <line x1="12" y1="15" x2="12" y2="3" />
            </svg>
            Unduh PDF
        </a>
    @else
        <button type="button" data-action="no-file" class="{{ $btnDisabled }}" disabled
            title="Belum ada berkas PDF">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                <polyline points="7 10 12 15 17 10" />
                <line x1="12" y1="15" x2="12" y2="3" />
            </svg>
            Unduh PDF
        </button>
    @endif

    <button type="button" data-action="delete" data-url="{{ route('sof.destroy', $sof) }}"
        class="inline-flex h-8 items-center gap-1.5 rounded-lg border border-red-300 bg-white px-2.5 text-[11px] font-semibold text-red-600 transition hover:bg-red-50 dark:border-red-500/60 dark:bg-transparent dark:text-red-400 dark:hover:bg-red-500/10"
        title="Hapus S.O.F">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-trash"
            viewBox="0 0 16 16">
            <path
                d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0z" />
            <path
                d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1zM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4zM2.5 3h11V2h-11z" />
        </svg>
        Hapus
    </button>
</div>