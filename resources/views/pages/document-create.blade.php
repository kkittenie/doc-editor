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

    $contractTotals = $contractTotals ?? ['one_time' => 0, 'monthly' => 0];

    // Periode kontrak read-only (diisi dari Form Pelanggan).
    $periodLabel = '—';

    if ($selectedCustomer && $selectedCustomer->active_date && $selectedCustomer->finish_date) {
        $periodLabel = $selectedCustomer->active_date->format('d M Y')
            . ' — '
            . $selectedCustomer->finish_date->format('d M Y')
            . ($selectedCustomer->active_months ? ' (' . $selectedCustomer->active_months . ' bulan)' : '');
    }
@endphp

<div x-data="{
        loadingTemplate: null,
        selectedTemplate: '',
        bodyHtml: '',
        headerHtml: '<p></p>',
        footerHtml: '<p></p>',

        // Konfirmasi lalu kirim form (kontrak dibaca dari Form Pelanggan,
        // template bersifat opsional — tanpa template editor dibuka kosong).
        async submitForm(event) {
            const form = event.target;

            if (!form.reportValidity()) return;

            event.preventDefault();

            const result = await Swal.fire({
                icon: 'question',
                title: 'Simpan & lanjut ke editor?',
                text: this.selectedTemplate
                    ? 'Data kontrak diambil dari Form Pelanggan.'
                    : 'Tanpa template, editor dibuka kosong dan status pelanggan menjadi On Progress.',
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

                // Nama & Nomor Kontrak milik user dipertahankan selama kolomnya
                // tidak kosong — template hanya mengisi fallback saat kosong,
                // supaya ketikan user tidak tertimpa placeholder template
                // seperti '[Nomor Perjanjian]'.
                if (t.title) {
                    const titleInput = document.getElementById('header-judul');
                    if (titleInput && titleInput.value.trim() === '') titleInput.value = t.title;
                }

                if (t.header_data?.nomorSurat) {
                    const nomorInput = document.getElementById('header-nomor');
                    if (nomorInput && nomorInput.value.trim() === '') nomorInput.value = t.header_data.nomorSurat;
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
                    text: 'Isi, ikon, & footer dimuat. Nama & nomor kontrak Anda dipertahankan.',
                    confirmButtonColor: '#1B2A4A',
                    timer: 1500,
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
            Data kontrak &amp; barang/service sudah diisi di Form Pelanggan (read-only di sini).
            Pilih template dokumen untuk mulai menyusun di editor.
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
                        Periode kontrak dari Form Pelanggan (read-only).
                    </p>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                {{-- Ringkasan pelanggan (read-only dari Form Pelanggan) --}}
                <div class="sm:col-span-2">
                    <label class="mb-1.5 block text-xs font-medium">
                        Nama Pelanggan
                    </label>

                    <input type="text" value="{{ $selectedCustomer->name }}" readonly
                        class="w-full cursor-not-allowed rounded-xl border border-parchment-300 bg-parchment-50 px-4 py-2.5 text-sm text-slate-warm-500 dark:border-slate-warm-700 dark:bg-slate-warm-800/60 dark:text-parchment-400">
                    <input type="hidden" name="customer_id" value="{{ $selectedCustomer->id }}">

                    <p class="mt-1 text-[11px] text-slate-warm-400">
                        Periode kontrak: {{ $periodLabel }}. Ubah lewat Form Pelanggan.
                    </p>
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

                {{-- Tanggal Aktif (read-only dari Form Pelanggan) --}}
                <div>
                    <label class="mb-1.5 block text-xs font-medium">
                        Tanggal Aktif
                    </label>

                    <input type="text" readonly tabindex="-1"
                        value="{{ $selectedCustomer->active_date?->format('d M Y') ?? '—' }}"
                        class="w-full cursor-not-allowed rounded-xl border border-parchment-300 bg-parchment-50 px-4 py-2.5 text-sm text-slate-warm-500 dark:border-slate-warm-700 dark:bg-slate-warm-800/60 dark:text-parchment-400">
                </div>

                {{-- Masa Aktif (read-only dari Form Pelanggan) --}}
                <div>
                    <label class="mb-1.5 block text-xs font-medium">
                        Masa Aktif (bulan)
                    </label>

                    <input type="text" readonly tabindex="-1"
                        value="{{ $selectedCustomer->active_months ? $selectedCustomer->active_months . ' bulan' : '—' }}"
                        class="w-full cursor-not-allowed rounded-xl border border-parchment-300 bg-parchment-50 px-4 py-2.5 text-sm text-slate-warm-500 dark:border-slate-warm-700 dark:bg-slate-warm-800/60 dark:text-parchment-400">
                </div>

                {{-- Tanggal Selesai --}}
                <div class="sm:col-span-2">
                    <label class="mb-1.5 block text-xs font-medium">
                        Tanggal Selesai
                    </label>

                    <input type="text" readonly tabindex="-1"
                        value="{{ $selectedCustomer->finish_date?->format('d M Y') ?? '—' }}"
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
                        Data Barang <span class="text-xs font-normal text-slate-warm-400"></span>
                    </h2>
                </div>
            </div>

            {{-- INPUT TAMBAH BARANG (dipindah ke Form Pelanggan) --}}
            <p class="text-[11px] text-slate-warm-400">
                Input barang dipindah ke Form Pelanggan — tabel di bawah hanya tampil (read-only).
            </p>

            {{-- TABEL BARANG (read-only dari Form Pelanggan) --}}
            @include('partials.contract.barang-table', ['selectedCustomer' => $selectedCustomer])
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
                        Data Service <span class="text-xs font-normal text-slate-warm-400"></span>
                    </h2>

                   
                </div>
            </div>

            {{-- INPUT TAMBAH SERVICE (dipindah ke Form Pelanggan) --}}
            <p class="text-[11px] text-slate-warm-400">
                Input service dipindah ke Form Pelanggan — tabel di bawah hanya tampil (read-only).
            </p>

            {{-- TABEL SERVICE + TOTAL (read-only dari Form Pelanggan) --}}
            @include('partials.contract.service-table', ['selectedCustomer' => $selectedCustomer, 'contractTotals' => $contractTotals])
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
                        Pilih Template Dokumen (Opsional)
                    </h2>

                    <p class="text-xs text-slate-warm-500 dark:text-parchment-400">
                        Template bersifat opsional. Tanpa template, editor dibuka kosong - Anda bisa mengetik manual.
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
                    Tanpa template: editor dibuka kosong. Anda bisa memilih template di atas atau langsung lanjut.
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