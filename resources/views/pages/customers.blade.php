@extends('layouts.app')

@section('content')

@php
    $customerData = $customers->map(function ($customer) {
        $status = strtolower($customer->status ?? 'draft');

        return [
            'id' => 'PLG-' . str_pad($customer->id, 5, '0', STR_PAD_LEFT),
            'databaseId' => $customer->id,
            'customerNumber' => $customer->customer_number,
            'name' => $customer->name,
            'contractNumber' => $customer->contract_number,
            'contractName' => $customer->contract_name ?? '—',
            'activeDate' => $customer->active_date
                ? $customer->active_date->format('d M Y')
                : '—',
            'activeMonths' => $customer->active_months
                ? $customer->active_months . ' bulan'
                : '—',
            'finishDate' => $customer->finish_date
                ? $customer->finish_date->format('d M Y')
                : '—',
            'barangCount' => $customer->barang_count ?? 0,
            'serviceCount' => $customer->services_count ?? 0,
            'status' => $status,
            'statusUpdated' => $customer->updated_at ? $customer->updated_at->format('d M Y H:i') : null,
            'statusLabel' => $customer->statusLabel(),
            // Lanjut langsung ke halaman pilih template (tanpa pilihan upload/baru).
            'createUrl' => route('documents.create', $customer->id),
            'deleteUrl' => route('customers.destroy', $customer->id),

            // Dokumen terbaru milik kontrak — sumber aksi "Revisi".
            'documentId' => $customer->documents->first()?->id,
            'documentStatus' => $customer->documents->first()?->status,

            // Revisi: keluarkan kontrak yang sudah disetujui dari Menu S.O.F
            // (berkas PDF dihapus) dan kembalikan statusnya ke On Progress.
            'canRevise' => $customer->documents->first()?->status === 'disetujui',
            'reviseUrl' => $customer->documents->first()
                ? route('documents.revise', $customer->documents->first()->id)
                : null,
        ];
    })->toArray();
@endphp

