@php
    /**
     * Badge status S.O.F — gaya disamakan dengan badge tabel Dokumen
     * (partials.customer.row-status): background soft + border tipis.
     *
     * Status S.O.F hanya ada dua: draft & selesai.
     */
    $classes = [
        'draft'    => 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-500/15 dark:text-amber-300 dark:border-amber-500/30',
        'selesai'  => 'bg-green-100 text-green-800 border-green-200 dark:bg-green-500/15 dark:text-green-300 dark:border-green-500/30',
    ][$status] ?? 'bg-parchment-100 text-slate-warm-600 border-parchment-300 dark:bg-slate-warm-800 dark:text-parchment-300 dark:border-slate-warm-700';
@endphp

<span class="inline-flex items-center whitespace-nowrap rounded-full border px-2.5 py-1 text-[11px] font-semibold {{ $classes }}">{{ $label }}</span>