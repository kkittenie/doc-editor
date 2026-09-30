@extends('layouts.app')

@section('content')

<script>
    window.customerNextContract = @json($nextContractNumber);
    window.flashSuccess = @json(session('success'));

    function customersPage() {
        return {
            submitting: false,
            contractTyped: false,
            autoContractNumber: window.customerNextContract,

            init() {
                if (window.flashSuccess) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Tersimpan',
                        text: window.flashSuccess,
                        confirmButtonColor: '#1B2A4A',
                        timer: 2200,
                        showConfirmButton: false,
                    });
                }
            },

            async confirmSubmit(event) {
                const form = event.target;

                if (!form.reportValidity()) {
                    return;
                }

                event.preventDefault();

                const result = await Swal.fire({
                    icon: 'question',
                    title: 'Simpan pelanggan?',
                    text: 'Data pelanggan akan masuk ke Tabel Pelanggan dengan status Draft.',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, simpan',
                    cancelButtonText: 'Batal',
                    confirmButtonColor: '#1B2A4A',
                });

                if (!result.isConfirmed) return;

                this.submitting = true;
                form.submit();
            },
        };
    }
</script>

@php
$summaryCards = [
[
'key' => 'all',
'label' => 'Total Pelanggan',
'icon' => '
<path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" />
<circle cx="9" cy="7" r="4" />',
'iconClass' => 'bg-parchment-100 text-ink-900 dark:bg-slate-warm-800 dark:text-parchment-200',
],
[
'key' => 'draft',
'label' => 'Draft',
'icon' => '
<circle cx="12" cy="12" r="9" />
<path d="M12 7v5l3 2" />',
'iconClass' => 'bg-amber-50 text-amber-700 dark:bg-amber-900/20 dark:text-amber-400',
],
[
'key' => 'on_progress',
'label' => 'On Progress',
'icon' => '
<path d="M3 12a9 9 0 1 0 3-6.7L3 8" />
<path d="M3 3v5h5" />',
'iconClass' => 'bg-sky-50 text-sky-700 dark:bg-sky-900/20 dark:text-sky-400',
],
[
'key' => 'on_review',
'label' => 'On Review',
'icon' => '
<circle cx="11" cy="11" r="7" />
<path d="m20 20-4-4" />',
'iconClass' => 'bg-violet-50 text-violet-700 dark:bg-violet-900/20 dark:text-violet-400',
],
[
'key' => 'revisi',
'label' => 'Revisi',
'icon' => '
<path d="M12 20h9" />
<path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4Z" />',
'iconClass' => 'bg-orange-50 text-orange-700 dark:bg-orange-900/20 dark:text-orange-400',
],
[
'key' => 'disetujui',
'label' => 'Disetujui',
'icon' => '
<path d="m20 6-11 11-5-5" />',
'iconClass' => 'bg-green-50 text-green-700 dark:bg-green-900/20 dark:text-green-400',
],
];
@endphp

