@extends('layouts.app')

@section('content')

@php
    $isAdmin = auth()->user()->hasRole('admin');

    $documentData = $documents->map(function ($doc) {
        $status = strtolower($doc->status ?? 'draft');

        return [
            'id' => 'DOC-' . str_pad($doc->id, 5, '0', STR_PAD_LEFT),
            'databaseId' => $doc->id,
            'title' => $doc->title,
            'nomorSurat' => $doc->header_data['nomorSurat'] ?? '—',

            'category' => ucfirst($doc->type ?? 'surat'),

            'date' => $doc->created_at
                ? $doc->created_at->format('d M Y')
                : '-',

            'status' => $status,

            'statusLabel' => match ($status) {
                'draft' => 'Draft',
                'review_marketing' => 'Review Marketing',
                'revisi' => 'Revisi',
                'disetujui' => 'Disetujui',
                default => ucfirst($status),
            },

            'signer' => $doc->footer_data['namaPenandatangan'] ?? 'Belum Ditentukan',

            'signerRole' => $doc->footer_data['jabatanPenandatangan'] ?? 'Belum Ditentukan',

            'pages' => count($doc->body_content['pages'] ?? []) ?: 1,

            'hasSignature' => !empty($doc->signature_data['signatureId']),

            'hasMaterai' => false,

            'revisionNotes' => $doc->revision_notes ?? [],
        ];
    })->toArray();
@endphp

