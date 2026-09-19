<div class="rounded-2xl border border-parchment-300 bg-white p-5 shadow-theme-xs dark:border-slate-warm-800 dark:bg-slate-warm-900">
    <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-slate-warm-500">
        Data Barang (dari Form Pelanggan)
    </p>

    <div class="mt-3 overflow-x-auto rounded-xl border border-parchment-200 dark:border-slate-warm-700">
        <table class="w-full min-w-[720px] text-left text-xs">
            <thead>
                <tr class="bg-parchment-100 text-slate-warm-500 dark:bg-slate-warm-800 dark:text-parchment-400">
                    <th class="px-3 py-2 font-semibold">Kode</th>
                    <th class="px-3 py-2 font-semibold">Nama Barang</th>
                    <th class="w-16 px-3 py-2 font-semibold">Unit</th>
                    <th class="w-28 px-3 py-2 font-semibold">Tipe</th>
                    <th class="w-28 px-3 py-2 font-semibold">Kepemilikan</th>
                    <th class="w-36 px-3 py-2 text-right font-semibold">Harga</th>
                </tr>
            </thead>

            <tbody>
                @forelse($selectedCustomer->barang as $index => $item)
                    <tr class="border-t border-parchment-200 dark:border-slate-warm-800">
                        <td class="whitespace-nowrap px-3 py-2 font-mono text-[11px] text-slate-warm-400">
                            BRG-{{ str_pad($index + 1, 5, '0', STR_PAD_LEFT) }}
                        </td>

                        <td class="px-3 py-2 font-medium text-ink-900 dark:text-parchment-100">
                            {{ $item->name }}
                        </td>

                        <td class="px-3 py-2 text-ink-800 dark:text-parchment-200">
                            {{ $item->quantity }}
                        </td>

                        <td class="px-3 py-2">
                            <span class="inline-flex items-center rounded-full border px-2 py-0.5 text-[10px] font-semibold {{ ($item->price_type ?? 'one_time') === 'monthly' ? 'border-sky-300 bg-sky-50 text-sky-700' : 'border-parchment-300 bg-parchment-50 text-slate-warm-600' }}">
                                {{ ($item->price_type ?? 'one_time') === 'monthly' ? 'Bulanan' : 'One Time' }}
                            </span>
                        </td>

                        <td class="px-3 py-2 capitalize text-ink-800 dark:text-parchment-200">
                            {{ $item->ownership ?? 'dibeli' }}
                        </td>

                        <td class="whitespace-nowrap px-3 py-2 text-right font-mono text-ink-900 dark:text-parchment-100">
                            Rp {{ number_format((float) $item->price, 0, ',', '.') }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-3 py-3 text-center text-[11px] text-slate-warm-400">
                            Belum ada barang untuk pelanggan ini.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
