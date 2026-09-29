@extends('layouts.app')

@section('content')
@php
$isAdmin = auth()->user()->hasRole('admin');
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
    {{-- SUMMARY CARDS --}}
    <div class="mb-6 grid grid-cols-2 gap-4 sm:grid-cols-4">
        <div
            class="rounded-xl border border-parchment-300 bg-white p-4 text-center dark:border-slate-warm-800 dark:bg-slate-warm-900">
            <p class="text-2xl font-bold text-ink-900 dark:text-parchment-50" x-text="counts.total"></p>
            <p class="text-[10px] uppercase text-slate-warm-500">Total S.O.F</p>
        </div>
        <div
            class="rounded-xl border border-parchment-300 bg-white p-4 text-center dark:border-slate-warm-800 dark:bg-slate-warm-900">
            <p class="text-2xl font-bold text-green-700" x-text="counts.with_file"></p>
            <p class="text-[10px] uppercase text-slate-warm-500">Dgn Berkas</p>
        </div>
        <div
            class="rounded-xl border border-parchment-300 bg-white p-4 text-center dark:border-slate-warm-800 dark:bg-slate-warm-900">
            <p class="text-2xl font-bold text-amber-700" x-text="counts.approved"></p>
            <p class="text-[10px] uppercase text-slate-warm-500">Disetujui</p>
        </div>
        <div
            class="rounded-xl border border-parchment-300 bg-white p-4 text-center dark:border-slate-warm-800 dark:bg-slate-warm-900">
            <p class="text-2xl font-bold text-slate-700" x-text="counts.pending"></p>
            <p class="text-[10px] uppercase text-slate-warm-500">Pending</p>
        </div>
    </div>

    @if (session('success'))
    <div class="mb-4 rounded-xl border border-green-200 bg-green-50 p-3 text-sm text-green-700">{{ session('success') }}
    </div>
    @endif

    {{-- FILTER + SEARCH --}}
    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex items-center gap-2">
            <button @click="setFilter('all')"
                :class="filterFile==='all'?'bg-ink-900 text-white':'bg-parchment-100 text-slate-warm-700'"
                class="filter-btn inline-flex h-8 items-center rounded-lg px-3 text-xs font-semibold">Semua</button>
            <button @click="setFilter('ready')"
                :class="filterFile==='ready'?'bg-ink-900 text-white':'bg-parchment-100 text-slate-warm-700'"
                class="filter-btn inline-flex h-8 items-center rounded-lg px-3 text-xs font-semibold">Dgn
                Berkas</button>
            <button @click="setFilter('pending')"
                :class="filterFile==='pending'?'bg-ink-900 text-white':'bg-parchment-100 text-slate-warm-700'"
                class="filter-btn inline-flex h-8 items-center rounded-lg px-3 text-xs font-semibold">Tanpa
                Berkas</button>
        </div>
        <div class="relative w-full sm:w-64">
            <input type="text" x-model="searchQuery" placeholder="Cari order / pelanggan / kontrak..."
                class="w-full rounded-xl border border-parchment-300 bg-white px-3 py-2 pl-9 text-sm text-ink-900 focus:border-bronze-500/50 focus:ring-0 dark:border-slate-warm-700 dark:bg-slate-warm-800">
            <svg class="absolute left-3 top-2.5 h-4 w-4 text-slate-warm-400" fill="none" stroke="currentColor"
                viewBox="0 0 24 24">
                <circle cx="11" cy="11" r="8"></circle>
                <line x1="21" y1="21" x2="15.636" y2="15.636"></line>
            </svg>
        </div>
    </div>

    {{-- TABLE --}}
    <div :class="loading ? 'opacity-60' : ''"
        class="overflow-x-auto rounded-xl border border-parchment-300 bg-white dark:border-slate-warm-800 dark:bg-slate-warm-900">
        <table class="w-full text-left text-sm">
            <thead class="bg-parchment-50 dark:bg-slate-warm-800">
                <tr>
                    @foreach ([
                    ['label' => 'Order', 'sort' => 'order_number', 'class' => ''],
                    ['label' => 'Pelanggan', 'sort' => 'customer_name', 'class' => ''],
                    ['label' => 'Kontrak', 'sort' => 'contract_number', 'class' => ''],
                    ['label' => 'Periode', 'sort' => 'active_date', 'class' => ''],
                    ['label' => 'Nilai', 'sort' => 'total_value', 'class' => ''],
                    ['label' => 'Berkas', 'sort' => null, 'class' => ''],
                    ['label' => 'Status', 'sort' => 'status', 'class' => ''],
                    ['label' => 'Aksi', 'sort' => null, 'class' => 'text-right'],
                    ] as $col)
                    <th class="px-4 py-3 text-xs font-semibold uppercase text-slate-warm-600 {{ $col['class'] }}">
                        @if ($col['sort'])
                        <button type="button" @click="sortBy('{{ $col['sort'] }}')"
                            class="inline-flex items-center gap-1 font-semibold uppercase hover:text-ink-900 dark:hover:text-parchment-50">
                            {{ $col['label'] }}
                            <span class="text-[9px]"
                                x-text="sortKey === '{{ $col['sort'] }}' ? (sortDir === 'asc' ? '▲' : '▼') : '↕'"></span>
                        </button>
                        @else
                        {{ $col['label'] }}
                        @endif
                    </th>
                    @endforeach
                </tr>
            </thead>

            <tbody class="divide-y divide-parchment-200 dark:divide-slate-warm-800">
                <tr x-show="!loading && sofs.length === 0" x-cloak>
                    <td colspan="8" class="px-4 py-10 text-center text-slate-warm-500 dark:text-parchment-400">
                        <div class="flex flex-col items-center gap-3">
                            <svg class="h-12 w-12 text-slate-warm-300" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <circle cx="12" cy="12" r="10"></circle>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12h6m-2 2l2-2-2-2M9 12l2-2 2 2"></path>
                            </svg>
                            <p>Tidak ada S.O.F ditemukan.</p>
                            @if($isAdmin)
                            <a href="{{ route('sof.create') }}" class="text-bronze-600 hover:underline">Buat S.O.F→</a>
                            @endif
                        </div>
                    </td>
                </tr>
                <template x-for="s in sofs" :key="s.id">
                    <tr class="border-t border-parchment-200 dark:border-slate-warm-800">
                        <td class="px-4 py-3">
                            <span class="font-mono text-xs text-ink-800 dark:text-parchment-200"
                                x-text="s.order_number"></span>
                        </td>
                        <td class="px-4 py-3">
                            <span class="text-sm text-ink-900 dark:text-parchment-100"
                                x-text="s.customer_name || '—'"></span>
                        </td>
                        <td class="px-4 py-3">
                            <span class="text-xs text-slate-warm-600 dark:text-parchment-400"
                                x-text="s.contract_number || '—'"></span>
                            <span class="block text-xs text-slate-warm-500" x-text="s.contract_name || ''"></span>
                        </td>
                        <td class="px-4 py-3">
                            <span class="text-xs text-slate-warm-600" x-text="s.periode_label"></span>
                        </td>
                        <td class="px-4 py-3">
                            <span class="text-xs"
                                x-text="s.total_value ? 'Rp ' + Number(s.total_value).toLocaleString('id-ID') : '—'"></span>
                        </td>
                        <td class="px-4 py-3">
                            <span x-show="s.has_file"
                                class="inline-flex items-center rounded bg-green-100 px-2 py-1 text-[10px] font-semibold text-green-800">Ada</span>
                            <span x-show="!s.has_file"
                                class="inline-flex items-center rounded bg-slate-100 px-2 py-1 text-[10px] font-semibold text-slate-700">Kosong</span>
                        </td>
                        <td class="px-4 py-3">
                            <span class="inline-block rounded-full px-2.5 py-1 text-[10px] font-semibold"
                                :class="s.status === 'approved' ? 'bg-green-100 text-green-800' : 'bg-slate-100 text-slate-700'"
                                x-text="s.status_label"></span>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex items-center justify-end gap-1">
                                <a :href="s.detail_url" class="rounded p-1.5 text-ink-700 hover:bg-parchment-100"
                                    title="Lihat"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                        fill="currentColor" class="bi bi-eye-fill" viewBox="0 0 16 16">
                                        <path d="M10.5 8a2.5 2.5 0 1 1-5 0 2.5 2.5 0 0 1 5 0" />
                                        <path
                                            d="M0 8s3-5.5 8-5.5S16 8 16 8s-3 5.5-8 5.5S0 8 0 8m8 3.5a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7" />
                                    </svg></i></a>
                                <a :href="s.edit_url" class="rounded p-1.5 text-amber-700 hover:bg-amber-50"
                                    title="Edit"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                        fill="currentColor" class="bi bi-pencil" viewBox="0 0 16 16">
                                        <path
                                            d="M12.146.146a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1 0 .708l-10 10a.5.5 0 0 1-.168.11l-5 2a.5.5 0 0 1-.65-.65l2-5a.5.5 0 0 1 .11-.168zM11.207 2.5 13.5 4.793 14.793 3.5 12.5 1.207zm1.586 3L10.5 3.207 4 9.707V10h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.293zm-9.761 5.175-.106.106-1.528 3.821 3.821-1.528.106-.106A.5.5 0 0 1 5 12.5V12h-.5a.5.5 0 0 1-.5-.5V11h-.5a.5.5 0 0 1-.468-.325" />
                                    </svg></a>
                                @if($isAdmin)
                                <button type="button" @click="downloadFile(s)"
                                    class="rounded p-1.5 text-ink-700 hover:bg-parchment-100" title="Unduh"><svg
                                        xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                                        class="bi bi-download" viewBox="0 0 16 16">
                                        <path
                                            d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5" />
                                        <path
                                            d="M7.646 11.854a.5.5 0 0 0 .708 0l3-3a.5.5 0 0 0-.708-.708L8.5 10.293V1.5a.5.5 0 0 0-1 0v8.793L5.354 8.146a.5.5 0 1 0-.708.708z" />
                                    </svg></button>
                                <button type="button" @click="deleteSof(s)"
                                    class="rounded p-1.5 text-red-700 hover:bg-red-50" title="Hapus"><svg
                                        xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                                        class="bi bi-trash3" viewBox="0 0 16 16">
                                        <path
                                            d="M6.5 1h3a.5.5 0 0 1 .5.5v1H6v-1a.5.5 0 0 1 .5-.5M11 2.5v-1A1.5 1.5 0 0 0 9.5 0h-3A1.5 1.5 0 0 0 5 1.5v1H1.5a.5.5 0 0 0 0 1h.538l.853 10.66A2 2 0 0 0 4.885 16h6.23a2 2 0 0 0 1.994-1.84l.853-10.66h.538a.5.5 0 0 0 0-1zm1.958 1-.846 10.58a1 1 0 0 1-.997.92h-6.23a1 1 0 0 1-.997-.92L3.042 3.5zm-7.487 1a.5.5 0 0 1 .528.47l.5 8.5a.5.5 0 0 1-.998.06L5 5.03a.5.5 0 0 1 .47-.53Zm5.058 0a.5.5 0 0 1 .47.53l-.5 8.5a.5.5 0 1 1-.998-.06l.5-8.5a.5.5 0 0 1 .528-.47M8 4.5a.5.5 0 0 1 .5.5v8.5a.5.5 0 0 1-1 0V5a.5.5 0 0 1 .5-.5" />
                                    </svg></button>
                                @endif
                            </div>
                        </td>
                    </tr>
                </template>
            </tbody>
        </table>
    </div>
    {{-- PAGINATION --}}
    <div x-show="meta.total > 0"
        class="mt-4 flex flex-col gap-3 text-xs text-slate-warm-500 dark:text-parchment-400 sm:flex-row sm:items-center sm:justify-between">

        <div class="flex items-center gap-3">
            <span x-text="'Menampilkan ' + meta.from + '–' + meta.to + ' dari ' + meta.total + ' data'"></span>

            <select x-model.number="perPage"
                class="h-8 rounded-lg border border-parchment-300 bg-white px-2 text-xs text-ink-900 outline-none focus:border-ink-900 dark:border-slate-warm-700 dark:bg-slate-warm-900 dark:text-parchment-100">
                <option value="10">10 / halaman</option>
                <option value="25">25 / halaman</option>
                <option value="50">50 / halaman</option>
            </select>
        </div>

        <div class="flex items-center gap-2">
            <button type="button" @click="goToPage(meta.current_page - 1)" :disabled="meta.current_page <= 1 || loading"
                class="inline-flex h-8 items-center rounded-lg border border-parchment-300 px-3 text-[11px] font-semibold text-ink-900 transition hover:border-ink-900 disabled:cursor-not-allowed disabled:opacity-40 dark:border-slate-warm-700 dark:text-parchment-200">
                ‹ Sebelumnya
            </button>

            <span x-text="'Halaman ' + meta.current_page + ' / ' + meta.last_page"></span>

            <button type="button" @click="goToPage(meta.current_page + 1)"
                :disabled="meta.current_page >= meta.last_page || loading"
                class="inline-flex h-8 items-center rounded-lg border border-parchment-300 px-3 text-[11px] font-semibold text-ink-900 transition hover:border-ink-900 disabled:cursor-not-allowed disabled:opacity-40 dark:border-slate-warm-700 dark:text-parchment-200">
                Berikutnya ›
            </button>
        </div>
    </div>
