@extends('layouts.app')

@section('content')

@php
    $summaryCards = [
        [
            'key' => 'total',
            'label' => 'Total S.O.F',
            'icon' => '<path d="M14 3H7a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8l-5-5z"/><path d="M14 3v5h5"/>',
            'iconClass' => 'bg-parchment-100 text-ink-900 dark:bg-slate-warm-800 dark:text-parchment-200',
        ],
        [
            'key' => 'barang',
            'label' => 'Total Barang',
            'icon' => '<path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><path d="M3.27 6.96 12 12.01l8.73-5.05"/><path d="M12 22.08V12"/>',
            'iconClass' => 'bg-sky-50 text-sky-700 dark:bg-sky-900/20 dark:text-sky-400',
        ],
        [
            'key' => 'service',
            'label' => 'Total Service',
            'icon' => '<path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/>',
            'iconClass' => 'bg-violet-50 text-violet-700 dark:bg-violet-900/20 dark:text-violet-400',
        ],
        [
            'key' => 'value',
            'label' => 'Total Nilai',
            'icon' => '<line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>',
            'iconClass' => 'bg-green-50 text-green-700 dark:bg-green-900/20 dark:text-green-400',
        ],
    ];
@endphp

<script>
    window.sofPageData = @json($sofData);

    function sofPage() {
        return {
            customers: window.sofPageData,
            filterFile: 'all',
            searchQuery: '',

            get filteredCustomers() {
                const search = this.searchQuery.toLowerCase();

                return this.customers.filter(customer => {
                    const matchesFile =
                        this.filterFile === 'all' ||
                        (this.filterFile === 'ready' && customer.fileReady) ||
                        (this.filterFile === 'pending' && !customer.fileReady);

                    const matchesSearch =
                        customer.name.toLowerCase().includes(search) ||
                        customer.id.toLowerCase().includes(search) ||
                        customer.customerNumber.toLowerCase().includes(search) ||
                        customer.contractNumber.toLowerCase().includes(search) ||
                        customer.contractName.toLowerCase().includes(search);

                    return matchesFile && matchesSearch;
                });
            },

            get totalBarang() {
                return this.customers.reduce((total, customer) => total + customer.barangCount, 0);
            },

            get totalService() {
                return this.customers.reduce((total, customer) => total + customer.serviceCount, 0);
            },

            get totalValue() {
                return this.customers.reduce((total, customer) => total + customer.totalValue, 0);
            },

            countFile(filter) {
                return this.customers.filter(customer =>
                    filter === 'ready' ? customer.fileReady : !customer.fileReady
                ).length;
            },

            formatRupiah(value) {
                return 'Rp ' + Number(value || 0).toLocaleString('id-ID');
            },

            fileClass(fileReady) {
                return fileReady
                    ? 'border-green-200 bg-green-50 text-green-700 dark:border-green-900/40 dark:bg-green-900/20 dark:text-green-400'
                    : 'border-amber-200 bg-amber-50 text-amber-700 dark:border-amber-900/40 dark:bg-amber-900/20 dark:text-amber-400';
            },
        };
    }
</script>