<script>
    window.customerPageData = @json($customerData);
    window.customerNextContract = @json($nextContractNumber);
    window.flashSuccess = @json(session('success'));

    function customersPage() {
        return {
            customers: window.customerPageData,
            filterStatus: 'all',
            searchQuery: '',
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

            get filteredCustomers() {
                const search = this.searchQuery.toLowerCase();

                return this.customers.filter(customer => {
                    const matchesStatus =
                        this.filterStatus === 'all' ||
                        customer.status === this.filterStatus;

                    const matchesSearch =
                        customer.name.toLowerCase().includes(search) ||
                        customer.id.toLowerCase().includes(search) ||
                        customer.customerNumber.toLowerCase().includes(search) ||
                        customer.contractNumber.toLowerCase().includes(search) ||
                        customer.contractName.toLowerCase().includes(search);

                    return matchesStatus && matchesSearch;
                });
            },

            countByStatus(status) {
                return this.customers.filter(c => c.status === status).length;
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

            async deleteCustomer(customer) {
                const result = await Swal.fire({
                    icon: 'warning',
                    title: 'Hapus pelanggan?',
                    html: `Pelanggan <strong>${customer.name}</strong> beserta barang & service-nya akan dihapus.`,
                    showCancelButton: true,
                    confirmButtonText: 'Ya, hapus',
                    cancelButtonText: 'Batal',
                    confirmButtonColor: '#dc2626',
                });

                if (!result.isConfirmed) return;

                try {
                    await window.axios.delete(customer.deleteUrl);

                    this.customers = this.customers.filter(
                        c => c.databaseId !== customer.databaseId
                    );

                    Swal.fire({
                        icon: 'success',
                        title: 'Terhapus',
                        text: 'Pelanggan berhasil dihapus.',
                        confirmButtonColor: '#1B2A4A',
                        timer: 1600,
                        showConfirmButton: false,
                    });
                } catch (error) {
                    console.error(error);

                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: 'Pelanggan gagal dihapus.',
                        confirmButtonColor: '#1B2A4A',
                    });
                }
            },

            // Revisi dokumen kontrak yang sudah disetujui: keluarkan dari
            // Menu S.O.F (berkas PDF dihapus) dan kembalikan status kontrak
            // ke On Progress supaya bisa diedit ulang di Studio Editor.
            async reviseCustomer(customer) {
                const result = await Swal.fire({
                    icon: 'question',
                    title: 'Ingin merevisi dokumen?',
                    text: 'Dokumen akan dikeluarkan dari Menu S.O.F dan status kontrak kembali menjadi On Progress agar dapat diedit ulang di Studio Editor.',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, revisi',
                    cancelButtonText: 'Batal',
                    confirmButtonColor: '#ea580c',
                });

                if (!result.isConfirmed) return;

                try {
                    await window.axios.post(customer.reviseUrl);

                    Swal.fire({
                        icon: 'success',
                        title: 'Siap direvisi',
                        text: 'Dokumen dikeluarkan dari S.O.F. Status kontrak kembali ke On Progress.',
                        confirmButtonColor: '#1B2A4A',
                        timer: 1800,
                        showConfirmButton: false,
                    });

                    // Muat ulang supaya badge status, kartu ringkasan, dan isi
                    // Menu S.O.F ikut terbarui.
                    setTimeout(() => window.location.reload(), 1200);
                } catch (error) {
                    console.error(error);

                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: error?.response?.data?.message || 'Dokumen gagal direvisi.',
                        confirmButtonColor: '#1B2A4A',
                    });
                }
            },

            // Kelas badge status untuk tabel pelanggan.
            statusClass(status) {
                return {
                    draft: 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-500/15 dark:text-amber-300 dark:border-amber-500/30',
                    on_progress: 'bg-sky-50 text-sky-700 border-sky-200 dark:bg-sky-500/15 dark:text-sky-300 dark:border-sky-500/30',
                    on_review: 'bg-violet-50 text-violet-700 border-violet-200 dark:bg-violet-500/15 dark:text-violet-300 dark:border-violet-500/30',
                    revisi: 'bg-orange-50 text-orange-700 border-orange-200 dark:bg-orange-500/15 dark:text-orange-300 dark:border-orange-500/30',
                    disetujui: 'bg-green-50 text-green-700 border-green-200 dark:bg-green-500/15 dark:text-green-300 dark:border-green-500/30',
                }[status] || 'bg-parchment-100 text-slate-warm-600 border-parchment-300 dark:bg-slate-warm-800 dark:text-parchment-300 dark:border-slate-warm-700';
            },
        };
    }
</script>

