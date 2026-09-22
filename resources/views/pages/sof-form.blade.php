@extends('layouts.app')

@section('content')
@php
        $sof = $sof ?? null;
    $isEdit = $sof !== null;
    $action = $action ?? route('sof.store');
    $method = $method ?? 'POST';
    $orderNumberPlaceholder = $orderNumberPlaceholder ?? 'SOF/001/IX/2026';

        $customerNumber  = $sof->customer_number ?? old('customer_number');
    $customerName    = $sof->customer_name   ?? old('customer_name');
    $contractNumber  = $sof->contract_number ?? old('contract_number');
    $contractName    = $sof->contract_name   ?? old('contract_name');
    $activeDate      = $sof?->active_date?->format('Y-m-d') ?? old('active_date');
    $activeMonths    = $sof->active_months  ?? old('active_months');
    $totalValue      = $sof->total_value    ?? old('total_value');
    $finishDate = ($activeDate && $activeMonths)
        ? \Carbon\Carbon::createFromFormat('Y-m-d', $activeDate)->addMonths($activeMonths)->format('Y-m-d')
        : '';
@endphp

<div class="mx-auto max-w-3xl py-10 px-4">
    <div class="mb-8 flex flex-col items-center justify-between gap-4 sm:flex-row">
        <div>
            <h1 class="font-serif text-2xl font-bold text-ink-900 dark:text-parchment-50">{{ $title }}</h1>
            @if($isEdit)
                <p class="mt-1 text-xs text-slate-warm-500 dark:text-parchment-400">
                    Order: <span class="font-mono text-ink-800 dark:text-parchment-200">{{ $sof->order_number }}</span>
                </p>
            @endif
        </div>
        <a href="{{ route('sof.index') }}" class="btn-ghost shrink-0 text-xs">← Menu S.O.F</a>
    </div>

    @if (session('error'))
        <div class="mb-4 rounded-xl border border-red-200 bg-red-50 p-3 text-sm text-red-700">{{ session('error') }}</div>
    @endif

        <form id="sof-form" action="{{ $action }}" method="POST" autocomplete="off">
        @csrf
        @if($method !== 'POST') @method($method) @endif

        {{-- BAGIAN 1: DATA PELANGGAN --}}
        <div class="rounded-2xl border border-parchment-300 bg-white p-6 shadow-sm">
            <h2 class="mb-4 text-xs font-semibold uppercase text-slate-warm-500">Data Pelanggan</h2>
            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <label class="mb-1 block text-xs text-ink-900">Order Number</label>
                    <input type="text" name="order_number" value="{{ $sof->order_number ?? $orderNumberPlaceholder }}" readonly
                        class="w-full cursor-default rounded-xl border bg-parchment-50 px-4 py-2.5 text-sm text-slate-warm-500">
                    <p class="mt-1 text-[10px] text-slate-warm-400">Auto-generate • SOF/001/IX/2026</p>
                </div>
                <div>
                    <label class="mb-1 block text-xs text-ink-900">Nomer Pelanggan</label>
                    <input type="text" name="customer_number" value="{{ $customerNumber }}" required
                        class="w-full rounded-xl border bg-white px-4 py-2.5 text-sm text-ink-900">
                </div>
                <div>
                    <label class="mb-1 block text-xs text-ink-900">Nama Pelanggan</label>
                    <input type="text" name="customer_name" value="{{ $customerName }}" required
                        class="w-full rounded-xl border bg-white px-4 py-2.5 text-sm text-ink-900">
                </div>
                <div class="sm:col-span-2">
                    <label class="mb-1 block text-xs text-ink-900">Nomor Kontrak</label>
                    <input type="text" id="contract_number" name="contract_number" value="{{ $contractNumber }}"
                        placeholder="KTR/001/IX/2026"
                        class="w-full rounded-xl border bg-white px-4 py-2.5 text-sm text-ink-900">
                </div>
                <div class="sm:col-span-2">
                    <label class="mb-1 block text-xs text-ink-900">Nama Kontrak</label>
                    <input type="text" id="contract_name" name="contract_name" value="{{ $contractName }}"
                        placeholder="Perjanjian Kerja Sama..."
                        class="w-full rounded-xl border bg-white px-4 py-2.5 text-sm text-ink-900">
                </div>
                <div>
                    <label class="mb-1 block text-xs text-ink-900">Tanggal Aktif</label>
                    <input type="date" id="active_date" name="active_date" value="{{ $activeDate }}"
                        class="w-full rounded-xl border bg-white px-4 py-2.5 text-sm text-ink-900">
                </div>
                <div>
                    <label class="mb-1 block text-xs text-ink-900">Masa Aktif (bln)</label>
                    <input type="number" id="active_months" name="active_months" value="{{ $activeMonths }}" min="1" max="120"
                        class="w-full rounded-xl border bg-white px-4 py-2.5 text-sm text-ink-900">
                </div>
            </div>
        </div>

        {{-- BAGIAN 2: NILAI & UPLOAD FILE --}}
        <div class="mt-6 rounded-2xl border border-parchment-300 bg-white p-6 shadow-sm">
            <h2 class="mb-4 text-xs font-semibold uppercase text-slate-warm-500">Nilai &amp; Berkas</h2>
            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                <div>
                    <label for="total_value" class="mb-1 block text-xs text-ink-900">Nilai Total</label>
                    <input type="text" id="total_value" name="total_value" value="{{ $totalValue ?: '0' }}"
                        class="w-full rounded-xl border bg-white px-4 py-2.5 text-sm text-ink-900">
                </div>
                <div>
                    <label class="mb-1 block text-xs text-ink-900">Tanggal Selesai</label>
                    <input type="text" value="{{ $finishDate }}" readonly
                        class="w-full cursor-default rounded-xl border bg-parchment-50 px-4 py-2.5 text-sm text-slate-warm-500">
                    <p class="mt-1 text-[10px] text-slate-warm-400">Otomatis dari Tanggal Aktif + Masa Aktif</p>
                </div>
                <div class="sm:col-span-2">
                    <label class="mb-1 block text-xs text-ink-900">Berkas PDF (Scan Kontrak)</label>
                    <div class="flex items-center gap-3">
                        <button type="button" id="upload-file-btn"
                            class="inline-flex h-9 items-center gap-2 rounded-lg border bg-white px-4 text-xs font-semibold text-ink-900 hover:bg-ink-900 hover:text-white">
                            📎 {{ ($sof && $sof->hasFile()) ? 'Ganti Berkas' : 'Pilih Berkas' }}
                        </button>
                        <span id="file-name-label"
                            class="text-xs truncate {{ ($sof && $sof->hasFile()) ? 'text-slate-warm-500' : 'text-slate-warm-400' }}">
                            {{ ($sof && $sof->hasFile()) ? ($sof->file_name ?: 'berkas.pdf') : 'Belum ada berkas' }}
                        </span>
                    </div>
                    {{-- Input file native tersembunyi — dipicu tombol 📎 (pola andal, konsisten editor.js) --}}
                    <input type="file" id="sof-file-input" accept="application/pdf,.pdf" class="hidden">
                    <input type="hidden" name="file_path" id="file_path_input" value="{{ $sof->file_path ?? '' }}">
                </div>
            </div>
        </div>

        {{-- CATATAN REVISI --}}
        <div class="mt-6 rounded-2xl border border-parchment-300 bg-white p-6 shadow-sm">
            <h2 class="mb-4 text-xs font-semibold uppercase text-slate-warm-500">Catatan Revisi</h2>
            <p class="mb-2 text-[11px] text-slate-warm-500">Opsional. Catat alasan/catatan untuk versi berikutnya.</p>
            <textarea name="revision_notes[reason]" rows="3"
                class="w-full rounded-xl border bg-white px-4 py-2.5 text-sm text-ink-900 dark:border-slate-warm-700"
                placeholder="Misal: perlu update harga barang Okt 2026...">{{ $sof->revision_notes['reason'] ?? '' }}</textarea>
        </div>

        {{-- SUBMIT --}}
        <div class="mt-8 flex items-center justify-between">
            <a href="{{ route('sof.index') }}" class="text-xs text-slate-warm-500 hover:text-ink-900">← Batal</a>
            <button type="submit" class="btn-primary px-8 py-3 text-sm">
                {{ $isEdit ? 'Simpan Perubahan' : 'Simpan S.O.F' }}
            </button>
        </div>
    </form>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var uploadBtn   = document.getElementById('upload-file-btn');
    var fileInput   = document.getElementById('sof-file-input');
    var filePathIn  = document.getElementById('file_path_input');
    var fileNameLbl = document.getElementById('file-name-label');
    var sofForm     = document.getElementById('sof-form');

    if (!uploadBtn || !fileInput) return;

    // Bersihkan pilihan file (hidden file_path_input tetap menyimpan
    // path dari upload sukses sebelumnya).
    var resetFileSelection = function () { fileInput.value = ''; };

    // Tombol 📎 → buka dialog pemilih file native browser.
    uploadBtn.addEventListener('click', function () {
        fileInput.click();
    });

    // File terpilih → validasi klien → konfirmasi → upload via axios.
    fileInput.addEventListener('change', function () {
        var file = fileInput.files && fileInput.files[0];
        if (!file) return;

        // Validasi klien: PDF (cek MIME + ekstensi).
        var isPdf = file.type === 'application/pdf' || /\.pdf$/i.test(file.name || '');
        if (!isPdf) {
            resetFileSelection();
            Swal.fire({ icon: 'error', title: 'Format tidak didukung', text: 'Berkas harus berformat PDF.' });
            return;
        }
        if (file.size > 10 * 1024 * 1024) {
            resetFileSelection();
            Swal.fire({ icon: 'error', title: 'Berkas terlalu besar', text: 'Ukuran berkas maksimal 10 MB.' });
            return;
        }

        var sizeMb = (file.size / (1024 * 1024)).toFixed(2);

        Swal.fire({
            icon: 'question',
            title: 'Upload berkas ini?',
            html: '<p class="text-sm font-medium text-ink-900">' + file.name + '</p>' +
                  '<p class="mt-1 text-xs text-slate-warm-500">' + sizeMb + ' MB — Format PDF</p>',
            showCancelButton: true,
            confirmButtonText: 'Ya, Upload',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#1B2A4A',
        }).then(function (result) {
            if (!result.isConfirmed) { resetFileSelection(); return; }

            Swal.fire({
                title: 'Mengunggah...',
                text: 'Mohon tunggu sebentar.',
                allowOutsideClick: false,
                allowEscapeKey: false,
                didOpen: function () { Swal.showLoading(); },
            });

            var formData = new FormData();
            formData.append('file', file, file.name);

            window.axios.post('{{ route('sof.upload') }}', formData, {
                headers: { 'Content-Type': 'multipart/form-data' },
            })
            .then(function (res) {
                filePathIn.value = res.data.path || '';
                if (fileNameLbl) {
                    fileNameLbl.textContent = file.name;
                    fileNameLbl.classList.remove('text-slate-warm-400');
                    fileNameLbl.classList.add('text-slate-warm-500');
                }
                Swal.fire({ icon: 'success', title: 'Berhasil', text: 'Berkas siap disimpan bersama data S.O.F.', timer: 1800, showConfirmButton: false });
            })
            .catch(function (err) {
                resetFileSelection();
                var msg = 'Upload gagal. Coba lagi.';
                var res = err && err.response;
                if (res && res.status === 422 && res.data && res.data.errors) {
                    var first = Object.values(res.data.errors)[0];
                    if (first && first.length) msg = first[0];
                } else if (res && res.data && res.data.message) {
                    msg = res.data.message;
                } else if (res && res.status === 403) {
                    msg = 'Anda tidak memiliki izin untuk mengunggah berkas.';
                } else if (res && res.status === 419) {
                    msg = 'Sesi kedaluwarsa — muat ulang halaman lalu coba lagi.';
                }
                console.error('Upload gagal:', err);
                Swal.fire({ icon: 'error', title: 'Gagal', text: msg });
            });
        });
    });

    // Guard e.submitter (bisa null bila form disubmit via tombol Enter).
    if (sofForm) {
        sofForm.addEventListener('submit', function (e) {
            var btn = e.submitter;
            if (!btn) return;
            btn.disabled = true;
            btn.innerHTML = 'Menyimpan...';
        });
    }
});
</script>
@endpush
@endsection
