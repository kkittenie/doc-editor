<div class="flex items-center justify-end gap-1 whitespace-nowrap">
    <a href="{{ route('sof.show', $sof) }}" class="rounded p-1.5 text-ink-700 hover:bg-parchment-100" title="Lihat">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" />
            <circle cx="12" cy="12" r="3" />
        </svg>
    </a>

    <a href="{{ route('sof.edit', $sof) }}" class="rounded p-1.5 text-amber-700 hover:bg-amber-50" title="Edit">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M12 20h9" />
            <path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4Z" />
        </svg>
    </a>

    @if ($sof->hasFile())
        <a href="{{ route('sof.download', $sof) }}" class="rounded p-1.5 text-ink-700 hover:bg-parchment-100" title="Unduh">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                <polyline points="7 10 12 15 17 10" />
                <line x1="12" y1="15" x2="12" y2="3" />
            </svg>
        </a>
    @else
        <button type="button" data-action="no-file" class="rounded p-1.5 text-slate-warm-400 hover:bg-parchment-100" title="Belum ada berkas">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                <polyline points="7 10 12 15 17 10" />
                <line x1="12" y1="15" x2="12" y2="3" />
            </svg>
        </button>
    @endif

    <button type="button" data-action="delete" data-url="{{ route('sof.destroy', $sof) }}"
        class="rounded p-1.5 text-red-700 hover:bg-red-50" title="Hapus">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <polyline points="3 6 5 6 21 6" />
            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6" />
            <path d="M10 11v6" />
            <path d="M14 11v6" />
            <path d="M9 6V4a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v2" />
        </svg>
    </button>
</div>