@php
    $summaryCards = [
        [
            'key' => 'all',
            'label' => 'Total Pelanggan',
            'icon' => '<path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/>',
            'iconClass' => 'bg-parchment-100 text-ink-900 dark:bg-slate-warm-800 dark:text-parchment-200',
        ],
        [
            'key' => 'draft',
            'label' => 'Draft',
            'icon' => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
            'iconClass' => 'bg-amber-50 text-amber-700 dark:bg-amber-900/20 dark:text-amber-400',
        ],
        [
            'key' => 'on_progress',
            'label' => 'On Progress',
            'icon' => '<path d="M3 12a9 9 0 1 0 3-6.7L3 8"/><path d="M3 3v5h5"/>',
            'iconClass' => 'bg-sky-50 text-sky-700 dark:bg-sky-900/20 dark:text-sky-400',
        ],
        [
            'key' => 'on_review',
            'label' => 'On Review',
            'icon' => '<circle cx="11" cy="11" r="7"/><path d="m20 20-4-4"/>',
            'iconClass' => 'bg-violet-50 text-violet-700 dark:bg-violet-900/20 dark:text-violet-400',
        ],
        [
            'key' => 'revisi',
            'label' => 'Revisi',
            'icon' => '<path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4Z"/>',
            'iconClass' => 'bg-orange-50 text-orange-700 dark:bg-orange-900/20 dark:text-orange-400',
        ],
        [
            'key' => 'disetujui',
            'label' => 'Disetujui',
            'icon' => '<path d="m20 6-11 11-5-5"/>',
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
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2">
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

        <a href="{{ route('editor.start') }}" class="btn-secondary shrink-0 text-xs">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path
                    d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
            </svg>

            Studio Editor
        </a>
    </div>

    {{-- SUMMARY CARDS --}}
    <div class="grid grid-cols-2 gap-3 md:grid-cols-3 lg:grid-cols-6">
        @foreach ($summaryCards as $card)
            @php
                $counterExpression = $card['key'] === 'all'
                    ? 'customers.length'
                    : "countByStatus('" . $card['key'] . "')";
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

{{-- FORM PELANGGAN --}}
    <div
        class="overflow-hidden rounded-2xl border border-parchment-300 bg-white shadow-theme-xs dark:border-slate-warm-800 dark:bg-slate-warm-900">

        <div class="flex items-start gap-3 border-b border-parchment-200 px-5 py-4 dark:border-slate-warm-800">
            <span
                class="mt-0.5 inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-xl bg-bronze-100 text-bronze-800 dark:bg-bronze-500/20 dark:text-bronze-300">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-width="2">
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
            x-data="customerForm()"
            class="grid grid-cols-1 gap-4 p-5 md:grid-cols-2 xl:grid-cols-4">
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

                <input id="customer-name" name="name" type="text" required maxlength="150"
                    value="{{ old('name') }}" placeholder="Contoh: Ahmad Junior"
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

                <input id="customer-active-months" type="number" required min="1" max="120"
                    x-model="activeMonths" placeholder="12"
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
                <button type="submit" class="btn-primary text-xs" :disabled="submitting" :class="submitting ? 'opacity-70' : ''">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2">
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
                <h2 class="text-sm font-semibold text-ink-900 dark:text-parchment-50">
                    Tabel Pelanggan
                </h2>

                <p class="mt-0.5 text-xs text-slate-warm-500 dark:text-parchment-400">
                    Tekan <strong>Lanjut</strong> pada baris pelanggan untuk menyusun kontraknya di Studio Editor.
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

                    <input type="search" x-model="searchQuery" placeholder="Cari pelanggan / kontrak..."
                        class="h-10 w-full rounded-lg border border-parchment-300 bg-white pl-9 pr-3 text-sm text-ink-900 outline-none transition placeholder:text-slate-warm-400 focus:border-ink-900 focus:ring-2 focus:ring-ink-900/10 dark:border-slate-warm-700 dark:bg-slate-warm-900 dark:text-parchment-100 sm:w-64">
                </div>
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
                <button type="button" @click="filterStatus = '{{ $value }}'"
                    class="rounded-full border px-3 py-1.5 text-[11px] font-semibold transition"
                    :class="filterStatus === '{{ $value }}'
                        ? 'border-ink-900 bg-ink-900 text-white dark:border-bronze-500 dark:bg-bronze-500 dark:text-ink-900'
                        : 'border-parchment-300 text-slate-warm-600 hover:border-ink-900 hover:text-ink-900 dark:border-slate-warm-700 dark:text-parchment-300 dark:hover:border-bronze-500'">
                    {{ $label }}
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
                            'Nomer Pelanggan',
                            'Nama Pelanggan',
                            'Nomor Kontrak',
                            'Nama Kontrak',
                            'Tanggal Aktif',
                            'Masa Aktif',
                            'Tanggal Selesai',
                            'Status',
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
                        <tr class="border-b border-parchment-100 transition hover:bg-parchment-50/70 dark:border-slate-warm-800 dark:hover:bg-white/[0.03]">
                            <td class="whitespace-nowrap px-4 py-3.5">
                                <span class="font-mono text-xs font-semibold text-ink-900 dark:text-parchment-100"
                                    x-text="customer.id"></span>
                            </td>

                            <td class="whitespace-nowrap px-4 py-3.5 text-sm text-ink-800 dark:text-parchment-200"
                                x-text="customer.customerNumber"></td>

                            <td class="px-4 py-3.5">
                                <div class="text-sm font-semibold text-ink-900 dark:text-parchment-50"
                                    x-text="customer.name"></div>

                                <div class="mt-0.5 text-[11px] text-slate-warm-400 dark:text-parchment-500">
                                    <span x-text="customer.barangCount + ' barang'"></span>
                                    <span class="mx-1">·</span>
                                    <span x-text="customer.serviceCount + ' service'"></span>
                                </div>
                            </td>

                            <td class="whitespace-nowrap px-4 py-3.5 font-mono text-xs text-slate-warm-600 dark:text-parchment-300"
                                x-text="customer.contractNumber"></td>

                            <td class="px-4 py-3.5 text-sm text-ink-800 dark:text-parchment-200"
                                x-text="customer.contractName"></td>

                            <td class="whitespace-nowrap px-4 py-3.5 text-sm text-ink-800 dark:text-parchment-200"
                                x-text="customer.activeDate"></td>

                            <td class="whitespace-nowrap px-4 py-3.5 text-sm text-ink-800 dark:text-parchment-200"
                                x-text="customer.activeMonths"></td>

                            <td class="whitespace-nowrap px-4 py-3.5 text-sm text-ink-800 dark:text-parchment-200"
                                x-text="customer.finishDate"></td>

                            <td class="whitespace-nowrap px-4 py-3.5">
                                <span
                                    class="inline-flex items-center rounded-full border px-2.5 py-1 text-[11px] font-semibold"
                                    :class="statusClass(customer.status)"
                                    x-text="customer.statusLabel"></span>
                                <p x-show="customer.status !== 'draft' && customer.statusUpdated"
                                    class="mt-1 text-[10px] leading-tight text-slate-warm-400 dark:text-parchment-500"
                                    x-text="'Update: ' + customer.statusUpdated"></p>
                            </td>

                            <td class="whitespace-nowrap px-4 py-3.5">
                                <div class="flex items-center gap-2">
                                    {{-- Lanjut → pilih template (konteks pelanggan) --}}
                                    <a :href="customer.createUrl"
                                        class="inline-flex h-8 items-center gap-1.5 rounded-lg border border-parchment-300 px-2.5 text-[11px] font-semibold text-ink-900 transition hover:border-ink-900 hover:bg-ink-900 hover:text-white dark:border-slate-warm-700 dark:text-parchment-200 dark:hover:border-bronze-500 dark:hover:bg-bronze-500 dark:hover:text-ink-900">
                                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none"
                                            stroke="currentColor" stroke-width="2">
                                            <path d="M12 20h9" />
                                            <path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4Z" />
                                        </svg>

                                        Lanjut
                                    </a>

                                    {{-- Revisi — keluarkan kontrak yang sudah disetujui dari S.O.F
                                         & kembalikan statusnya ke On Progress --}}
                                    <template x-if="customer.canRevise">
                                        <button type="button" @click="reviseCustomer(customer)"
                                            class="inline-flex h-8 items-center gap-1.5 rounded-lg border border-orange-400 bg-white px-2.5 text-[11px] font-semibold text-orange-700 transition hover:bg-orange-50 dark:border-orange-500/60 dark:bg-transparent dark:text-orange-300 dark:hover:bg-orange-500/10"
                                            title="Keluarkan dari S.O.F &amp; kembalikan ke On Progress">
                                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none"
                                                stroke="currentColor" stroke-width="2">
                                                <path d="M12 20h9" />
                                                <path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4Z" />
                                            </svg>

                                            Revisi
                                        </button>
                                    </template>

                                    {{-- Hapus pelanggan --}}
                                    <button type="button" @click="deleteCustomer(customer)"
                                        class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-transparent text-slate-warm-400 transition hover:border-red-200 hover:bg-red-50 hover:text-red-600 dark:hover:border-red-900/40 dark:hover:bg-red-900/20"
                                        title="Hapus pelanggan">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none"
                                            stroke="currentColor" stroke-width="2">
                                            <polyline points="3 6 5 6 21 6" />
                                            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6" />
                                            <path d="M10 11v6" />
                                            <path d="M14 11v6" />
                                            <path d="M9 6V4a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v2" />
                                        </svg>
                                    </button>
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
                    <circle cx="11" cy="11" r="7" />
                    <path d="m20 20-4-4" />
                </svg>
            </div>

            <h3 class="mt-4 text-sm font-semibold text-ink-900 dark:text-parchment-50">
                Pelanggan tidak ditemukan
            </h3>

            <p class="mt-1 text-xs text-slate-warm-500">
                Tambahkan pelanggan baru lewat Form Pelanggan di atas, atau ubah kata pencarian/filter status.
            </p>
        </div>
    </div>
</div>

@endsection