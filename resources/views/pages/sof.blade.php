@extends('layouts.app')

@section('content')
@php
    $isAdmin = auth()->user()->hasRole('admin');
    $totalSof = count($sofs);
    $withFile = collect($sofs)->where('has_file', true)->count();
    $approved = collect($sofs)->where('status', 'approved')->count();
    $pending  = $totalSof - $approved;
@endphp

<div x-data="sofList()" class="container mx-auto px-4 py-10">
    <div class="mb-6 flex flex-col items-start justify-between gap-4 sm:flex-row sm:items-center">
        <div>
            <h1 class="font-serif text-2xl font-bold text-ink-900 dark:text-parchment-50">Menu S.O.F</h1>
            <p class="mt-1 text-sm text-slate-warm-500 dark:text-parchment-400">
                Repositori berkas Order Formulir — entitas mandiri, tidak terhubung ke Tabel Pelanggan.
            </p>
        </div>
        @if($isAdmin)
            <a href="{{ route('sof.create') }}" class="btn-primary shrink-0 text-xs">+ Tambah S.O.F</a>
        @endif
    </div>

    {{-- SUMMARY CARDS --}}
    <div class="mb-6 grid grid-cols-2 gap-4 sm:grid-cols-4">
        <div class="rounded-xl border border-parchment-300 bg-white p-4 text-center dark:border-slate-warm-800 dark:bg-slate-warm-900">
            <p class="text-2xl font-bold text-ink-900 dark:text-parchment-50">{{ $totalSof }}</p>
            <p class="text-[10px] uppercase text-slate-warm-500">Total S.O.F</p>
        </div>
        <div class="rounded-xl border border-parchment-300 bg-white p-4 text-center dark:border-slate-warm-800 dark:bg-slate-warm-900">
            <p class="text-2xl font-bold text-green-700">{{ $withFile }}</p>
            <p class="text-[10px] uppercase text-slate-warm-500">Dgn Berkas</p>
        </div>
        <div class="rounded-xl border border-parchment-300 bg-white p-4 text-center dark:border-slate-warm-800 dark:bg-slate-warm-900">
            <p class="text-2xl font-bold text-amber-700">{{ $approved }}</p>
            <p class="text-[10px] uppercase text-slate-warm-500">Disetujui</p>
        </div>
        <div class="rounded-xl border border-parchment-300 bg-white p-4 text-center dark:border-slate-warm-800 dark:bg-slate-warm-900">
            <p class="text-2xl font-bold text-slate-700">{{ $pending }}</p>
            <p class="text-[10px] uppercase text-slate-warm-500">Pending</p>
        </div>
    </div>

    @if (session('success'))
        <div class="mb-4 rounded-xl border border-green-200 bg-green-50 p-3 text-sm text-green-700">{{ session('success') }}</div>
    @endif

    {{-- FILTER + SEARCH --}}
    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex items-center gap-2">
            <button @click="setFilter('all')"
                    :class="filterFile==='all'?'bg-ink-900 text-white':'bg-parchment-100 text-slate-warm-700'"
                    class="filter-btn inline-flex h-8 items-center rounded-lg px-3 text-xs font-semibold">Semua</button>
            <button @click="setFilter('ready')"
                    :class="filterFile==='ready'?'bg-ink-900 text-white':'bg-parchment-100 text-slate-warm-700'"
                    class="filter-btn inline-flex h-8 items-center rounded-lg px-3 text-xs font-semibold">Dgn Berkas</button>
            <button @click="setFilter('pending')"
                    :class="filterFile==='pending'?'bg-ink-900 text-white':'bg-parchment-100 text-slate-warm-700'"
                    class="filter-btn inline-flex h-8 items-center rounded-lg px-3 text-xs font-semibold">Tanpa Berkas</button>
        </div>
        <div class="relative w-full sm:w-64">
            <input type="text" x-model="searchQuery"
                   placeholder="Cari order / pelanggan / kontrak..."
                   class="w-full rounded-xl border border-parchment-300 bg-white px-3 py-2 pl-9 text-sm text-ink-900 focus:border-bronze-500/50 focus:ring-0 dark:border-slate-warm-700 dark:bg-slate-warm-800">
            <svg class="absolute left-3 top-2.5 h-4 w-4 text-slate-warm-400" fill="none" stroke="currentColor"
                 viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"></circle>
                <line x1="21" y1="21" x2="15.636" y2="15.636"></line></svg>
        </div>
    </div>

    {{-- TABLE --}}
    <div class="overflow-x-auto rounded-xl border border-parchment-300 bg-white dark:border-slate-warm-800 dark:bg-slate-warm-900">
        <table class="w-full text-left text-sm">
            <thead class="bg-parchment-50 dark:bg-slate-warm-800">
                <tr>
                    <th class="px-4 py-3 text-xs font-semibold uppercase text-slate-warm-600">Order</th>
                    <th class="px-4 py-3 text-xs font-semibold uppercase text-slate-warm-600">Pelanggan</th>
                    <th class="px-4 py-3 text-xs font-semibold uppercase text-slate-warm-600">Kontrak</th>
                    <th class="px-4 py-3 text-xs font-semibold uppercase text-slate-warm-600">Periode</th>
                    <th class="px-4 py-3 text-xs font-semibold uppercase text-slate-warm-600">Nilai</th>
                    <th class="px-4 py-3 text-xs font-semibold uppercase text-slate-warm-600">Berkas</th>
                    <th class="px-4 py-3 text-xs font-semibold uppercase text-slate-warm-600">Status</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold uppercase text-slate-warm-600">Aksi</th>
                </tr>
            </thead>

            <tbody class="divide-y divide-parchment-200 dark:divide-slate-warm-800">
                <tr x-show="filtered.length === 0" x-cloak>
                    <td colspan="8" class="px-4 py-10 text-center text-slate-warm-500 dark:text-parchment-400">
                        <div class="flex flex-col items-center gap-3">
                            <svg class="h-12 w-12 text-slate-warm-300" fill="none" stroke="currentColor"
                                 viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"></circle>
                                 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                 d="M9 12h6m-2 2l2-2-2-2M9 12l2-2 2 2"></path></svg>
                            <p>Tidak ada S.O.F ditemukan.</p>
                            @if($isAdmin)
                                <a href="{{ route('sof.create') }}" class="text-bronze-600 hover:underline">Buat S.O.F pertama →</a>
                            @endif
                        </div>
                    </td>
                </tr>
                <template x-for="s in filtered" :key="s.id">
                    <tr class="border-t border-parchment-200 dark:border-slate-warm-800">
                        <td class="px-4 py-3">
                            <span class="font-mono text-xs text-ink-800 dark:text-parchment-200" x-text="s.order_number"></span>
                        </td>
                        <td class="px-4 py-3">
                            <span class="text-sm text-ink-900 dark:text-parchment-100" x-text="s.customer_name || '—'"></span>
                        </td>
                        <td class="px-4 py-3">
                            <span class="text-xs text-slate-warm-600 dark:text-parchment-400" x-text="s.contract_number || '—'"></span>
                            <span class="block text-xs text-slate-warm-500" x-text="s.contract_name || ''"></span>
                        </td>
                        <td class="px-4 py-3">
                            <span class="text-xs text-slate-warm-600" x-text="s.periode_label"></span>
                        </td>
                        <td class="px-4 py-3">
                            <span class="text-xs" x-text="s.total_value ? 'Rp ' + Number(s.total_value).toLocaleString('id-ID') : '—'"></span>
                        </td>
                        <td class="px-4 py-3">
                            <span x-show="s.has_file" class="inline-flex items-center rounded bg-green-100 px-2 py-1 text-[10px] font-semibold text-green-800">Ada</span>
                            <span x-show="!s.has_file" class="inline-flex items-center rounded bg-slate-100 px-2 py-1 text-[10px] font-semibold text-slate-700">Kosong</span>
                        </td>
                        <td class="px-4 py-3">
                            <span class="inline-block rounded-full px-2.5 py-1 text-[10px] font-semibold"
                                  :class="s.status === 'approved' ? 'bg-green-100 text-green-800' : 'bg-slate-100 text-slate-700'"
                                  x-text="s.status_label"></span>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex items-center justify-end gap-1">
                                <a :href="s.detail_url" class="rounded p-1.5 text-ink-700 hover:bg-parchment-100" title="Lihat">👁️</a>
                                <a :href="s.edit_url" class="rounded p-1.5 text-amber-700 hover:bg-amber-50" title="Edit">✏️</a>
                                @if($isAdmin)
                                    <button type="button" @click="downloadFile(s)" class="rounded p-1.5 text-ink-700 hover:bg-parchment-100" title="Unduh">⬇️</button>
                                    <button type="button" @click="deleteSof(s)" class="rounded p-1.5 text-red-700 hover:bg-red-50" title="Hapus">🗑️</button>
                                @endif
                            </div>
                        </td>
                    </tr>
                </template>
            </tbody>
        </table>
    </div>
