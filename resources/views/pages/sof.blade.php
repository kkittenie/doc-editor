@extends('layouts.app')

@section('content')
@php
$isAdmin = auth()->user()->hasRole('admin');
@endphp

<div class="container mx-auto px-4 py-10">
    <div class="mb-6 flex flex-col items-start justify-between gap-4 sm:flex-row sm:items-center">
        <div>
            <h1 class="font-serif text-2xl font-bold text-ink-900 dark:text-parchment-50">Menu S.O.F</h1>
        </div>
        @if($isAdmin)
        <a href="{{ route('sof.create') }}" class="btn-primary shrink-0 text-xs">+ Tambah S.O.F</a>
        @endif
    </div>


    {{-- SUMMARY CARDS --}}
    <div class="mb-6 grid grid-cols-2 gap-4">
        <div
            class="rounded-xl border border-parchment-300 bg-white p-4 text-center dark:border-slate-warm-800 dark:bg-slate-warm-900">
            <p class="text-2xl font-bold text-ink-900 dark:text-parchment-50" data-count="total">0</p>
            <p class="text-[10px] uppercase text-slate-warm-500">Total S.O.F</p>
        </div>
        <div
            class="rounded-xl border border-parchment-300 bg-white p-4 text-center dark:border-slate-warm-800 dark:bg-slate-warm-900">
            <p class="text-2xl font-bold text-green-700" data-count="selesai">0</p>
            <p class="text-[10px] uppercase text-slate-warm-500">Selesai</p>
        </div>
    </div>

    @if (session('success'))
    <div class="mb-4 rounded-xl border border-green-200 bg-green-50 p-3 text-sm text-green-700">{{ session('success') }}
    </div>
    @endif

    {{-- FILTER + SEARCH --}}
    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex items-center gap-2">
            @foreach (['all' => 'Semua', 'ready' => 'Dgn Berkas', 'pending' => 'Tanpa Berkas'] as $value => $label)
            <button type="button" data-berkas-filter="{{ $value }}"
                class="inline-flex h-8 items-center rounded-lg px-3 text-xs font-semibold">{{ $label }}</button>
            @endforeach
        </div>
        <div class="relative w-full sm:w-64">
            <input id="sof-search" type="search" placeholder="Cari order / pelanggan / kontrak..."
                class="w-full rounded-xl border border-parchment-300 bg-white px-3 py-2 pl-9 text-sm text-ink-900 focus:border-bronze-500/50 focus:ring-0 dark:border-slate-warm-700 dark:bg-slate-warm-800">
            <svg class="absolute left-3 top-2.5 h-4 w-4 text-slate-warm-400" fill="none" stroke="currentColor"
                viewBox="0 0 24 24">
                <circle cx="11" cy="11" r="8"></circle>
                <line x1="21" y1="21" x2="15.636" y2="15.636"></line>
            </svg>
        </div>
    </div>

    {{-- TABLE (diisi DataTables + Yajra) --}}
    <div class="rounded-xl border border-parchment-300 bg-white p-3 dark:border-slate-warm-800 dark:bg-slate-warm-900">
        <table id="sof-table" class="display w-full min-w-[900px]">
            <thead>
                <tr>
                    <th>Order</th>
                    <th>Pelanggan</th>
                    <th>Kontrak</th>
                    <th>Periode</th>
                    <th>Nilai</th>
                    <th>Berkas</th>
                    <th>Status</th>
                    <th class="text-right">Aksi</th>
                </tr>
            </thead>
        </table>
    </div>
</div>

@push('styles')
<style>
    .dt-container {
        font-size: 13px;
    }

    .dt-container .dt-layout-table {
        overflow-x: auto;
    }

    table.dataTable thead th {
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: .04em;
        white-space: nowrap;
    }

    table.dataTable tbody td {
        vertical-align: middle;
    }
</style>
@endpush