<div x-data="customersPage()" class="space-y-6">

    {{-- HEADER --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <div class="mb-2 flex items-center gap-2">
                <span
                    class="inline-flex h-7 w-7 items-center justify-center rounded-lg bg-ink-900 text-white dark:bg-bronze-500 dark:text-ink-900">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" />
                        <circle cx="9" cy="7" r="4" />
                    </svg>
                </span>

                <span class="text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-warm-500">
                    Contract Management
                </span>
            </div>

            <h1 class="font-serif text-2xl font-bold tracking-tight text-ink-900 dark:text-parchment-50">
                Dokumen Saya
            </h1>

            <p class="mt-1.5 max-w-2xl text-sm text-slate-warm-500 dark:text-parchment-400">
                Isi data pelanggan terlebih dahulu, lalu tekan <strong>Lanjut</strong> untuk menyusun kontraknya di
                Studio Editor.
            </p>
        </div>
    </div>

    {{-- SUMMARY CARDS --}}
    <div class="grid grid-cols-2 gap-3 md:grid-cols-3 lg:grid-cols-6">
        @foreach ($summaryCards as $card)
        <div
            class="rounded-2xl border border-parchment-300 bg-white p-4 shadow-theme-xs dark:border-slate-warm-800 dark:bg-slate-warm-900">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-[11px] font-medium uppercase tracking-wide text-slate-warm-500">
                        {{ $card['label'] }}
                    </p>

                    {{-- Angka diisi dari JSON "counts" kiriman Yajra lewat event xhr.dt. --}}
                    <p class="mt-2 text-2xl font-bold text-ink-900 dark:text-parchment-50"
                        data-count="{{ $card['key'] }}">0</p>
                </div>

                <div class="flex h-9 w-9 items-center justify-center rounded-xl {{ $card['iconClass'] }}">
                    <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        {!! $card['icon'] !!}
                    </svg>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- FORM PELANGGAN --}}
    <div
        class="overflow-hidden rounded-2xl border border-parchment-300 bg-white shadow-theme-xs dark:border-slate-warm-800 dark:bg-slate-warm-900">

        <div class="flex items-start gap-3 border-b border-parchment-200 px-5 py-4 dark:border-slate-warm-800">
            <span
                class="mt-0.5 inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-xl bg-bronze-100 text-bronze-800 dark:bg-bronze-500/20 dark:text-bronze-300">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="12" y1="5" x2="12" y2="19" />
                    <line x1="5" y1="12" x2="19" y2="12" />
                </svg>
            </span>

            <div>
                <h2 class="text-sm font-semibold text-ink-900 dark:text-parchment-50">
                    Form Pelanggan
                </h2>

                <p class="mt-0.5 text-xs text-slate-warm-500 dark:text-parchment-400">
                    Isi data pelanggan
                </p>
            </div>
        </div>

        <form method="POST" action="{{ route('customers.store') }}" @submit="confirmSubmit($event)"
            x-data="customerForm()" class="grid grid-cols-1 gap-4 p-5 md:grid-cols-2 xl:grid-cols-4">
            @csrf

            {{-- Pelanggan ID (auto) --}}
            <div>
                <label for="customer-id"
                    class="mb-1.5 block text-xs font-semibold text-slate-warm-600 dark:text-parchment-300">
                    Pelanggan ID
                </label>

                <input id="customer-id" type="text" value="{{ $nextCustomerCode }}" readonly tabindex="-1"
                    class="h-11 w-full cursor-not-allowed rounded-lg border border-parchment-300 bg-parchment-50 px-3 font-mono text-sm text-slate-warm-400 dark:border-slate-warm-700 dark:bg-slate-warm-800/60 dark:text-parchment-500">

                <p class="mt-1 text-[11px] text-slate-warm-400 dark:text-parchment-500">
                    Dibuat otomatis oleh sistem.
                </p>
            </div>

            {{-- Nomer Pelanggan --}}
            <div>
                <label for="customer-number"
                    class="mb-1.5 block text-xs font-semibold text-slate-warm-600 dark:text-parchment-300">
                    Nomer Pelanggan <span class="text-red-500">*</span>
                </label>

                <input id="customer-number" name="customer_number" type="text" required maxlength="50"
                    value="{{ old('customer_number') }}" placeholder="Contoh: 081234567890"
                    class="h-11 w-full rounded-lg border border-parchment-300 bg-white px-3 text-sm text-ink-900 outline-none transition placeholder:text-slate-warm-400 focus:border-ink-900 focus:ring-2 focus:ring-ink-900/10 dark:border-slate-warm-700 dark:bg-slate-warm-900 dark:text-parchment-100 dark:focus:border-bronze-500">

                @error('customer_number')
                <p class="mt-1 text-[11px] font-medium text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Nama Pelanggan --}}
            <div>
                <label for="customer-name"
                    class="mb-1.5 block text-xs font-semibold text-slate-warm-600 dark:text-parchment-300">
                    Nama Pelanggan <span class="text-red-500">*</span>
                </label>

                <input id="customer-name" name="name" type="text" required maxlength="150" value="{{ old('name') }}"
                    placeholder="Contoh: Ahmad Junior"
                    class="h-11 w-full rounded-lg border border-parchment-300 bg-white px-3 text-sm text-ink-900 outline-none transition placeholder:text-slate-warm-400 focus:border-ink-900 focus:ring-2 focus:ring-ink-900/10 dark:border-slate-warm-700 dark:bg-slate-warm-900 dark:text-parchment-100 dark:focus:border-bronze-500">

                @error('name')
                <p class="mt-1 text-[11px] font-medium text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Nomor Kontrak (auto-generate) --}}
            <div x-data="{ typed: false }">
                <label for="contract-number"
                    class="mb-1.5 block text-xs font-semibold text-slate-warm-600 dark:text-parchment-300">
                    Nomor Kontrak
                </label>

                <input id="contract-number" name="contract_number" type="text" maxlength="100"
                    value="{{ old('contract_number') }}" placeholder="{{ $nextContractNumber }}"
                    @input="typed = $event.target.value.length > 0"
                    class="h-11 w-full rounded-lg border border-parchment-300 bg-white px-3 font-mono text-sm text-ink-900 outline-none transition placeholder:text-slate-warm-300 focus:border-ink-900 focus:ring-2 focus:ring-ink-900/10 dark:border-slate-warm-700 dark:bg-slate-warm-900 dark:text-parchment-100 dark:placeholder:text-parchment-600 dark:focus:border-bronze-500">

                <p x-show="!typed" class="mt-1 text-[11px] text-slate-warm-400 dark:text-parchment-500">
                    Kosongkan untuk memakai nomor otomatis di atas.
                </p>

                <p x-show="typed" x-cloak class="mt-1 text-[11px] font-medium text-bronze-700 dark:text-bronze-400">
                    Memakai nomor yang Anda ketik.
                </p>

                @error('contract_number')
                <p class="mt-1 text-[11px] font-medium text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Tanggal Aktif --}}
            <div>
                <label for="customer-active-date"
                    class="mb-1.5 block text-xs font-semibold text-slate-warm-600 dark:text-parchment-300">
                    Tanggal Aktif <span class="text-red-500">*</span>
                </label>

                <input id="customer-active-date" type="date" required x-model="activeDate"
                    class="h-11 w-full rounded-lg border border-parchment-300 bg-white px-3 text-sm text-ink-900 outline-none transition focus:border-ink-900 focus:ring-2 focus:ring-ink-900/10 dark:border-slate-warm-700 dark:bg-slate-warm-900 dark:text-parchment-100 dark:focus:border-bronze-500">

                <input type="hidden" name="active_date" :value="activeDate">

                @error('active_date')
                <p class="mt-1 text-[11px] font-medium text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Masa Aktif --}}
            <div>
                <label for="customer-active-months"
                    class="mb-1.5 block text-xs font-semibold text-slate-warm-600 dark:text-parchment-300">
                    Masa Aktif (bulan) <span class="text-red-500">*</span>
                </label>

                <input id="customer-active-months" type="number" required min="1" max="120" x-model="activeMonths"
                    placeholder="12"
                    class="h-11 w-full rounded-lg border border-parchment-300 bg-white px-3 text-sm text-ink-900 outline-none transition placeholder:text-slate-warm-400 focus:border-ink-900 focus:ring-2 focus:ring-ink-900/10 dark:border-slate-warm-700 dark:bg-slate-warm-900 dark:text-parchment-100 dark:focus:border-bronze-500">

                <input type="hidden" name="active_months" :value="activeMonths">

                @error('active_months')
                <p class="mt-1 text-[11px] font-medium text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Tanggal Selesai (otomatis) --}}
            <div>
                <label for="customer-finish-date"
                    class="mb-1.5 block text-xs font-semibold text-slate-warm-600 dark:text-parchment-300">
                    Tanggal Selesai
                </label>

                <input id="customer-finish-date" type="text" readonly tabindex="-1" :value="finishDateLabel"
                    placeholder="—"
                    class="h-11 w-full cursor-not-allowed rounded-lg border border-parchment-300 bg-parchment-50 px-3 text-sm text-slate-warm-500 dark:border-slate-warm-700 dark:bg-slate-warm-800/60 dark:text-parchment-400">
            </div>

            <div class="hidden xl:block"></div>

            @include('partials.customer.barang-input')

            @include('partials.customer.service-input')

            <div class="flex items-end md:col-span-2 xl:col-span-4">
                <button type="submit" class="btn-primary text-xs" :disabled="submitting"
                    :class="submitting ? 'opacity-70' : ''">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z" />
                        <polyline points="17 21 17 13 7 13 7 21" />
                        <polyline points="7 3 7 8 15 8" />
                    </svg>

                    <span x-text="submitting ? 'Menyimpan...' : 'Simpan / Tambah'"></span>
                </button>
            </div>
        </form>

        @include('partials.customer.form-script')
    </div>

    {{-- TABEL PELANGGAN --}}
    <div
        class="overflow-hidden rounded-2xl border border-parchment-300 bg-white shadow-theme-xs dark:border-slate-warm-800 dark:bg-slate-warm-900">

        <div
            class="flex flex-col gap-4 border-b border-parchment-200 px-5 py-4 dark:border-slate-warm-800 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h2 class="text-sm font-semibold text-ink-900 dark:text-parchment-50">Tabel Pelanggan</h2>
                <p class="mt-0.5 text-xs text-slate-warm-500 dark:text-parchment-400">
                    Tekan <strong>Lanjut</strong> pada baris pelanggan untuk menyusun kontraknya di Studio Editor.
                </p>
            </div>

            <div class="relative">
                <svg class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-slate-warm-400"
                    width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="11" cy="11" r="7" />
                    <path d="m20 20-4-4" />
                </svg>

                <input id="customers-search" type="search" placeholder="Cari pelanggan / kontrak..."
                    class="h-10 w-full rounded-lg border border-parchment-300 bg-white pl-9 pr-3 text-sm text-ink-900 outline-none transition placeholder:text-slate-warm-400 focus:border-ink-900 focus:ring-2 focus:ring-ink-900/10 dark:border-slate-warm-700 dark:bg-slate-warm-900 dark:text-parchment-100 sm:w-64">
            </div>
        </div>

        {{-- FILTER STATUS --}}
        <div class="flex flex-wrap items-center gap-2 border-b border-parchment-200 px-5 py-3 dark:border-slate-warm-800">
            @foreach ([
                'all' => 'Semua',
                'draft' => 'Draft',
                'on_progress' => 'On Progress',
                'on_review' => 'On Review',
                'revisi' => 'Revisi',
                'disetujui' => 'Disetujui',
            ] as $value => $label)
                <button type="button" data-status-filter="{{ $value }}"
                    class="rounded-full border px-3 py-1.5 text-[11px] font-semibold transition">
                    {{ $label }}
                </button>
            @endforeach
        </div>

        {{-- TABEL (diisi DataTables + Yajra) --}}
        <div class="p-3">
            <table id="customers-table" class="display w-full min-w-[1180px]">
                <thead>
                    <tr>
                        <th>Pelanggan ID</th>
                        <th>Nomer Pelanggan</th>
                        <th>Nama Pelanggan</th>
                        <th>Nomor Kontrak</th>
                        <th>Nama Kontrak</th>
                        <th>Tanggal Aktif</th>
                        <th>Masa Aktif</th>
                        <th>Tanggal Selesai</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>

    @push('styles')
