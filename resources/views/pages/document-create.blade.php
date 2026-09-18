@extends('layouts.app')

@section('content')
@php
    $selectedCustomer = $selectedCustomer ?? null;

    // Daftar template kontrak (grid pemilihan template di bawah tabel
    // barang/service). Key harus sama dengan ContractTemplates di server.
    $templates = [
        ['key' => 'kontrak-kemitraan', 'code' => 'TPL-KM-05', 'name' => 'Kontrak Kemitraan', 'desc' => 'Perjanjian kerja sama jual kembali jasa layanan akses internet.'],
        ['key' => 'kontrak-colocation', 'code' => 'TPL-CO-06', 'name' => 'Kontrak Colocation', 'desc' => 'Perjanjian colocation perangkat pada data center.'],
        ['key' => 'kontrak-managed-service', 'code' => 'TPL-MS-07', 'name' => 'Kontrak Managed Service', 'desc' => 'Perjanjian pengelolaan layanan TI (managed service).'],
        ['key' => 'kontrak-soho', 'code' => 'TPL-SH-08', 'name' => 'Jasa SOHO', 'desc' => 'Perjanjian berlangganan jasa SOHO untuk pelanggan.'],
        ['key' => 'kontrak-payung', 'code' => 'TPL-KP-09', 'name' => 'Kontrak Payung Metro', 'desc' => 'Kontrak payung berlangganan jasa Metro Fiber Optik.'],
    ];

    $defaultActiveDate = old('active_date', now()->toDateString());
    $defaultMonths = old('active_months', $selectedCustomer->active_months ?? 12);
@endphp

