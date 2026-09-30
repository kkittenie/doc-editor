@php
    $classes = [
        'draft'       => 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-500/15 dark:text-amber-300 dark:border-amber-500/30',
        'on_progress' => 'bg-sky-50 text-sky-700 border-sky-200 dark:bg-sky-500/15 dark:text-sky-300 dark:border-sky-500/30',
        'on_review'   => 'bg-violet-50 text-violet-700 border-violet-200 dark:bg-violet-500/15 dark:text-violet-300 dark:border-violet-500/30',
        'revisi'      => 'bg-orange-50 text-orange-700 border-orange-200 dark:bg-orange-500/15 dark:text-orange-300 dark:border-orange-500/30',
        'disetujui'   => 'bg-green-50 text-green-700 border-green-200 dark:bg-green-500/15 dark:text-green-300 dark:border-green-500/30',
    ][$status] ?? 'bg-parchment-100 text-slate-warm-600 border-parchment-300 dark:bg-slate-warm-800 dark:text-parchment-300 dark:border-slate-warm-700';
@endphp

<span class="inline-flex items-center whitespace-nowrap rounded-full border px-2.5 py-1 text-[11px] font-semibold {{ $classes }}">{{ $label }}</span>
@if ($status !== 'draft' && $lastUpdate)
    <p class="mt-1 whitespace-nowrap text-[10px] leading-tight text-slate-warm-400 dark:text-parchment-500">Update: {{ $lastUpdate->format('d M Y H:i') }}</p>
@endif