<script>
    window.documentPageData = @json($documentData);
    window.canManageDocuments = @json($isAdmin);

    function documentsPage() {
        return {
            filterStatus: 'all',
            searchQuery: '',
            documents: window.documentPageData,
            canManage: window.canManageDocuments,

            get filteredDocuments() {
                return this.documents.filter(doc => {

                    const matchesStatus =
                        this.filterStatus === 'all' ||
                        doc.status === this.filterStatus;

                    const search = this.searchQuery.toLowerCase();

                    const matchesSearch =
                        doc.title.toLowerCase().includes(search) ||
                        doc.id.toLowerCase().includes(search);

                    return matchesStatus && matchesSearch;
                });
            },

            async updateDocumentStatus(docId, status, action, reason = null) {
                const konfirmasi = {
                    kirim:   { icon: 'info',    title: 'Kirim untuk review?', text: 'Dokumen akan dikirim ke marketing untuk direview.', confirm: 'Ya, kirim', color: '#2563eb' },
                    setujui: { icon: 'success', title: 'Setujui dokumen?',    text: 'Dokumen akan berstatus Disetujui.', confirm: 'Ya, setujui', color: '#059669' },
                }[action];

                const result = await Swal.fire({
                    icon: konfirmasi.icon,
                    title: konfirmasi.title,
                    text: konfirmasi.text,
                    showCancelButton: true,
                    confirmButtonText: konfirmasi.confirm,
                    cancelButtonText: 'Batal',
                    confirmButtonColor: konfirmasi.color,
                });

                if (!result.isConfirmed) return;

                await this.submitStatusUpdate(docId, status, reason);
            },

            // Popup "Alasan Revisi": textarea wajib diisi sebelum dikirim.
            async requestRevision(docId) {
                const result = await Swal.fire({
                    icon: 'warning',
                    title: 'Minta revisi dokumen?',
                    input: 'textarea',
                    inputLabel: 'Alasan Revisi',
                    inputPlaceholder: 'Tuliskan alasan / catatan revisi untuk admin...',
                    inputAttributes: { 'aria-label': 'Alasan Revisi' },
                    showCancelButton: true,
                    confirmButtonText: 'Kirim ke Admin',
                    cancelButtonText: 'Batal',
                    confirmButtonColor: '#d97706',
                    inputValidator: (value) => {
                        if (!value || value.trim() === '') {
                            return 'Alasan revisi wajib diisi.';
                        }
                    },
                });

                if (!result.isConfirmed) return;

                await this.submitStatusUpdate(docId, 'revisi', result.value.trim());
            },

            async submitStatusUpdate(docId, status, reason = null) {
                try {
                    await window.axios.patch(`/documents/${docId}/status`, {
                        status,
                        ...(reason !== null ? { reason } : {}),
                    });
                    window.location.reload();
                } catch (error) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: 'Tidak dapat memperbarui status dokumen.',
                        confirmButtonColor: '#dc2626',
                    });
                }
            },

            // Admin: popup daftar alasan revisi dari marketing.
            async showRevisionNotes(doc) {
                const notes = doc.revisionNotes ?? [];

                if (notes.length === 0) {
                    Swal.fire({
                        icon: 'info',
                        title: 'Alasan Revisi',
                        text: 'Tidak ada catatan revisi untuk dokumen ini.',
                    });
                    return;
                }

                const items = [...notes].reverse().map((note) => `
                    <div style="text-align:left; border:1px solid #e5e7eb; border-radius:10px; padding:10px 14px; margin-bottom:10px;">
                        <div style="font-size:11px; color:#9ca3af; margin-bottom:4px;">
                            ${note.by ?? '-'} • ${note.at ?? '-'}
                        </div>
                        <div style="font-size:14px; color:#374151; white-space:pre-wrap;">${String(note.reason ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;')}</div>
                    </div>
                `).join('');

                Swal.fire({
                    title: 'Alasan Revisi',
                    html: items,
                    width: 560,
                    confirmButtonText: 'Tutup',
                    confirmButtonColor: '#111827',
                });
            },

            async deleteDocument(docId) {
                const result = await Swal.fire({
                    icon: 'warning',
                    title: 'Hapus dokumen ini?',
                    text: 'Dokumen akan dipindahkan ke Trash.',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, hapus',
                    cancelButtonText: 'Batal',
                    confirmButtonColor: '#dc2626',
                });

                if (!result.isConfirmed) return;

                fetch('/documents/' + docId, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    }
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Gagal menghapus dokumen.');
                    }

                    return response.json();
                })
                .then(() => {
                    Swal.fire({
                        icon: 'success',
                        title: 'Terhapus',
                        text: 'Dokumen berhasil dihapus.',
                        confirmButtonColor: '#1B2A4A'
                    }).then(() => window.location.reload());
                })
                .catch(error => {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: error.message,
                        confirmButtonColor: '#1B2A4A'
                    });

                    console.error(error);
                });
            },

            async deleteAllDocuments() {
                const result = await Swal.fire({
                    icon: 'warning',
                    title: 'Hapus semua dokumen?',
                    html: `Semua <strong>${this.documents.length}</strong> dokumen akan dipindahkan ke Trash.`,
                    showCancelButton: true,
                    confirmButtonText: 'Ya, hapus semua',
                    cancelButtonText: 'Batal',
                    confirmButtonColor: '#dc2626',
                });

                if (!result.isConfirmed) return;

                fetch('/documents', {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    }
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Gagal menghapus semua dokumen.');
                    }

                    return response.json();
                })
                .then(() => {
                    Swal.fire({
                        icon: 'success',
                        title: 'Terhapus',
                        text: 'Semua dokumen berhasil dihapus.',
                        confirmButtonColor: '#1B2A4A'
                    }).then(() => window.location.reload());
                })
                .catch(error => {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: error.message,
                        confirmButtonColor: '#1B2A4A'
                    });

                    console.error(error);
                });
            }
        };
    }
</script>

