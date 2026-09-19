<div class="md:col-span-2 xl:col-span-4">
    <div class="flex items-center justify-between">
        <p class="text-xs font-semibold text-slate-warm-600 dark:text-parchment-300">
            Barang <span class="font-normal text-slate-warm-400"></span>
        </p>

        <button type="button" @click="addBarang()"
            class="inline-flex h-8 items-center gap-1 rounded-lg border border-parchment-300 px-2.5 text-[11px] font-semibold text-ink-900 transition hover:border-ink-900 hover:bg-ink-900 hover:text-white dark:border-slate-warm-700 dark:text-parchment-200">
            + Tambah Barang
        </button>
    </div>

    <div class="mt-2 overflow-x-auto rounded-xl border border-parchment-200 dark:border-slate-warm-700">
        <table class="w-full min-w-[760px] text-left text-xs">
            <thead>
                <tr class="bg-parchment-100 text-slate-warm-500 dark:bg-slate-warm-800 dark:text-parchment-400">
                    <th class="px-3 py-2 font-semibold">Nama Barang</th>
                    <th class="w-20 px-3 py-2 font-semibold">Unit</th>
                    <th class="w-36 px-3 py-2 font-semibold">Harga (Rp)</th>
                    <th class="w-32 px-3 py-2 font-semibold">Tipe</th>
                    <th class="w-36 px-3 py-2 font-semibold">Kepemilikan</th>
                    <th class="w-12 px-3 py-2"></th>
                </tr>
            </thead>

            <tbody>
                <template x-for="(row, index) in barangRows" :key="'b' + index">
                    <tr class="border-t border-parchment-200 dark:border-slate-warm-800">
                        <td class="px-3 py-2">
                            <input type="text" :name="'barang[' + index + '][name]'"
                                x-model="row.name" maxlength="150" placeholder="Contoh: Router"
                                class="h-9 w-full rounded-lg border border-parchment-300 bg-white px-2.5 text-xs outline-none focus:border-ink-900 dark:border-slate-warm-700 dark:bg-slate-warm-900">
                        </td>

                        <td class="px-3 py-2">
                            <input type="number" min="1" :name="'barang[' + index + '][quantity]'"
                                x-model="row.quantity"
                                class="h-9 w-full rounded-lg border border-parchment-300 bg-white px-2.5 text-xs outline-none focus:border-ink-900 dark:border-slate-warm-700 dark:bg-slate-warm-900">
                        </td>

                        <td class="px-3 py-2">
                            <input type="number" min="0" step="0.01" :name="'barang[' + index + '][price]'"
                                x-model="row.price" placeholder="0"
                                class="h-9 w-full rounded-lg border border-parchment-300 bg-white px-2.5 text-xs outline-none focus:border-ink-900 dark:border-slate-warm-700 dark:bg-slate-warm-900">
                        </td>

                        <td class="px-3 py-2">
                            <select :name="'barang[' + index + '][price_type]'" x-model="row.price_type"
                                class="h-9 w-full rounded-lg border border-parchment-300 bg-white px-2 text-xs outline-none focus:border-ink-900 dark:border-slate-warm-700 dark:bg-slate-warm-900">
                                <option value="one_time">One Time</option>
                                <option value="monthly">Bulanan</option>
                            </select>
                        </td>

                        <td class="px-3 py-2">
                            <select :name="'barang[' + index + '][ownership]'" x-model="row.ownership"
                                class="h-9 w-full rounded-lg border border-parchment-300 bg-white px-2 text-xs outline-none focus:border-ink-900 dark:border-slate-warm-700 dark:bg-slate-warm-900">
                                <option value="disewa">Disewa</option>
                                <option value="dipinjamkan">Dipinjamkan</option>
                                <option value="dibeli">Dibeli</option>
                            </select>
                        </td>

                        <td class="px-3 py-2 text-center">
                            <button type="button" @click="removeBarang(index)"
                                class="inline-flex h-7 w-7 items-center justify-center rounded-lg text-slate-warm-400 transition hover:bg-red-50 hover:text-red-600"
                                title="Hapus baris">×</button>
                        </td>
                    </tr>
                </template>

                <tr x-show="barangRows.length === 0">
                    <td colspan="6" class="px-3 py-3 text-center text-[11px] text-slate-warm-400">
                        Belum ada barang. Klik "Tambah Barang" bila pelanggan memiliki barang.
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