<div x-data="{
        loadingTemplate: null,
        selectedTemplate: '',
        bodyHtml: '',
        headerHtml: '<p></p>',
        footerHtml: '<p></p>',

        // --- Detail kontrak ---
        activeDate: @js($defaultActiveDate),
        activeMonths: @js((string) $defaultMonths),

        // --- Data barang & service (opsional, boleh banyak) ---
        itemsBarang: [],
        itemsService: [],
        barangDraft: { name: '', quantity: 1, price: null },
        serviceDraft: { name: '', price: null },

        // Tanggal Selesai dihitung otomatis: Tanggal Aktif + Masa Aktif (bulan).
        // Hari akhir bulan di-clamp agar hasilnya sama dengan hitungan server
        // (Carbon addMonthsNoOverflow) — mis. 31 Jan + 1 bulan = 28 Feb.
        get finishDate() {
            if (!this.activeDate || !this.activeMonths) return '';

            const parts = String(this.activeDate).split('-').map(Number);
            const year = parts[0];
            const month = parts[1];
            const day = parts[2];

            if (!year || !month || !day) return '';

            const months = parseInt(this.activeMonths, 10);
            if (!months || months < 1) return '';

            const target = (month - 1) + months;
            const targetYear = year + Math.floor(target / 12);
            const targetMonth = ((target % 12) + 12) % 12;

            // Hari terakhir bulan tujuan (clamp).
            const lastDay = new Date(Date.UTC(targetYear, targetMonth + 1, 0)).getUTCDate();
            const safeDay = Math.min(day, lastDay);

            return targetYear + '-'
                + String(targetMonth + 1).padStart(2, '0') + '-'
                + String(safeDay).padStart(2, '0');
        },

        get finishDateLabel() {
            if (!this.finishDate) return '';

            const parts = this.finishDate.split('-');
            const monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];

            return parseInt(parts[2], 10) + ' ' + monthNames[parseInt(parts[1], 10) - 1] + ' ' + parts[0];
        },

        // Rupiah untuk pratinjau tabel (nilai yang dikirim tetap angka murni).
        formatRupiah(value) {
            const number = Number(value || 0);

            return 'Rp ' + number.toLocaleString('id-ID', { maximumFractionDigits: 2 });
        },

        itemCode(prefix, index) {
            return prefix + '-' + String(index + 1).padStart(5, '0');
        },

        addBarang() {
            if (!this.barangDraft.name.trim()) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Nama barang kosong',
                    text: 'Isi nama barang terlebih dahulu.',
                    confirmButtonColor: '#1B2A4A',
                });
                return;
            }

            this.itemsBarang.push({
                uid: 'brg-' + Date.now() + '-' + this.itemsBarang.length,
                name: this.barangDraft.name.trim(),
                quantity: Math.max(1, parseInt(this.barangDraft.quantity, 10) || 1),
                price: Number(this.barangDraft.price || 0),
            });

            this.barangDraft = { name: '', quantity: 1, price: null };
        },

        addService() {
            if (!this.serviceDraft.name.trim()) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Nama service kosong',
                    text: 'Isi nama service terlebih dahulu.',
                    confirmButtonColor: '#1B2A4A',
                });
                return;
            }

            this.itemsService.push({
                uid: 'svc-' + Date.now() + '-' + this.itemsService.length,
                name: this.serviceDraft.name.trim(),
                price: Number(this.serviceDraft.price || 0),
            });

            this.serviceDraft = { name: '', price: null };
        },

        removeBarang(index) {
            this.itemsBarang.splice(index, 1);
        },

        removeService(index) {
            this.itemsService.splice(index, 1);
        },

        // Konfirmasi lalu kirim form (data disimpan ke dokumen + pelanggan).
        async submitForm(event) {
            const form = event.target;

            if (!form.reportValidity()) return;

            event.preventDefault();

            if (!this.selectedTemplate) {
                Swal.fire({
                    icon: 'info',
                    title: 'Template belum dipilih',
                    text: 'Pilih salah satu template dokumen di bagian bawah halaman.',
                    confirmButtonColor: '#1B2A4A',
                });
                return;
            }

            const result = await Swal.fire({
                icon: 'question',
                title: 'Simpan & lanjut ke editor?',
                text: 'Detail kontrak serta data barang/service akan disimpan.',
                showCancelButton: true,
                confirmButtonText: 'Ya, lanjut',
                cancelButtonText: 'Batal',
                confirmButtonColor: '#1B2A4A',
            });

            if (!result.isConfirmed) return;

            form.submit();
        },

        async loadTemplate(key) {
            this.loadingTemplate = key;
            this.selectedTemplate = key;

            try {
                const res = await window.axios.get('/documents/template/' + key);
                const t = res.data;

                if (t.title) {
                    const titleInput = document.getElementById('header-judul');
                    if (titleInput) titleInput.value = t.title;
                }

                if (t.header_data?.nomorSurat) {
                    const nomorInput = document.getElementById('header-nomor');
                    if (nomorInput) nomorInput.value = t.header_data.nomorSurat;
                }

                if (t.body_html) {
                    this.bodyHtml = t.body_html;
                }

                // Isi otomatis section HEADER & FOOTER dari template.
                // Template ber-cover mengirim HTML jadi (header_content =
                // ikon pihak pertama, footer_content = identitas pihak +
                // paraf + stample/materai); sisanya dirangkai dari data kop.
                if (t.header_content) {
                    this.headerHtml = t.header_content;
                } else if (t.header_data) {
                    this.headerHtml = this.buildHeaderHtml(t.header_data);
                }
                if (t.footer_content) {
                    this.footerHtml = t.footer_content;
                } else if (t.footer_data) {
                    this.footerHtml = this.buildFooterHtml(t.footer_data);
                }

                Swal.fire({
                    icon: 'success',
                    title: 'Template dimuat',
                    text: 'Judul, nomor, ikon, isi, & footer dokumen sudah diisi otomatis.',
                    confirmButtonColor: '#1B2A4A',
                    timer: 1200,
                    showConfirmButton: false,
                });

            } catch (e) {
                console.error(e);
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: 'Gagal memuat template.',
                    confirmButtonColor: '#1B2A4A',
                });
            } finally {
                this.loadingTemplate = null;
            }
        },

        // Susun HTML untuk section HEADER (kop surat) dari data header template.
        buildHeaderHtml(hd) {
            if (!hd) return '<p></p>';

            const parts = [];

            if (hd.kopInstansi) {
                parts.push('<p style=&quot;text-align:center&quot;><strong>' + hd.kopInstansi + '</strong></p>');
            }
            if (hd.kopAlamat) {
                parts.push('<p style=&quot;text-align:center&quot;>' + hd.kopAlamat + '</p>');
            }
            if (hd.kopKontrak) {
                parts.push('<p style=&quot;text-align:center&quot;>' + hd.kopKontrak + '</p>');
            }
            if (hd.perihalSurat) {
                parts.push('<p style=&quot;text-align:center&quot;><em>' + hd.perihalSurat + '</em></p>');
            }

            return parts.length ? parts.join('') : '<p></p>';
        },

        // Susun HTML untuk section FOOTER dari data footer template.
        buildFooterHtml(fd) {
            if (!fd) return '<p></p>';

            const parts = [];

            if (fd.kotaTtd) {
                parts.push('<p style=&quot;text-align:center&quot;>' + fd.kotaTtd + '</p>');
            }
            if (fd.jabatanPenandatangan) {
                parts.push('<p style=&quot;text-align:center&quot;><strong>' + fd.jabatanPenandatangan + '</strong></p>');
            }
            if (fd.namaPenandatangan) {
                parts.push('<p style=&quot;text-align:center&quot;><strong><u>' + fd.namaPenandatangan + '</u></strong></p>');
            }
            if (fd.nipPenandatangan) {
                parts.push('<p style=&quot;text-align:center&quot;>' + fd.nipPenandatangan + '</p>');
            }
            if (fd.tembusan) {
                parts.push('<p><strong>TEMBUSAN:</strong><br>' + fd.tembusan.replace(/\n/g, '<br>') + '</p>');
            }

            return parts.length ? parts.join('') : '<p></p>';
        },
    }" class="mx-auto max-w-4xl py-8">

    {{-- TITLE --}}
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-ink-900 dark:text-parchment-50">
            Buat Dokumen Baru
        </h1>

        <p class="mt-2 text-sm text-slate-warm-600 dark:text-parchment-400">
            Lengkapi detail kontrak, tambahkan data barang/service bila perlu, lalu pilih template dokumen untuk
            mulai menyusun di editor.
        </p>
    </div>

    <form action="{{ route('documents.store') }}" method="POST" @submit="submitForm($event)">

        @csrf

        {{-- Kop surat & footer otomatis diisi dari template terpilih lewat
        headerHtml / footerHtml (kalau tanpa template, tetap kosong). --}}
        <input type="hidden" name="header_data[content]" :value="headerHtml">
        <input type="hidden" name="footer_data[content]" :value="footerHtml">
        <input type="hidden" name="template" x-model="selectedTemplate">
        <input type="hidden" name="body_html" x-model="bodyHtml">

        {{-- FORM 1: DETAIL KONTRAK --}}
        <div
            class="mb-6 rounded-2xl border border-parchment-300 bg-white p-5 shadow-sm dark:border-slate-warm-700 dark:bg-slate-warm-900">

            <div class="mb-4 flex items-start gap-3">
                <span
                    class="inline-flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-ink-900 text-[11px] font-bold text-white dark:bg-bronze-500 dark:text-ink-900">
                    1
                </span>

                <div>
                    <h2 class="font-serif text-base font-bold text-ink-900 dark:text-parchment-50">
                        Detail Kontrak
                    </h2>

                    <p class="text-xs text-slate-warm-500 dark:text-parchment-400">
                        Tanggal Selesai terhitung otomatis dari Tanggal Aktif + Masa Aktif (bulan).
                    </p>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                {{-- Pelanggan --}}
                <div>
                    <label for="contract-customer" class="mb-1.5 block text-xs font-medium">
                        Nama Pelanggan <span class="text-red-500">*</span>
                    </label>

                    @if ($selectedCustomer)
                        <input id="contract-customer" type="text" value="{{ $selectedCustomer->name }}" readonly
                            class="w-full cursor-not-allowed rounded-xl border border-parchment-300 bg-parchment-50 px-4 py-2.5 text-sm text-slate-warm-500 dark:border-slate-warm-700 dark:bg-slate-warm-800/60 dark:text-parchment-400">
                        <input type="hidden" name="customer_id" value="{{ $selectedCustomer->id }}">
                    @else
                        <select id="contract-customer" name="customer_id" required
                            class="w-full rounded-xl border border-parchment-300 bg-white px-4 py-2.5 text-sm outline-none focus:border-bronze-500 dark:border-slate-warm-700 dark:bg-slate-warm-800 dark:text-parchment-100">
                            <option value=""> Pilih pelanggan</option>

                            @foreach ($customers as $customerOption)
                                <option value="{{ $customerOption->id }}">
                                    {{ $customerOption->name }} — {{ $customerOption->contract_number }}
                                </option>
                            @endforeach
                        </select>

                        <p class="mt-1 text-[11px] text-slate-warm-400">
                            Belum ada pelanggan? Tambahkan dulu di halaman
                            <a href="{{ route('documents') }}" class="font-semibold underline">Dokumen Saya</a>.
                        </p>
                    @endif
                </div>

                {{-- Nama Kontrak (judul dokumen) --}}
                <div>
                    <label for="header-judul" class="mb-1.5 block text-xs font-medium">
                        Nama Kontrak <span class="text-red-500">*</span>
                    </label>

                    <input type="text" id="header-judul" name="title" required
                        value="{{ old('title', $selectedCustomer->contract_name ?? '') }}"
                        placeholder="Contoh: Perjanjian Kerjasama Layanan Internet"
                        class="w-full rounded-xl border border-parchment-300 bg-white px-4 py-2.5 text-sm outline-none focus:border-bronze-500 dark:border-slate-warm-700 dark:bg-slate-warm-800">
                </div>

                {{-- Nomor Kontrak --}}
                <div>
                    <label for="header-nomor" class="mb-1.5 block text-xs font-medium">
                        Nomor Kontrak <span class="text-red-500">*</span>
                    </label>

                    <input type="text" id="header-nomor" name="header_data[nomorSurat]" required
                        value="{{ old('header_data.nomorSurat', $selectedCustomer->contract_number ?? '') }}"
                        placeholder="KTR/001/IX/2026"
                        class="w-full rounded-xl border border-parchment-300 bg-white px-4 py-2.5 font-mono text-sm outline-none focus:border-bronze-500 dark:border-slate-warm-700 dark:bg-slate-warm-800">

                    <p class="mt-1 text-[11px] text-slate-warm-400">
                        Terisi otomatis dari data pelanggan!
                    </p>
                </div>

                {{-- Tanggal Aktif + Masa Aktif --}}
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                    <div>
                        <label for="active-date" class="mb-1.5 block text-xs font-medium">
                            Tanggal Aktif <span class="text-red-500">*</span>
                        </label>

                        <input type="date" id="active-date" name="active_date" required x-model="activeDate"
                            class="w-full rounded-xl border border-parchment-300 bg-white px-4 py-2.5 text-sm outline-none focus:border-bronze-500 dark:border-slate-warm-700 dark:bg-slate-warm-800 dark:text-parchment-100">
                    </div>

                    <div>
                        <label for="active-months" class="mb-1.5 block text-xs font-medium">
                            Masa Aktif (bulan) <span class="text-red-500">*</span>
                        </label>

                        <input type="number" id="active-months" name="active_months" required min="1"
                            max="120" step="1" x-model="activeMonths"
                            class="w-full rounded-xl border border-parchment-300 bg-white px-4 py-2.5 text-sm outline-none focus:border-bronze-500 dark:border-slate-warm-700 dark:bg-slate-warm-800 dark:text-parchment-100">
                    </div>
                </div>

                {{-- Tanggal Selesai (otomatis) --}}
                <div class="sm:col-span-2">
                    <label for="finish-date" class="mb-1.5 block text-xs font-medium">
                        Tanggal Selesai <span class="text-[11px] font-normal text-slate-warm-400"></span>
                    </label>

                    <input type="text" id="finish-date" readonly tabindex="-1" :value="finishDateLabel"
                        placeholder="Terisi setelah Tanggal Aktif & Masa Aktif diisi"
                        class="w-full cursor-not-allowed rounded-xl border border-parchment-300 bg-parchment-50 px-4 py-2.5 text-sm font-semibold text-ink-900 placeholder:font-normal placeholder:text-slate-warm-400 dark:border-slate-warm-700 dark:bg-slate-warm-800/60 dark:text-parchment-100">
                </div>
            </div>
        </div>

        {{-- FORM 2: DATA BARANG (opsional) --}}
        <div
            class="mb-6 rounded-2xl border border-parchment-300 bg-white p-5 shadow-sm dark:border-slate-warm-700 dark:bg-slate-warm-900">

            <div class="mb-4 flex items-start gap-3">
                <span
                    class="inline-flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-ink-900 text-[11px] font-bold text-white dark:bg-bronze-500 dark:text-ink-900">
                    2
                </span>

                <div>
                    <h2 class="font-serif text-base font-bold text-ink-900 dark:text-parchment-50">
                        Data Barang <span class="text-xs font-normal text-slate-warm-400">(opsional)</span>
                    </h2>

                    <p class="text-xs text-slate-warm-500 dark:text-parchment-400">
                        Satu pelanggan boleh memiliki beberapa barang. Kosongkan bila kontrak ini tanpa barang.
                    </p>
                </div>
            </div>

            {{-- INPUT TAMBAH BARANG --}}
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-12">
                <div class="sm:col-span-6">
                    <label class="mb-1.5 block text-xs font-medium">Nama Barang</label>

                    <input type="text" x-model="barangDraft.name" @keydown.enter.prevent="addBarang()"
                        placeholder="Contoh: Router Mikrotik RB4011"
                        class="w-full rounded-xl border border-parchment-300 bg-white px-4 py-2.5 text-sm outline-none focus:border-bronze-500 dark:border-slate-warm-700 dark:bg-slate-warm-800 dark:text-parchment-100">
                </div>

                <div class="sm:col-span-2">
                    <label class="mb-1.5 block text-xs font-medium">Jumlah</label>

                    <input type="number" min="1" step="1" x-model="barangDraft.quantity"
                        @keydown.enter.prevent="addBarang()"
                        class="w-full rounded-xl border border-parchment-300 bg-white px-4 py-2.5 text-sm outline-none focus:border-bronze-500 dark:border-slate-warm-700 dark:bg-slate-warm-800 dark:text-parchment-100">
                </div>

                <div class="sm:col-span-2">
                    <label class="mb-1.5 block text-xs font-medium">Harga</label>

                    <input type="number" min="0" step="any" x-model="barangDraft.price"
                        @keydown.enter.prevent="addBarang()" placeholder="0"
                        class="w-full rounded-xl border border-parchment-300 bg-white px-4 py-2.5 text-sm outline-none focus:border-bronze-500 dark:border-slate-warm-700 dark:bg-slate-warm-800 dark:text-parchment-100">
                </div>

                <div class="flex items-end sm:col-span-2">
                    <button type="button" @click="addBarang()" class="btn-secondary w-full text-xs">
                        + Tambah
                    </button>
                </div>
            </div>

            {{-- TABEL BARANG --}}
            <div class="mt-4 overflow-x-auto rounded-xl border border-parchment-200 dark:border-slate-warm-800">
                <table class="w-full min-w-[560px] border-collapse text-left">
                    <thead>
                        <tr class="bg-parchment-50 dark:bg-slate-warm-800/60">
                            @foreach (['ID Barang', 'Nama Barang', 'Jumlah', 'Harga', 'Aksi'] as $heading)
                                <th
                                    class="border-b border-parchment-200 px-4 py-2.5 text-[11px] font-semibold uppercase tracking-wide text-slate-warm-500 dark:border-slate-warm-800 dark:text-parchment-400">
                                    {{ $heading }}
                                </th>
                            @endforeach
                        </tr>
                    </thead>

                    <tbody>
                        <template x-for="(item, index) in itemsBarang" :key="item.uid">
                            <tr class="border-b border-parchment-100 dark:border-slate-warm-800">
                                <td class="whitespace-nowrap px-4 py-3">
                                    <span class="font-mono text-xs text-slate-warm-500"
                                        x-text="itemCode('BRG', index)"></span>

                                    {{-- Nilai yang dikirim ke server --}}
                                    <input type="hidden" :name="'items_barang[' + index + '][name]'"
                                        :value="item.name">
                                    <input type="hidden" :name="'items_barang[' + index + '][quantity]'"
                                        :value="item.quantity">
                                    <input type="hidden" :name="'items_barang[' + index + '][price]'"
                                        :value="item.price">
                                </td>

                                <td class="px-4 py-3 text-sm text-ink-800 dark:text-parchment-200"
                                    x-text="item.name"></td>

                                <td class="px-4 py-3 text-sm text-ink-800 dark:text-parchment-200"
                                    x-text="item.quantity"></td>

                                <td class="whitespace-nowrap px-4 py-3 text-sm text-ink-800 dark:text-parchment-200"
                                    x-text="formatRupiah(item.price)"></td>

                                <td class="px-4 py-3">
                                    <button type="button" @click="removeBarang(index)"
                                        class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-transparent text-slate-warm-400 transition hover:border-red-200 hover:bg-red-50 hover:text-red-600 dark:hover:border-red-900/40 dark:hover:bg-red-900/20"
                                        title="Hapus barang">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none"
                                            stroke="currentColor" stroke-width="2">
                                            <polyline points="3 6 5 6 21 6" />
                                            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6" />
                                        </svg>
                                    </button>
                                </td>
                            </tr>
                        </template>

                        <tr x-show="itemsBarang.length === 0">
                            <td colspan="5" class="px-4 py-6 text-center text-xs text-slate-warm-400">
                                Belum ada barang ditambahkan.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        {{-- FORM 3: DATA SERVICE (opsional) --}}
        <div
            class="mb-6 rounded-2xl border border-parchment-300 bg-white p-5 shadow-sm dark:border-slate-warm-700 dark:bg-slate-warm-900">

            <div class="mb-4 flex items-start gap-3">
                <span
                    class="inline-flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-ink-900 text-[11px] font-bold text-white dark:bg-bronze-500 dark:text-ink-900">
                    3
                </span>

                <div>
                    <h2 class="font-serif text-base font-bold text-ink-900 dark:text-parchment-50">
                        Data Service <span class="text-xs font-normal text-slate-warm-400">(opsional)</span>
                    </h2>

                    <p class="text-xs text-slate-warm-500 dark:text-parchment-400">
                        Satu pelanggan boleh memiliki beberapa service sekaligus.
                    </p>
                </div>
            </div>

            {{-- INPUT TAMBAH SERVICE --}}
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-12">
                <div class="sm:col-span-8">
                    <label class="mb-1.5 block text-xs font-medium">Nama Service</label>

                    <input type="text" x-model="serviceDraft.name" @keydown.enter.prevent="addService()"
                        placeholder="Contoh: Internet Dedicated 100 Mbps"
                        class="w-full rounded-xl border border-parchment-300 bg-white px-4 py-2.5 text-sm outline-none focus:border-bronze-500 dark:border-slate-warm-700 dark:bg-slate-warm-800 dark:text-parchment-100">
                </div>

                <div class="sm:col-span-2">
                    <label class="mb-1.5 block text-xs font-medium">Harga</label>

                    <input type="number" min="0" step="any" x-model="serviceDraft.price"
                        @keydown.enter.prevent="addService()" placeholder="0"
                        class="w-full rounded-xl border border-parchment-300 bg-white px-4 py-2.5 text-sm outline-none focus:border-bronze-500 dark:border-slate-warm-700 dark:bg-slate-warm-800 dark:text-parchment-100">
                </div>

                <div class="flex items-end sm:col-span-2">
                    <button type="button" @click="addService()" class="btn-secondary w-full text-xs">
                        + Tambah
                    </button>
                </div>
            </div>

            {{-- TABEL SERVICE --}}
            <div class="mt-4 overflow-x-auto rounded-xl border border-parchment-200 dark:border-slate-warm-800">
                <table class="w-full min-w-[560px] border-collapse text-left">
                    <thead>
                        <tr class="bg-parchment-50 dark:bg-slate-warm-800/60">
                            @foreach (['ID Service', 'Nama Service', 'Harga', 'Aksi'] as $heading)
                                <th
                                    class="border-b border-parchment-200 px-4 py-2.5 text-[11px] font-semibold uppercase tracking-wide text-slate-warm-500 dark:border-slate-warm-800 dark:text-parchment-400">
                                    {{ $heading }}
                                </th>
                            @endforeach
                        </tr>
                    </thead>

                    <tbody>
                        <template x-for="(item, index) in itemsService" :key="item.uid">
                            <tr class="border-b border-parchment-100 dark:border-slate-warm-800">
                                <td class="whitespace-nowrap px-4 py-3">
                                    <span class="font-mono text-xs text-slate-warm-500"
                                        x-text="itemCode('SVC', index)"></span>

                                    <input type="hidden" :name="'items_service[' + index + '][name]'"
                                        :value="item.name">
                                    <input type="hidden" :name="'items_service[' + index + '][price]'"
                                        :value="item.price">
                                </td>

                                <td class="px-4 py-3 text-sm text-ink-800 dark:text-parchment-200"
                                    x-text="item.name"></td>

                                <td class="whitespace-nowrap px-4 py-3 text-sm text-ink-800 dark:text-parchment-200"
                                    x-text="formatRupiah(item.price)"></td>

                                <td class="px-4 py-3">
                                    <button type="button" @click="removeService(index)"
                                        class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-transparent text-slate-warm-400 transition hover:border-red-200 hover:bg-red-50 hover:text-red-600 dark:hover:border-red-900/40 dark:hover:bg-red-900/20"
                                        title="Hapus service">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none"
                                            stroke="currentColor" stroke-width="2">
                                            <polyline points="3 6 5 6 21 6" />
                                            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6" />
                                        </svg>
                                    </button>
                                </td>
                            </tr>
                        </template>

                        <tr x-show="itemsService.length === 0">
                            <td colspan="4" class="px-4 py-6 text-center text-xs text-slate-warm-400">
                                Belum ada service ditambahkan.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        {{-- FORM 4: PILIH TEMPLATE DOKUMEN --}}
        <div class="mb-6">
            <div class="mb-4 flex items-start gap-3">
                <span
                    class="inline-flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-ink-900 text-[11px] font-bold text-white dark:bg-bronze-500 dark:text-ink-900">
                    4
                </span>

                <div>
                    <h2 class="font-serif text-base font-bold text-ink-900 dark:text-parchment-50">
                        Pilih Template Dokumen
                    </h2>

                    <p class="text-xs text-slate-warm-500 dark:text-parchment-400">
                        Klik template untuk mengisi judul & nomor otomatis, lalu tekan tombol simpan di bawah.
                    </p>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($templates as $template)
                    <div class="template-card flex flex-col justify-between rounded-2xl border border-parchment-300 bg-white p-5 shadow-sm dark:border-slate-warm-700 dark:bg-slate-warm-900"
                        :class="selectedTemplate === '{{ $template['key'] }}'
                            ? 'border-bronze-500 ring-2 ring-bronze-500/30 dark:border-bronze-500' : ''">
                        <div>
                            <div class="mb-3 flex items-center justify-between">
                                <span
                                    class="rounded bg-bronze-100 px-2.5 py-0.5 font-mono text-[10px] font-semibold text-bronze-800 dark:bg-bronze-900 dark:text-bronze-300">
                                    {{ $template['name'] }}
                                </span>

                                <span class="font-mono text-[10px] text-slate-warm-400">{{ $template['code'] }}</span>
                            </div>

                            <div
                                class="template-card-preview mb-4 flex min-h-[110px] flex-col items-center justify-center rounded bg-parchment-50 p-4 shadow-xs dark:bg-slate-warm-800">
                                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="1.5" class="mb-2 text-parchment-300">
                                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
                                    <path d="M14 2v6h6" />
                                </svg>

                                <span class="text-[10px] text-slate-warm-400">{{ $template['name'] }}</span>
                            </div>

                            <h3 class="mb-2 font-serif text-base font-bold text-ink-900 dark:text-parchment-100">
                                {{ $template['name'] }}
                            </h3>

                            <p class="mb-4 text-xs leading-relaxed text-slate-warm-500 dark:text-parchment-400">
                                {{ $template['desc'] }}
                            </p>
                        </div>

                        <button type="button" @click="loadTemplate('{{ $template['key'] }}')"
                            :disabled="loadingTemplate" class="btn-primary w-full py-2.5 text-center text-xs">
                            <span
                                x-text="loadingTemplate === '{{ $template['key'] }}'
                                    ? 'Memuat...'
                                    : (selectedTemplate === '{{ $template['key'] }}' ? '✓ Template Terpilih' : 'Gunakan Template Ini →')"></span>
                        </button>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- SIMPAN --}}
        <div
            class="flex flex-col-reverse items-stretch justify-between gap-3 rounded-2xl border border-parchment-300 bg-white p-5 shadow-sm dark:border-slate-warm-700 dark:bg-slate-warm-900 sm:flex-row sm:items-center">
            <div>
                <p class="text-xs font-semibold text-ink-900 dark:text-parchment-100"
                    x-show="selectedTemplate" x-cloak>
                    Template terpilih: <span class="font-mono" x-text="selectedTemplate"></span>
                </p>

                <p class="text-xs text-slate-warm-500 dark:text-parchment-400" x-show="!selectedTemplate">
                    Belum ada template dipilih. Pilih salah satu template di atas untuk melanjutkan.
                </p>

                <p class="mt-1 text-[11px] text-slate-warm-400">
                    Setelah disimpan, Anda akan diarahkan ke Studio Editor dan status pelanggan berubah menjadi
                    <strong>On Progress</strong>.
                </p>
            </div>

            <button type="submit" class="btn-primary shrink-0 px-8 py-3 text-sm">
                Simpan & Lanjut ke Editor →
            </button>
        </div>
    </form>

</div>

@endsection