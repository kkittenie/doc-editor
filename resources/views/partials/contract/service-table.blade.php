<div class="rounded-2xl border border-parchment-300 bg-white p-5 shadow-theme-xs dark:border-slate-warm-800 dark:bg-slate-warm-900">
    <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-slate-warm-500">
        Data Service (dari Form Pelanggan)
    </p>

    <div class="mt-3 overflow-x-auto rounded-xl border border-parchment-200 dark:border-slate-warm-700">
        <table class="w-full min-w-[560px] text-left text-xs">
            <thead>
                <tr class="bg-parchment-100 text-slate-warm-500 dark:bg-slate-warm-800 dark:text-parchment-400">
                    <th class="px-3 py-2 font-semibold">Kode</th>
                    <th class="px-3 py-2 font-semibold">Nama Service</th>
                    <th class="w-28 px-3 py-2 font-semibold">Tipe</th>
                    <th class="w-36 px-3 py-2 text-right font-semibold">Harga</th>
                </tr>
            </thead>

            <tbody>
                @forelse($selectedCustomer->services as $index => $item)
                    <tr class="border-t border-parchment-200 dark:border-slate-warm-800">
                        <td class="whitespace-nowrap px-3 py-2 font-mono text-[11px] text-slate-warm-400">
                            SRV-{{ str_pad($index + 1, 5, '0', STR_PAD_LEFT) }}
                        </td>

                        <td class="px-3 py-2 font-medium text-ink-900 dark:text-parchment-100">
                            {{ $item->name }}
                        </td>

                        <td class="px-3 py-2">
                            <span class="inline-flex items-center rounded-full border px-2 py-0.5 text-[10px] font-semibold {{ ($item->price_type ?? 'one_time') === 'monthly' ? 'border-sky-300 bg-sky-50 text-sky-700' : 'border-parchment-300 bg-parchment-50 text-slate-warm-600' }}">
                                {{ ($item->price_type ?? 'one_time') === 'monthly' ? 'Bulanan' : 'One Time' }}
                            </span>
                        </td>

                        <td class="whitespace-nowrap px-3 py-2 text-right font-mono text-ink-900 dark:text-parchment-100">
                            Rp {{ number_format((float) $item->price, 0, ',', '.') }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-3 py-3 text-center text-[11px] text-slate-warm-400">
                            Belum ada service untuk pelanggan ini.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3 grid grid-cols-1 gap-3 sm:grid-cols-2">
        <div class="rounded-xl bg-parchment-50 px-4 py-3 dark:bg-slate-warm-800">
            <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-warm-500">Total One Time</p>
            <p class="mt-1 font-mono text-sm font-bold text-ink-900 dark:text-parchment-50">
                Rp {{ number_format((float) ($contractTotals['one_time'] ?? 0), 0, ',', '.') }}
            </p>
        </div>

        <div class="rounded-xl bg-parchment-50 px-4 py-3 dark:bg-slate-warm-800">
            <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-warm-500">Total Bulanan</p>
            <p class="mt-1 font-mono text-sm font-bold text-ink-900 dark:text-parchment-50">
                Rp {{ number_format((float) ($contractTotals['monthly'] ?? 0), 0, ',', '.') }}
            </p>
        </div>
    </div>
</div>