</div>

@push('scripts')
<script>
    window.sofDataUrl = @json(route('sof.data'));

    function sofList() {
        return {
            // data dari server
            sofs: [],
            counts: { total: 0, with_file: 0, approved: 0, pending: 0 },
            meta: { current_page: 1, last_page: 1, per_page: 10, total: 0, from: 0, to: 0 },
            loading: true,

            // kontrol tabel
            filterFile: 'all',
            searchQuery: '',
            sortKey: '',
            sortDir: 'asc',
            perPage: 10,
            page: 1,

            // internal
            searchTimer: null,
            requestId: 0,

            init() {
                this.fetchSofs();

                this.$watch('searchQuery', () => {
                    clearTimeout(this.searchTimer);
                    this.searchTimer = setTimeout(() => {
                        this.page = 1;
                        this.fetchSofs();
                    }, 350);
                });

                this.$watch('filterFile', () => { this.page = 1; this.fetchSofs(); });
                this.$watch('perPage', () => { this.page = 1; this.fetchSofs(); });
            },

            setFilter(f) { this.filterFile = f; },

            async fetchSofs() {
                const currentRequest = ++this.requestId;
                this.loading = true;

                try {
                    const { data } = await window.axios.get(window.sofDataUrl, {
                        params: {
                            search: this.searchQuery.trim(),
                            berkas: this.filterFile,
                            sort: this.sortKey || undefined,
                            dir: this.sortKey ? this.sortDir : undefined,
                            per_page: this.perPage,
                            page: this.page,
                        },
                    });

                    if (currentRequest !== this.requestId) return;

                    if (this.page > data.meta.last_page) {
                        this.page = data.meta.last_page;
                        return this.fetchSofs();
                    }

                    this.sofs = data.data;
                    this.meta = data.meta;
                    this.counts = data.counts;
                } catch (error) {
                    if (currentRequest !== this.requestId) return;
                    console.error(error);

                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal memuat data',
                        text: error?.response?.data?.message || 'Data S.O.F gagal dimuat.',
                    });
                } finally {
                    if (currentRequest === this.requestId) this.loading = false;
                }
            },

            sortBy(key) {
                if (this.sortKey === key) {
                    this.sortDir = this.sortDir === 'asc' ? 'desc' : 'asc';
                } else {
                    this.sortKey = key;
                    this.sortDir = 'asc';
                }
                this.page = 1;
                this.fetchSofs();
            },

            goToPage(p) {
                if (p < 1 || p > this.meta.last_page || p === this.meta.current_page) return;
                this.page = p;
                this.fetchSofs();
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
                    await this.fetchSofs();
                    Swal.fire({ icon: 'success', title: 'Dihapus', timer: 1200, showConfirmButton: false });
                } catch (e) {
                    Swal.fire({ icon: 'error', title: 'Gagal', text: 'Tidak dapat menghapus S.O.F.' });
                }
            },
        };
    }
</script>
@endpush
@endsection