<style>
    [x-cloak] { display: none !important; }
    .dt-container { font-size: 13px; }
    .dt-container .dt-layout-table { overflow-x: auto; }
    table.dataTable thead th { font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: .04em; white-space: nowrap; }
    table.dataTable tbody td { vertical-align: middle; }
</style>
@endpush

@push('scripts')
@vite('resources/js/datatables.js')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const DataTable = window.DataTable;
    let statusFilter = 'all';

    // Error ditangani sendiri lewat 'dt-error.dt' di bawah, bukan alert() bawaan.
    DataTable.ext.errMode = 'none';

    const esc = (s) => String(s ?? '').replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));

    const table = new DataTable('#customers-table', {
        processing: true,
        serverSide: true,
        autoWidth: false,
        ajax: {
            url: @json(route('customers.data')),
            data: (d) => { d.status = statusFilter; },
        },
        order: [[0, 'desc']],          // terbaru dulu
        pageLength: 10,
        lengthMenu: [10, 25, 50],
        layout: {
            topStart: null,            // search & length bawaan dimatikan,
            topEnd: null,              // kita pakai input search sendiri
            bottomStart: ['pageLength', 'info'],
            bottomEnd: 'paging',
        },
        language: {
            processing: 'Memuat data...',
            zeroRecords: 'Pelanggan tidak ditemukan',
            emptyTable: 'Belum ada pelanggan. Tambahkan lewat Form Pelanggan di atas.',
            info: 'Menampilkan _START_–_END_ dari _TOTAL_ data',
            infoEmpty: 'Tidak ada data',
            infoFiltered: '',
            lengthMenu: '_MENU_ / halaman',
            paginate: { previous: '‹', next: '›' },
        },
        columnDefs: [{ targets: '_all', defaultContent: '—' }],
        columns: [
            { data: 'id', name: 'id' },
            { data: 'customer_number', name: 'customer_number' },
            { data: 'name', name: 'name' },
            { data: 'contract_number', name: 'contract_number' },
            { data: 'contract_name', name: 'contract_name' },
            { data: 'active_date', name: 'active_date', searchable: false },
            { data: 'active_months', name: 'active_months', searchable: false },
            { data: 'finish_date', name: 'finish_date', searchable: false },
            { data: 'status', name: 'status', searchable: false },
            { data: 'action', name: 'action', orderable: false, searchable: false },
        ],
    });

    // Kartu ringkasan: isi dari JSON "counts" yang dikirim Yajra.
    table.on('xhr.dt', (e, settings, json) => {
        if (!json || !json.counts) return;
        document.querySelectorAll('[data-count]').forEach((el) => {
            el.textContent = json.counts[el.dataset.count] ?? 0;
        });
    });

    table.on('dt-error.dt', () => {
        Swal.fire({
            icon: 'error',
            title: 'Gagal memuat data',
            text: 'Data pelanggan gagal dimuat. Coba muat ulang halaman.',
            confirmButtonColor: '#1B2A4A',
        });
    });

    // Search (tunggu user berhenti mengetik 350 ms).
    const search = document.getElementById('customers-search');
    let searchTimer;
    search.addEventListener('input', () => {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(() => table.search(search.value.trim()).draw(), 350);
    });

    // Filter status (pill).
    const activeCls = ['border-ink-900', 'bg-ink-900', 'text-white', 'dark:border-bronze-500', 'dark:bg-bronze-500', 'dark:text-ink-900'];
    const idleCls = ['border-parchment-300', 'text-slate-warm-600', 'hover:border-ink-900', 'hover:text-ink-900', 'dark:border-slate-warm-700', 'dark:text-parchment-300', 'dark:hover:border-bronze-500'];
    const pills = document.querySelectorAll('[data-status-filter]');
    const paintPills = () => pills.forEach((p) => {
        const on = p.dataset.statusFilter === statusFilter;
        activeCls.forEach((c) => p.classList.toggle(c, on));
        idleCls.forEach((c) => p.classList.toggle(c, !on));
    });
    pills.forEach((p) => p.addEventListener('click', () => {
        statusFilter = p.dataset.statusFilter;
        paintPills();
        table.draw();
    }));
    paintPills();

    // Muat ulang data tanpa pindah halaman; kalau halaman sekarang sudah
    // kosong (mis. setelah hapus data terakhir), mundur ke halaman terakhir.
    const reloadTable = () => table.ajax.reload(() => {
        const info = table.page.info();
        const last = Math.max(info.pages - 1, 0);
        if (info.page > last) table.page(last).draw('page');
    }, false);

    // ---- Aksi per baris ----
    const deleteCustomer = async (url, name) => {
        const result = await Swal.fire({
            icon: 'warning',
            title: 'Hapus pelanggan?',
            html: `Pelanggan <strong>${esc(name)}</strong> beserta barang & service-nya akan dihapus.`,
            showCancelButton: true,
            confirmButtonText: 'Ya, hapus',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#dc2626',
        });
        if (!result.isConfirmed) return;

        try {
            await window.axios.delete(url);
            reloadTable();
            Swal.fire({ icon: 'success', title: 'Terhapus', text: 'Pelanggan berhasil dihapus.', confirmButtonColor: '#1B2A4A', timer: 1600, showConfirmButton: false });
        } catch (error) {
            console.error(error);
            Swal.fire({ icon: 'error', title: 'Gagal', text: 'Pelanggan gagal dihapus.', confirmButtonColor: '#1B2A4A' });
        }
    };

    const approveCustomer = async (url) => {
        const konfirmasi = await Swal.fire({
            icon: 'question',
            title: 'Setujui dokumen',
            html: '<div style="text-align:left;font-size:13px;line-height:1.7">' +
                '<p>Upload berkas kontrak final (PDF, maks 10 MB). Berkas ini ' +
                'menggantikan dokumen kontrak pelanggan dan dipakai saat Unduh PDF.</p></div>',
            input: 'file',
            inputAttributes: { accept: 'application/pdf', 'aria-label': 'Pilih berkas kontrak (PDF)' },
            inputValidator: (file) => {
                if (!file) return 'Pilih berkas kontrak terlebih dahulu.';
                if (file.type && file.type !== 'application/pdf') return 'Berkas kontrak harus berformat PDF.';
                if (file.size > 10 * 1024 * 1024) return 'Ukuran berkas kontrak maksimal 10 MB.';
                return null;
            },
            showCancelButton: true,
            confirmButtonText: 'Setujui & Upload',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#16a34a',
        });
        if (!konfirmasi.isConfirmed || !konfirmasi.value) return;

        try {
            const formData = new FormData();
            formData.append('file', konfirmasi.value);
            const { data } = await window.axios.post(url, formData);

            Swal.fire({
                icon: 'success',
                title: 'Disetujui',
                html: 'Dokumen disetujui & berkas kontrak tersimpan.' +
                    (data?.downloadUrl
                        ? ' <a href="' + data.downloadUrl + '" target="_blank" style="color:#1B2A4A;font-weight:600;text-decoration:underline;">Unduh PDF</a>'
                        : ''),
                confirmButtonColor: '#1B2A4A',
                timer: 1800,
                showConfirmButton: false,
            });
            setTimeout(reloadTable, 1200);
        } catch (error) {
            console.error(error);
            Swal.fire({ icon: 'error', title: 'Gagal', text: error?.response?.data?.message || 'Dokumen gagal disetujui.', confirmButtonColor: '#1B2A4A' });
        }
    };

    const requestRevision = async (url) => {
        const result = await Swal.fire({
            icon: 'warning',
            title: 'Minta revisi?',
            text: 'Dokumen akan dikembalikan ke status Revisi agar dapat diperbaiki.',
            showCancelButton: true,
            confirmButtonText: 'Ya, minta revisi',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#ea580c',
        });
        if (!result.isConfirmed) return;

        try {
            await window.axios.patch(url, { status: 'revisi' });
            Swal.fire({ icon: 'success', title: 'Revisi diminta', text: 'Dokumen kembali ke status Revisi.', confirmButtonColor: '#1B2A4A', timer: 1600, showConfirmButton: false });
            setTimeout(reloadTable, 1200);
        } catch (error) {
            console.error(error);
            Swal.fire({ icon: 'error', title: 'Gagal', text: error?.response?.data?.message || 'Tidak dapat meminta revisi dokumen.', confirmButtonColor: '#1B2A4A' });
        }
    };

    // Satu listener untuk semua tombol di dalam tabel (baris dibuat ulang tiap draw).
    document.getElementById('customers-table').addEventListener('click', (e) => {
        const btn = e.target.closest('[data-action]');
        if (!btn) return;
        const { action, url, name } = btn.dataset;
        if (action === 'delete') deleteCustomer(url, name);
        if (action === 'approve') approveCustomer(url);
        if (action === 'revision') requestRevision(url);
    });
});
</script>
@endpush

@endsection