<div x-data="documentsPage()" class="space-y-6">

    
    {{-- HEADER --}}
    
    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">

        <div>
            <div class="mb-2 flex items-center gap-2">
                <span class="inline-flex h-7 w-7 items-center justify-center rounded-lg bg-ink-900 text-white dark:bg-bronze-500 dark:text-ink-900">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                        <polyline points="14 2 14 8 20 8"/>
                        <line x1="8" y1="13" x2="16" y2="13"/>
                        <line x1="8" y1="17" x2="16" y2="17"/>
                    </svg>
                </span>

                <span class="text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-warm-500">
                    Document Management
                </span>
            </div>

            <h1 class="font-serif text-2xl font-bold tracking-tight text-ink-900 dark:text-parchment-50">
                Arsip & Dokumen Saya
            </h1>

            <p class="mt-1.5 max-w-2xl text-sm text-slate-warm-500 dark:text-parchment-400">
                Kelola dokumen resmi, proses tanda tangan digital, dan arsip dokumen dalam satu tempat.
            </p>
        </div>

        <a
            href="{{ route('documents.create') }}"
            @if(!$isAdmin) hidden @endif
            class="inline-flex h-10 items-center justify-center gap-2 rounded-xl bg-ink-900 px-4 text-xs font-semibold text-white shadow-sm transition hover:-translate-y-0.5 hover:shadow-md dark:bg-bronze-500 dark:text-ink-900"
        >
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2">
                <line x1="12" y1="5" x2="12" y2="19"/>
                <line x1="5" y1="12" x2="19" y2="12"/>
            </svg>

            Buat Dokumen Baru
        </a>

    </div>


    
    {{-- SUMMARY CARDS --}}
    
    <div class="grid grid-cols-2 gap-3 lg:grid-cols-5">

        {{-- Total --}}
        <div class="rounded-2xl border border-parchment-300 bg-white p-4 shadow-theme-xs dark:border-slate-warm-800 dark:bg-slate-warm-900">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-[11px] font-medium uppercase tracking-wide text-slate-warm-500">
                        Total Dokumen
                    </p>

                    <p class="mt-2 text-2xl font-bold text-ink-900 dark:text-parchment-50"
                       x-text="documents.length">
                    </p>
                </div>

                <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-parchment-100 text-ink-900 dark:bg-slate-warm-800 dark:text-parchment-200">
                    <svg width="17" height="17" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                        <polyline points="14 2 14 8 20 8"/>
                    </svg>
                </div>
            </div>
        </div>


        {{-- Draft --}}
        <div class="rounded-2xl border border-parchment-300 bg-white p-4 shadow-theme-xs dark:border-slate-warm-800 dark:bg-slate-warm-900">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-[11px] font-medium uppercase tracking-wide text-slate-warm-500">
                        Draft
                    </p>

                    <p
                        class="mt-2 text-2xl font-bold text-ink-900 dark:text-parchment-50"
                        x-text="documents.filter(d => d.status === 'draft').length"
                    ></p>
                </div>

                <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-amber-50 text-amber-700 dark:bg-amber-900/20 dark:text-amber-400">
                    <svg width="17" height="17" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="9"/>
                        <path d="M12 7v5l3 2"/>
                    </svg>
                </div>
            </div>
        </div>


        {{-- Revisi --}}
        <div class="rounded-2xl border border-parchment-300 bg-white p-4 shadow-theme-xs dark:border-slate-warm-800 dark:bg-slate-warm-900">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-[11px] font-medium uppercase tracking-wide text-slate-warm-500">
                        Revisi
                    </p>

                    <p
                        class="mt-2 text-2xl font-bold text-ink-900 dark:text-parchment-50"
                        x-text="documents.filter(d => d.status === 'revisi').length"
                    ></p>
                </div>

                <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-orange-50 text-orange-700 dark:bg-orange-900/20 dark:text-orange-400">
                    <svg width="17" height="17" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2">
                        <path d="M3 12a9 9 0 1 0 3-6.7L3 8"/>
                        <path d="M3 3v5h5"/>
                    </svg>
                </div>
            </div>
        </div>


        {{-- Review Marketing --}}
        <div class="rounded-2xl border border-parchment-300 bg-white p-4 shadow-theme-xs dark:border-slate-warm-800 dark:bg-slate-warm-900">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-[11px] font-medium uppercase tracking-wide text-slate-warm-500">
                        Review Marketing
                    </p>

                    <p
                        class="mt-2 text-2xl font-bold text-ink-900 dark:text-parchment-50"
                        x-text="documents.filter(d => d.status === 'review_marketing').length"
                    ></p>
                </div>

                <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-blue-50 text-blue-700 dark:bg-blue-900/20 dark:text-blue-400">
                    <svg width="17" height="17" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2">
                        <circle cx="11" cy="11" r="8"/>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"/>
                    </svg>
                </div>
            </div>
        </div>


        {{-- Disetujui --}}
        <div class="rounded-2xl border border-parchment-300 bg-white p-4 shadow-theme-xs dark:border-slate-warm-800 dark:bg-slate-warm-900">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-[11px] font-medium uppercase tracking-wide text-slate-warm-500">
                        Disetujui
                    </p>

                    <p
                        class="mt-2 text-2xl font-bold text-ink-900 dark:text-parchment-50"
                        x-text="documents.filter(d => d.status === 'disetujui').length"
                    ></p>
                </div>

                <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-emerald-50 text-emerald-700 dark:bg-emerald-900/20 dark:text-emerald-400">
                    <svg width="17" height="17" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2">
                        <path d="M20 6L9 17l-5-5"/>
                    </svg>
                </div>
            </div>
        </div>

    </div>


    
    {{-- FILTER & SEARCH --}}
    
    <div class="rounded-2xl border border-parchment-300 bg-white p-3 shadow-theme-xs dark:border-slate-warm-800 dark:bg-slate-warm-900">

        <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">

            {{-- Status --}}
            <div class="flex overflow-x-auto rounded-xl bg-parchment-100 p-1 dark:bg-slate-warm-800">

                <button
                    @click="filterStatus = 'all'"
                    :class="filterStatus === 'all'
                        ? 'bg-white text-ink-900 shadow-sm dark:bg-slate-warm-700 dark:text-white'
                        : 'text-slate-warm-500 hover:text-ink-900 dark:hover:text-white'"
                    class="whitespace-nowrap rounded-lg px-3 py-2 text-xs font-medium transition"
                >
                    Semua
                </button>

                <button
                    @click="filterStatus = 'draft'"
                    :class="filterStatus === 'draft'
                        ? 'bg-white text-ink-900 shadow-sm dark:bg-slate-warm-700 dark:text-white'
                        : 'text-slate-warm-500 hover:text-ink-900 dark:hover:text-white'"
                    class="whitespace-nowrap rounded-lg px-3 py-2 text-xs font-medium transition"
                >
                    Draft
                </button>

                <button
                    @click="filterStatus = 'revisi'"
                    :class="filterStatus === 'revisi'
                        ? 'bg-white text-ink-900 shadow-sm dark:bg-slate-warm-700 dark:text-white'
                        : 'text-slate-warm-500 hover:text-ink-900 dark:hover:text-white'"
                    class="whitespace-nowrap rounded-lg px-3 py-2 text-xs font-medium transition"
                >
                    Revisi
                </button>

                <button
                    @click="filterStatus = 'review_marketing'"
                    :class="filterStatus === 'review_marketing'
                        ? 'bg-white text-ink-900 shadow-sm dark:bg-slate-warm-700 dark:text-white'
                        : 'text-slate-warm-500 hover:text-ink-900 dark:hover:text-white'"
                    class="whitespace-nowrap rounded-lg px-3 py-2 text-xs font-medium transition"
                >
                    Review Marketing
                </button>

                <button
                    @click="filterStatus = 'disetujui'"
                    :class="filterStatus === 'disetujui'
                        ? 'bg-white text-ink-900 shadow-sm dark:bg-slate-warm-700 dark:text-white'
                        : 'text-slate-warm-500 hover:text-ink-900 dark:hover:text-white'"
                    class="whitespace-nowrap rounded-lg px-3 py-2 text-xs font-medium transition"
                >
                    Disetujui
                </button>

            </div>


            {{-- Search --}}
            <div class="relative w-full lg:w-72">

                <svg
                    class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-slate-warm-400"
                    width="15"
                    height="15"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                >
                    <circle cx="11" cy="11" r="8"/>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"/>
                </svg>

                <input
                    type="text"
                    x-model="searchQuery"
                    placeholder="Cari dokumen atau nomor..."
                    class="h-10 w-full rounded-xl border border-parchment-300 bg-parchment-25 pl-9 pr-3 text-xs outline-none transition placeholder:text-slate-warm-400 focus:border-ink-900 focus:ring-2 focus:ring-ink-900/5 dark:border-slate-warm-700 dark:bg-slate-warm-800 dark:text-parchment-100 dark:focus:border-bronze-500"
                >

            </div>

        </div>

    </div>


    
    {{-- DOCUMENT TABLE --}}
    <div class="overflow-hidden rounded-2xl border border-parchment-300 bg-white shadow-theme-xs dark:border-slate-warm-800 dark:bg-slate-warm-900">

        {{-- Table Header --}}
        <div class="flex items-center justify-between border-b border-parchment-200 px-5 py-4 dark:border-slate-warm-800">

            <div>
                <h2 class="text-sm font-semibold text-ink-900 dark:text-parchment-50">
                    Daftar Dokumen
                </h2>

                <p class="mt-0.5 text-[11px] text-slate-warm-500">
                    Dokumen yang tersimpan pada akun Anda
                </p>
            </div>

            <div class="flex items-center gap-2">

                <button
                    type="button"
                    @click="deleteAllDocuments()"
                    x-show="documents.length > 0"
                    class="inline-flex h-8 items-center gap-1.5 rounded-lg border border-transparent px-2.5 text-[11px] font-semibold text-slate-warm-400 transition hover:border-red-200 hover:bg-red-50 hover:text-red-600 dark:hover:border-red-900/40 dark:hover:bg-red-900/20 dark:hover:text-red-400"
                    title="Hapus semua dokumen"
                >
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2">
                        <polyline points="3 6 5 6 21 6"/>
                        <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6"/>
                        <path d="M10 11v6"/>
                        <path d="M14 11v6"/>
                        <path d="M9 6V4a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v2"/>
                    </svg>

                    Hapus Semua
                </button>

                <span
                    class="rounded-full bg-parchment-100 px-2.5 py-1 text-[11px] font-medium text-slate-warm-600 dark:bg-slate-warm-800 dark:text-parchment-300"
                    x-text="documents.length + ' dokumen'"
                ></span>

            </div>

        </div>


        <div class="overflow-x-auto">

            <table class="min-w-full text-left">

                <thead class="bg-parchment-50 dark:bg-slate-warm-800/50">

                    <tr>

                        <th class="px-5 py-3 text-[10px] font-semibold uppercase tracking-[0.12em] text-slate-warm-500">
                            Dokumen
                        </th>

                        <th class="px-5 py-3 text-[10px] font-semibold uppercase tracking-[0.12em] text-slate-warm-500">
                            Nomor
                        </th>

                        <th class="px-5 py-3 text-[10px] font-semibold uppercase tracking-[0.12em] text-slate-warm-500">
                            Tanggal
                        </th>

                        <th class="px-5 py-3 text-[10px] font-semibold uppercase tracking-[0.12em] text-slate-warm-500">
                            Status
                        </th>

                        <th class="px-5 py-3 text-right text-[10px] font-semibold uppercase tracking-[0.12em] text-slate-warm-500">
                            Aksi
                        </th>

                    </tr>

                </thead>


                <tbody>

                    <template
                        x-for="doc in filteredDocuments"
                        :key="doc.id"
                    >

                        <tr class="group border-t border-parchment-200 transition hover:bg-parchment-50 dark:border-slate-warm-800 dark:hover:bg-slate-warm-800/40">

                            {{-- Document --}}
                            <td class="px-5 py-4">

                                <div class="flex items-center gap-3">

                                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-parchment-200 bg-parchment-50 text-ink-900 dark:border-slate-warm-700 dark:bg-slate-warm-800 dark:text-parchment-200">

                                        <svg width="17" height="17" viewBox="0 0 24 24" fill="none"
                                            stroke="currentColor" stroke-width="1.8">
                                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                                            <polyline points="14 2 14 8 20 8"/>
                                            <line x1="8" y1="13" x2="16" y2="13"/>
                                            <line x1="8" y1="17" x2="14" y2="17"/>
                                        </svg>

                                    </div>

                                    <div class="min-w-0">

                                        <div
                                            class="max-w-[280px] truncate text-sm font-semibold text-ink-900 dark:text-parchment-100"
                                            x-text="doc.title"
                                        ></div>

                                        <div
                                            class="mt-0.5 text-[11px] text-slate-warm-500"
                                            x-text="doc.category"
                                        ></div>

                                    </div>

                                </div>

                            </td>


                            {{-- ID --}}
                            <td class="px-5 py-4">

                                <span
                                    class="font-mono text-[11px] font-medium text-slate-warm-600 dark:text-parchment-300"
                                    x-text="doc.nomorSurat"
                                ></span>

                            </td>


                            {{-- Date --}}
                            <td class="px-5 py-4">

                                <div
                                    class="text-xs font-medium text-slate-warm-700 dark:text-parchment-200"
                                    x-text="doc.date"
                                ></div>

                                <div class="mt-0.5 text-[10px] text-slate-warm-400">
                                    Dibuat
                                </div>

                            </td>


                            {{-- Status --}}
                            <td class="px-5 py-4">

                                <template x-if="doc.status === 'draft'">
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-amber-50 px-2.5 py-1 text-[10px] font-semibold text-amber-700 dark:bg-amber-900/20 dark:text-amber-400">
                                        <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>
                                        Draft
                                    </span>
                                </template>

                                <template x-if="doc.status === 'review_marketing'">
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-blue-50 px-2.5 py-1 text-[10px] font-semibold text-blue-700 dark:bg-blue-900/20 dark:text-blue-400">
                                        <span class="h-1.5 w-1.5 rounded-full bg-blue-500"></span>
                                        Review Marketing
                                    </span>
                                </template>

                                <template x-if="doc.status === 'revisi'">
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-orange-50 px-2.5 py-1 text-[10px] font-semibold text-orange-700 dark:bg-orange-900/20 dark:text-orange-400">
                                        <span class="h-1.5 w-1.5 rounded-full bg-orange-500"></span>
                                        Revisi
                                    </span>
                                </template>

                                <template x-if="doc.status === 'disetujui'">
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-2.5 py-1 text-[10px] font-semibold text-emerald-700 dark:bg-emerald-900/20 dark:text-emerald-400">
                                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                        Disetujui
                                    </span>
                                </template>

                                <template x-if="!['draft', 'review_marketing', 'revisi', 'disetujui'].includes(doc.status)">
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-slate-100 px-2.5 py-1 text-[10px] font-semibold text-slate-600">
                                        <span class="h-1.5 w-1.5 rounded-full bg-slate-400"></span>
                                        <span x-text="doc.statusLabel"></span>
                                    </span>
                                </template>

                            </td>


                            {{-- Actions --}}
                            <td class="px-5 py-4">

                                <div class="flex justify-end gap-1.5">

                                    @if($isAdmin)
                                        {{-- Kirim Review (draft/revisi) --}}
                                        <button
                                            type="button"
                                            x-show="doc.status === 'draft' || doc.status === 'revisi'"
                                            @click="updateDocumentStatus(doc.databaseId, 'review_marketing', 'kirim')"
                                            class="inline-flex h-8 items-center gap-1.5 rounded-lg border border-blue-200 bg-blue-50 px-2.5 text-[11px] font-semibold text-blue-700 transition hover:bg-blue-100 dark:border-blue-900/40 dark:bg-blue-900/20 dark:text-blue-300"
                                            title="Kirim ke marketing untuk direview"
                                        >
                                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none"
                                                stroke="currentColor" stroke-width="2">
                                                <line x1="22" y1="2" x2="11" y2="13"/>
                                                <polygon points="22 2 15 22 11 13 2 9 22 2"/>
                                            </svg>

                                            Kirim Review
                                        </button>

                                        {{-- Lihat Revisi (admin, status revisi) --}}
                                        <button
                                            type="button"
                                            x-show="doc.status === 'revisi'"
                                            @click="showRevisionNotes(doc)"
                                            class="inline-flex h-8 items-center gap-1.5 rounded-lg border border-amber-200 bg-amber-50 px-2.5 text-[11px] font-semibold text-amber-700 transition hover:bg-amber-100 dark:border-amber-900/40 dark:bg-amber-900/20 dark:text-amber-300"
                                            title="Lihat alasan revisi dari marketing"
                                        >
                                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none"
                                                stroke="currentColor" stroke-width="2">
                                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                                                <polyline points="14 2 14 8 20 8"/>
                                                <line x1="8" y1="13" x2="16" y2="13"/>
                                                <line x1="8" y1="17" x2="16" y2="17"/>
                                            </svg>

                                            Lihat Revisi
                                        </button>

                                        {{-- Edit (admin) --}}
                                        <button
                                            type="button"
                                            @click="window.location.href = `/documents/${doc.databaseId}/edit`"
                                            class="inline-flex h-8 items-center gap-1.5 rounded-lg border border-parchment-300 px-2.5 text-[11px] font-semibold text-ink-900 transition hover:border-ink-900 hover:bg-ink-900 hover:text-white dark:border-slate-warm-700 dark:text-parchment-200 dark:hover:border-bronze-500 dark:hover:bg-bronze-500 dark:hover:text-ink-900"
                                        >
                                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none"
                                                stroke="currentColor" stroke-width="2">
                                                <path d="M12 20h9"/>
                                                <path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4Z"/>
                                            </svg>

                                            Lanjut
                                        </button>


                                        {{-- Delete (admin) --}}
                                        <button
                                            type="button"
                                            @click="deleteDocument(doc.databaseId)"
                                            class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-transparent text-slate-warm-400 transition hover:border-red-200 hover:bg-red-50 hover:text-red-600 dark:hover:border-red-900/40 dark:hover:bg-red-900/20"
                                            title="Hapus dokumen"
                                        >
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none"
                                                stroke="currentColor" stroke-width="2">
                                                <polyline points="3 6 5 6 21 6"/>
                                                <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6"/>
                                                <path d="M10 11v6"/>
                                                <path d="M14 11v6"/>
                                                <path d="M9 6V4a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v2"/>
                                            </svg>
                                        </button>
                                    @else
                                        {{-- Setujui (marketer, review_marketing) --}}
                                        <button
                                            type="button"
                                            x-show="doc.status === 'review_marketing'"
                                            @click="updateDocumentStatus(doc.databaseId, 'disetujui', 'setujui')"
                                            class="inline-flex h-8 items-center gap-1.5 rounded-lg border border-emerald-200 bg-emerald-50 px-2.5 text-[11px] font-semibold text-emerald-700 transition hover:bg-emerald-100 dark:border-emerald-900/40 dark:bg-emerald-900/20 dark:text-emerald-300"
                                            title="Setujui dokumen"
                                        >
                                            ✓ Setujui
                                        </button>

                                        {{-- Minta Revisi (marketer, review_marketing) --}}
                                        <button
                                            type="button"
                                            x-show="doc.status === 'review_marketing'"
                                            @click="requestRevision(doc.databaseId)"
                                            class="inline-flex h-8 items-center gap-1.5 rounded-lg border border-amber-200 bg-amber-50 px-2.5 text-[11px] font-semibold text-amber-700 transition hover:bg-amber-100 dark:border-amber-900/40 dark:bg-amber-900/20 dark:text-amber-300"
                                            title="Minta revisi ke admin"
                                        >
                                            ↺ Minta Revisi
                                        </button>

                                        {{-- Lihat (marketer, read-only) --}}
                                        <button
                                            type="button"
                                            @click="window.location.href = `/documents/${doc.databaseId}/edit`"
                                            class="inline-flex h-8 items-center gap-1.5 rounded-lg border border-parchment-300 px-2.5 text-[11px] font-semibold text-ink-900 transition hover:border-ink-900 hover:bg-ink-900 hover:text-white dark:border-slate-warm-700 dark:text-parchment-200 dark:hover:border-bronze-500 dark:hover:bg-bronze-500 dark:hover:text-ink-900"
                                        >
                                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none"
                                                stroke="currentColor" stroke-width="2">
                                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                                <circle cx="12" cy="12" r="3"/>
                                            </svg>

                                            Lihat
                                        </button>
                                    @endif

                                </div>

                            </td>

                        </tr>

                    </template>

                </tbody>

            </table>

        </div>


        {{-- Empty State --}}
        <div
            x-show="filteredDocuments.length === 0"
            x-cloak
            class="border-t border-parchment-200 px-6 py-14 text-center dark:border-slate-warm-800"
        >

            <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-parchment-100 text-slate-warm-400 dark:bg-slate-warm-800">

                <svg width="21" height="21" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="1.7">
                    <circle cx="11" cy="11" r="7"/>
                    <path d="m20 20-4-4"/>
                </svg>

            </div>

            <h3 class="mt-4 text-sm font-semibold text-ink-900 dark:text-parchment-50">
                Dokumen tidak ditemukan
            </h3>

            <p class="mt-1 text-xs text-slate-warm-500">
                Coba ubah kata pencarian atau filter status.
            </p>

        </div>

    </div>

</div>

@endsection