<div x-data="sofPage()" class="space-y-6">

    {{-- HEADER --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <div class="mb-2 flex items-center gap-2">
                <span
                    class="inline-flex h-7 w-7 items-center justify-center rounded-lg bg-ink-900 text-white dark:bg-bronze-500 dark:text-ink-900">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2">
                        <path d="M14 3H7a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8l-5-5z" />
                        <path d="M14 3v5h5" />
                    </svg>
                </span>

                <span class="text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-warm-500">
                    Contract Management
                </span>
            </div>

            <h1 class="font-serif text-2xl font-bold tracking-tight text-ink-900 dark:text-parchment-50">
                Menu S.O.F
            </h1>

            <p class="mt-1.5 max-w-2xl text-sm text-slate-warm-500 dark:text-parchment-400">
                Kumpulan Surat Order Formulir dari kontrak yang sudah <strong>disetujui</strong>. Berkas PDF dibuat
                otomatis saat Anda menekan <strong>Selesai</strong> (Setujui) di Studio Editor.
            </p>
            </p>
        </div>

        <a href="{{ route('documents') }}" class="btn-secondary shrink-0 text-xs">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" />
                <circle cx="9" cy="7" r="4" />
            </svg>

            Dokumen Saya
        </a>
    </div>

    {{-- SUMMARY CARDS --}}
    <div class="grid grid-cols-2 gap-3 md:grid-cols-4">
        @foreach ($summaryCards as $card)
            @php
                $counterExpression = match ($card['key']) {
                    'total' => 'customers.length',
                    'barang' => 'totalBarang',
                    'service' => 'totalService',
                    'value' => 'formatRupiah(totalValue)',
                };
            @endphp

            <div
                class="rounded-2xl border border-parchment-300 bg-white p-4 shadow-theme-xs dark:border-slate-warm-800 dark:bg-slate-warm-900">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-[11px] font-medium uppercase tracking-wide text-slate-warm-500">
                            {{ $card['label'] }}
                        </p>

                        <p class="mt-2 text-2xl font-bold text-ink-900 dark:text-parchment-50"
                            x-text="{!! $counterExpression !!}"></p>
                    </div>

                    <div class="flex h-9 w-9 items-center justify-center rounded-xl {{ $card['iconClass'] }}">
                        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2">
                            {!! $card['icon'] !!}
                        </svg>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
    {{-- TABEL S.O.F --}}
    <div
        class="overflow-hidden rounded-2xl border border-parchment-300 bg-white shadow-theme-xs dark:border-slate-warm-800 dark:bg-slate-warm-900">

        <div
            class="flex flex-col gap-4 border-b border-parchment-200 px-5 py-4 dark:border-slate-warm-800 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h2 class="text-sm font-semibold text-ink-900 dark:text-parchment-50">
                    Daftar Surat Order Formulir
                </h2>

                <p class="mt-0.5 text-xs text-slate-warm-500 dark:text-parchment-400">
                    Tekan <strong>Unduh</strong> untuk mengambil berkas PDF S.O.F. Kalau berkas belum ada, sistem
                    membuatnya otomatis dari data kontrak + Barang + Service + canvas dokumen.
                </p>
            </div>

            <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                {{-- SEARCH --}}
                <div class="relative">
                    <svg class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-slate-warm-400"
                        width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2">
                        <circle cx="11" cy="11" r="7" />
                        <path d="m20 20-4-4" />
                    </svg>

                    <input type="search" x-model="searchQuery" placeholder="Cari pelanggan / nomor S.O.F..."
                        class="h-10 w-full rounded-lg border border-parchment-300 bg-white pl-9 pr-3 text-sm text-ink-900 outline-none transition placeholder:text-slate-warm-400 focus:border-ink-900 focus:ring-2 focus:ring-ink-900/10 dark:border-slate-warm-700 dark:bg-slate-warm-900 dark:text-parchment-100 sm:w-64">
                </div>
            </div>
        </div>

        {{-- FILTER BERKAS --}}
        <div class="flex flex-wrap items-center gap-2 border-b border-parchment-200 px-5 py-3 dark:border-slate-warm-800">
            @foreach ([
                'all' => 'Semua',
                'ready' => 'Berkas Siap',
                'pending' => 'Belum Ada Berkas',
            ] as $value => $label)
                <button type="button" @click="filterFile = '{{ $value }}'"
                    class="rounded-full border px-3 py-1.5 text-[11px] font-semibold transition"
                    :class="filterFile === '{{ $value }}'
                        ? 'border-ink-900 bg-ink-900 text-white dark:border-bronze-500 dark:bg-bronze-500 dark:text-ink-900'
                        : 'border-parchment-300 text-slate-warm-600 hover:border-ink-900 hover:text-ink-900 dark:border-slate-warm-700 dark:text-parchment-300 dark:hover:border-bronze-500'">
                    {{ $label }}

                    @if ($value !== 'all')
                        <span class="ml-1 opacity-70"
                            x-text="'(' + countFile('{{ $value }}') + ')'"></span>
                    @endif
                </button>
            @endforeach
        </div>
        {{-- TABLE --}}
        <div class="overflow-x-auto">
            <table class="w-full min-w-[1180px] border-collapse text-left">
                <thead>
                    <tr class="bg-parchment-50 dark:bg-slate-warm-800/60">
                        @foreach ([
                            'Pelanggan ID',
                            'Nama Pelanggan',
                            'Nomer S.O.F / Kontrak',
                            'Masa Kontrak',
                            'Barang / Service',
                            'Nilai Kontrak',
                            'Berkas S.O.F',
                            'Action',
                        ] as $heading)
                            <th
                                class="whitespace-nowrap border-b border-parchment-200 px-4 py-3 text-[11px] font-semibold uppercase tracking-wide text-slate-warm-500 dark:border-slate-warm-800 dark:text-parchment-400">
                                {{ $heading }}
                            </th>
                        @endforeach
                    </tr>
                </thead>

                <tbody>
                    <template x-for="customer in filteredCustomers" :key="customer.databaseId">
                        <tr
                            class="border-b border-parchment-100 transition hover:bg-parchment-50/70 dark:border-slate-warm-800 dark:hover:bg-white/[0.03]">
                            <td class="whitespace-nowrap px-4 py-3.5">
                                <span class="font-mono text-xs font-semibold text-ink-900 dark:text-parchment-100"
                                    x-text="customer.id"></span>
                            </td>

                            <td class="px-4 py-3.5">
                                <div class="text-sm font-semibold text-ink-900 dark:text-parchment-50"
                                    x-text="customer.name"></div>

                                <div class="mt-0.5 text-[11px] text-slate-warm-400 dark:text-parchment-500"
                                    x-text="'Nomer pelanggan: ' + customer.customerNumber"></div>
                            </td>

                            <td class="whitespace-nowrap px-4 py-3.5">
                                <div class="font-mono text-xs text-slate-warm-600 dark:text-parchment-300"
                                    x-text="customer.contractNumber"></div>

                                <div class="mt-0.5 text-[11px] text-slate-warm-400 dark:text-parchment-500"
                                    x-text="customer.contractName"></div>
                            </td>

                            <td class="whitespace-nowrap px-4 py-3.5 text-sm text-ink-800 dark:text-parchment-200">
                                <div x-text="customer.activeDate + ' → ' + customer.finishDate"></div>

                                <div class="mt-0.5 text-[11px] text-slate-warm-400 dark:text-parchment-500"
                                    x-text="customer.activeMonths"></div>
                            </td>

                            <td class="whitespace-nowrap px-4 py-3.5 text-sm text-ink-800 dark:text-parchment-200">
                                <div>
                                    <span class="font-semibold" x-text="customer.barangCount"></span> barang
                                    <span class="mx-1 text-slate-warm-400">·</span>
                                    <span class="font-semibold" x-text="customer.serviceCount"></span> service
                                </div>

                                <div class="mt-0.5 text-[11px] text-slate-warm-400 dark:text-parchment-500"
                                    x-text="'Barang ' + formatRupiah(customer.totalBarang) + ' · Service ' + formatRupiah(customer.totalService)">
                                </div>
                            </td>

                            <td class="whitespace-nowrap px-4 py-3.5 text-sm font-semibold text-ink-900 dark:text-parchment-50"
                                x-text="formatRupiah(customer.totalValue)"></td>

                            <td class="whitespace-nowrap px-4 py-3.5">
                                <span class="inline-flex items-center rounded-full border px-2.5 py-1 text-[11px] font-semibold"
                                    :class="fileClass(customer.fileReady)"
                                    x-text="customer.fileReady ? 'Berkas Siap' : 'Belum Dibuat'"></span>

                                <div class="mt-1 text-[11px] text-slate-warm-400 dark:text-parchment-500"
                                    x-text="customer.generatedAt ? 'Dibuat ' + customer.generatedAt : 'Dokumen #' + (customer.documentId ?? '—')">
                                </div>
                            </td>

                            <td class="whitespace-nowrap px-4 py-3.5">
                                <div class="flex items-center gap-2">
                                    {{-- Lihat dokumen sumber (canvas + kop + tanda tangan) --}}
                                    <template x-if="customer.documentUrl">
                                        <a :href="customer.documentUrl"
                                            class="inline-flex h-8 items-center gap-1.5 rounded-lg border border-parchment-300 px-2.5 text-[11px] font-semibold text-ink-900 transition hover:border-ink-900 hover:bg-ink-900 hover:text-white dark:border-slate-warm-700 dark:text-parchment-200 dark:hover:border-bronze-500 dark:hover:bg-bronze-500 dark:hover:text-ink-900">
                                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none"
                                                stroke="currentColor" stroke-width="2">
                                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" />
                                                <circle cx="12" cy="12" r="3" />
                                            </svg>

                                            Lihat
                                        </a>
                                    </template>

                                    {{-- Unduh berkas S.O.F (dibuat otomatis bila belum ada) --}}
                                    <a :href="customer.downloadUrl"
                                        class="inline-flex h-8 items-center gap-1.5 rounded-lg bg-ink-900 px-2.5 text-[11px] font-semibold text-white transition hover:opacity-90 dark:bg-bronze-500 dark:text-ink-900">
                                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none"
                                            stroke="currentColor" stroke-width="2">
                                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                                            <polyline points="7 10 12 15 17 10" />
                                            <line x1="12" y1="15" x2="12" y2="3" />
                                        </svg>

                                        Unduh
                                    </a>
                                </div>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>

        {{-- EMPTY STATE --}}
        <div x-show="filteredCustomers.length === 0" x-cloak
            class="border-t border-parchment-200 px-6 py-14 text-center dark:border-slate-warm-800">
            <div
                class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-parchment-100 text-slate-warm-400 dark:bg-slate-warm-800">
                <svg width="21" height="21" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-width="1.7">
                    <path d="M14 3H7a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8l-5-5z" />
                    <path d="M14 3v5h5" />
                </svg>
            </div>

            <h3 class="mt-4 text-sm font-semibold text-ink-900 dark:text-parchment-50">
                Belum ada S.O.F
            </h3>

            <p class="mt-1 text-xs text-slate-warm-500">
                S.O.F muncul di sini setelah dokumen kontrak disetujui. Buka <strong>Studio Editor</strong> dari
                Tabel Pelanggan, lalu tekan <strong>Selesai</strong> (Setujui) di sebelah tombol Save — boleh dari
                status Draft, On Progress, On Review, maupun Revisi.
            </p>
        </div>
    </div>
</div>

@endsection