</div>

@push('scripts')
<script>
window.sofListData = @json($sofs);

function sofList() {
    return {
        sofs: window.sofListData,
        filterFile: 'all',
        searchQuery: '',

        setFilter(f) { this.filterFile = f; },

        get filtered() {
            const search = this.searchQuery.toLowerCase();
            return this.sofs.filter(s => {
                const matchesFile = this.filterFile === 'all'
                    || (this.filterFile === 'ready' && s.has_file)
                    || (this.filterFile === 'pending' && !s.has_file);
                const text = (s.order_number + ' ' + (s.customer_name || '') + ' ' + (s.contract_number || '') + ' ' + (s.contract_name || '')).toLowerCase();
                return matchesFile && (search === '' || text.includes(search));
            });
        },

        downloadFile(s) {
            if (!s.has_file) {
                Swal.fire({ icon: 'warning', title: 'Tidak ada berkas', text: 'S.O.F ini belum memiliki berkas PDF.' });
                return;
            }
            window.location.href = s.download_url;
        },

        async deleteSof(s) {
            const r = await Swal.fire({
                icon: 'warning', title: 'Hapus S.O.F?',
                text: 'Berkas dan data akan dihapus permanen. Lanjutkan?',
                showCancelButton: true, confirmButtonText: 'Ya, hapus',
                cancelButtonText: 'Batal', confirmButtonColor: '#dc2626',
            });
            if (!r.isConfirmed) return;
            try {
                await window.axios.delete(s.delete_url);
                Swal.fire({ icon: 'success', title: 'Dihapus', timer: 1200, showConfirmButton: false });
                window.location.reload();
            } catch (e) {
                Swal.fire({ icon: 'error', title: 'Gagal', text: 'Tidak dapat menghapus S.O.F.' });
            }
        },
    };
}
</script>
@endpush
@endsection
