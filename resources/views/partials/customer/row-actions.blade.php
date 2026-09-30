@php
    $docStatus   = $doc?->status;
    $canContinue = ! in_array($docStatus, ['on_review', 'disetujui'], true);
    $canView     = $isAdmin && $doc !== null;
    $canReview   = $isAdmin && $docStatus === 'on_review';
    $canExport   = $isAdmin && $doc !== null;

    $btn = 'inline-flex h-8 items-center gap-1.5 rounded-lg border border-parchment-300 px-2.5 text-[11px] font-semibold text-ink-900 transition hover:border-ink-900 hover:bg-ink-900 hover:text-white dark:border-slate-warm-700 dark:text-parchment-200 dark:hover:border-bronze-500 dark:hover:bg-bronze-500 dark:hover:text-ink-900';
@endphp

<div class="flex items-center gap-2 whitespace-nowrap">
    @if ($canContinue)
        <a href="{{ route('documents.create', $customer->id) }}" class="{{ $btn }}">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M12 20h9" />
                <path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4Z" />
            </svg>
            Lanjut
        </a>
    @endif

    @if ($canView)
        <a href="{{ route('documents.edit', $doc->id) }}" class="{{ $btn }}" title="Lihat dokumen (read-only)">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" />
                <circle cx="12" cy="12" r="3" />
            </svg>
            Lihat
        </a>
    @endif

    @if ($canReview)
        <button type="button" data-action="approve" data-url="{{ route('documents.approve', $doc->id) }}"
            class="inline-flex h-8 items-center gap-1.5 rounded-lg border border-green-500 bg-green-600 px-2.5 text-[11px] font-semibold text-white transition hover:bg-green-700 dark:border-green-500/60 dark:bg-green-600 dark:hover:bg-green-500"
            title="Setujui dokumen & upload berkas kontrak final">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="m20 6-11 11-5-5" />
            </svg>
            Setujui
        </button>

        <button type="button" data-action="revision" data-url="{{ route('documents.Status', $doc->id) }}"
            class="inline-flex h-8 items-center gap-1.5 rounded-lg border border-orange-400 bg-white px-2.5 text-[11px] font-semibold text-orange-700 transition hover:bg-orange-50 dark:border-orange-500/60 dark:bg-transparent dark:text-orange-300 dark:hover:bg-orange-500/10"
            title="Kembalikan dokumen ke status Revisi">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M12 20h9" />
                <path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4Z" />
            </svg>
            Revisi
        </button>
    @endif

    @if ($canExport)
        <a href="{{ route('documents.export', $doc->id) }}" target="_blank" class="{{ $btn }}" title="Unduh berkas PDF dokumen">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                <polyline points="7 10 12 15 17 10" />
                <line x1="12" y1="15" x2="12" y2="3" />
            </svg>
            Unduh PDF
        </a>
    @endif

    <button type="button" data-action="delete" data-url="{{ route('customers.destroy', $customer->id) }}"
        data-name="{{ $customer->name }}"
        class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-transparent text-slate-warm-400 transition hover:border-red-200 hover:bg-red-50 hover:text-red-600 dark:hover:border-red-900/40 dark:hover:bg-red-900/20"
        title="Hapus pelanggan">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <polyline points="3 6 5 6 21 6" />
            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6" />
            <path d="M10 11v6" />
            <path d="M14 11v6" />
            <path d="M9 6V4a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v2" />
        </svg>
    </button>
</div>