@push('scripts')
@vite('resources/js/datatables.js')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const DataTable = window.DataTable;
        let berkasFilter = 'all';

        DataTable.ext.errMode = 'none';

        const table = new DataTable('#sof-table', {
            processing: true,
            serverSide: true,
            autoWidth: false,
            ajax: {
                url: @json(route('sof.data')),
                data: (d) => { d.berkas = berkasFilter; },
            },
            order: [[0, 'desc']],          // terbaru dulu
            pageLength: 10,
            lengthMenu: [10, 25, 50],
            layout: {
                topStart: null,
                topEnd: null,
                bottomStart: ['pageLength', 'info'],
                bottomEnd: 'paging',
            },
            language: {
                processing: 'Memuat data...',
                zeroRecords: 'Tidak ada S.O.F ditemukan.',
                emptyTable: 'Belum ada S.O.F.',
                info: 'Menampilkan _START_–_END_ dari _TOTAL_ data',
                infoEmpty: 'Tidak ada data',
                infoFiltered: '',
                lengthMenu: '_MENU_ / halaman',
                paginate: { previous: '‹', next: '›' },
            },
            columnDefs: [{ targets: '_all', defaultContent: '—' }],
            columns: [
                { data: 'order_number', name: 'order_number' },
                { data: 'customer_name', name: 'customer_name' },
                { data: 'contract_number', name: 'contract_number' },
                { data: 'active_date', name: 'active_date', searchable: false },
                { data: 'total_value', name: 'total_value', searchable: false },
                { data: 'has_file', name: 'has_file', orderable: false, searchable: false },
                { data: 'status', name: 'status', searchable: false },
                { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-right' },
            ],
        });

        table.on('xhr.dt', (e, settings, json) => {
            if (!json || !json.counts) return;
            document.querySelectorAll('[data-count]').forEach((el) => {
                el.textContent = json.counts[el.dataset.count] ?? 0;
            });
        });

        table.on('dt-error.dt', () => {
            Swal.fire({ icon: 'error', title: 'Gagal memuat data', text: 'Data S.O.F gagal dimuat. Coba muat ulang halaman.' });
        });

        // Search (debounce 350 ms).
        const search = document.getElementById('sof-search');
        let searchTimer;
        search.addEventListener('input', () => {
            clearTimeout(searchTimer);
            searchTimer = setTimeout(() => table.search(search.value.trim()).draw(), 350);
        });

        // Filter berkas.
        const activeCls = ['bg-ink-900', 'text-white'];
        const idleCls = ['bg-parchment-100', 'text-slate-warm-700'];
        const pills = document.querySelectorAll('[data-berkas-filter]');
        const paintPills = () => pills.forEach((p) => {
            const on = p.dataset.berkasFilter === berkasFilter;
            activeCls.forEach((c) => p.classList.toggle(c, on));
            idleCls.forEach((c) => p.classList.toggle(c, !on));
        });
        pills.forEach((p) => p.addEventListener('click', () => {
            berkasFilter = p.dataset.berkasFilter;
            paintPills();
            table.draw();
        }));
        paintPills();

        const reloadTable = () => table.ajax.reload(() => {
            const info = table.page.info();
            const last = Math.max(info.pages - 1, 0);
            if (info.page > last) table.page(last).draw('page');
        }, false);

        const deleteSof = async (url) => {
            const r = await Swal.fire({
                icon: 'warning', title: 'Hapus S.O.F?',
                text: 'Berkas dan data akan dihapus permanen. Lanjutkan?',
                showCancelButton: true, confirmButtonText: 'Ya, hapus',
                cancelButtonText: 'Batal', confirmButtonColor: '#dc2626',
            });
            if (!r.isConfirmed) return;
            try {
                await window.axios.delete(url);
                reloadTable();
                Swal.fire({ icon: 'success', title: 'Dihapus', timer: 1200, showConfirmButton: false });
            } catch (e) {
                Swal.fire({ icon: 'error', title: 'Gagal', text: 'Tidak dapat menghapus S.O.F.' });
            }
        };

        document.getElementById('sof-table').addEventListener('click', (e) => {
            const btn = e.target.closest('[data-action]');
            if (!btn) return;
            if (btn.dataset.action === 'delete') deleteSof(btn.dataset.url);
            if (btn.dataset.action === 'no-file') {
                Swal.fire({ icon: 'warning', title: 'Tidak ada berkas', text: 'S.O.F ini belum memiliki berkas PDF.' });
            }
        });
    });
</script>
@endpush
@endsection