@extends('layouts.app')

@section('content')
@php
    $documentData = $documents->map(function ($doc) {
        return [
            'id' => 'DOC-' . str_pad($doc->id, 5, '0', STR_PAD_LEFT),
            'databaseId' => $doc->id,
            'title' => $doc->title,
            'category' => ucfirst($doc->type),
            'date' => $doc->created_at->format('d M Y'),
            'status' => $doc->status,
            'statusLabel' => ucfirst($doc->status),
            'signer' => $doc->footer_data['namaPenandatangan'] ?? 'Belum Ditentukan',
            'signerRole' => $doc->footer_data['jabatanPenandatangan'] ?? 'Belum Ditentukan',
            'pages' => count($doc->body_content) + 1, // +1 for header page
            'hasMaterai' => false
        ];
    });
@endphp
<div
    x-data="{
        filterStatus: 'all',
        searchQuery: '',

        documents: @js($documentData),

        deleteDocument(docId) {

            if (!confirm('Apakah Anda yakin ingin menghapus dokumen ini?')) {
                return;
            }

            fetch('/documents/' + docId, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                    'Accept': 'application/json'
                }
            })
            .then(response => {

                if (!response.ok) {
                    throw new Error('Gagal Menghapus dokumen.');
                }

                window.location.reload();

            })
            .catch(error => {

                alert(error.message);
                console.error(error);

            });

        }

    }"
>
    <!-- Page Header Bar -->
    <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="font-serif font-bold text-xl text-ink-900 dark:text-parchment-50">
                Arsip & Dokumen Saya
            </h1>
            <p class="text-xs text-slate-warm-500 dark:text-parchment-400 mt-1">
                Kelola daftar dokumen resmi, riwayat tanda tangan digital, dan salinan tersimpan.
            </p>
        </div>
        <a href="/" class="btn-primary text-xs shadow-sm">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
            </svg>
            <span>+ Buat Dokumen Baru</span>
        </a>
    </div>

    <!-- Filter & Search Controls -->
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3 rounded-xl border border-parchment-300 bg-white p-3 shadow-theme-xs dark:border-slate-warm-800 dark:bg-slate-warm-900">
        <!-- Status Filter Pills -->
        <div class="flex items-center gap-1.5 overflow-x-auto no-scrollbar">
            <button @click="filterStatus = 'all'" :class="filterStatus === 'all' ? 'bg-ink-900 text-white font-semibold' : 'text-slate-warm-600 hover:bg-parchment-100 dark:text-parchment-300'" class="px-3 py-1.5 rounded-lg text-xs transition-all">
                Semua Dokumen
            </button>
            <button @click="filterStatus = 'pending'" :class="filterStatus === 'pending' ? 'bg-bronze-600 text-white font-semibold' : 'text-slate-warm-600 hover:bg-parchment-100 dark:text-parchment-300'" class="px-3 py-1.5 rounded-lg text-xs transition-all">
                Menunggu TTD
            </button>
            <button @click="filterStatus = 'signed'" :class="filterStatus === 'signed' ? 'bg-seal-700 text-white font-semibold' : 'text-slate-warm-600 hover:bg-parchment-100 dark:text-parchment-300'" class="px-3 py-1.5 rounded-lg text-xs transition-all">
                Terverifikasi
            </button>
            <button @click="filterStatus = 'draft'" :class="filterStatus === 'draft' ? 'bg-parchment-300 text-ink-900 font-semibold' : 'text-slate-warm-600 hover:bg-parchment-100 dark:text-parchment-300'" class="px-3 py-1.5 rounded-lg text-xs transition-all">
                Draft
            </button>
        </div>

        <!-- Search Input -->
        <div class="relative w-full sm:w-64">
            <input type="text" x-model="searchQuery" placeholder="Cari judul / nomor..." class="w-full text-xs rounded-lg border border-parchment-300 bg-parchment-25 py-2 pl-8 pr-3 dark:bg-slate-warm-800 dark:border-slate-warm-700 dark:text-parchment-100" />
            <svg class="absolute left-2.5 top-2.5 text-slate-warm-400" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
            </svg>
        </div>
    </div>

    <!-- Document Grid View (Paper Card Thumbnails) -->
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">
        <template x-for="doc in documents.filter(d => (filterStatus === 'all' || d.status === filterStatus) && (d.title.toLowerCase().includes(searchQuery.toLowerCase()) || d.id.toLowerCase().includes(searchQuery.toLowerCase())))" :key="doc.id">
            <div class="doc-thumbnail p-4 bg-white dark:bg-slate-warm-900 flex flex-col justify-between h-full">
                <div>
                    <!-- Top Info: Category Badge & ID -->
                    <div class="flex items-center justify-between mb-3">
                        <span class="px-2 py-0.5 rounded text-[10px] font-mono font-semibold uppercase bg-parchment-200 text-ink-900 dark:bg-slate-warm-800 dark:text-parchment-200" x-text="doc.category"></span>
                        <span class="text-[10px] font-mono text-slate-warm-400" x-text="doc.id"></span>
                    </div>

                    <!-- Mini Paper Card Visual Preview -->
                    <div class="doc-thumbnail-preview rounded border border-parchment-200 mb-3 p-3 relative shadow-xs">
                        <div class="flex items-center justify-between mb-2">
                            <div class="w-12 h-2 rounded bg-ink-900"></div>
                            <span class="text-[9px] font-mono text-slate-warm-400" x-text="doc.pages + ' hlm'"></span>
                        </div>
                        <div class="doc-thumbnail-lines mb-3">
                            <div class="doc-thumbnail-line"></div>
                            <div class="doc-thumbnail-line"></div>
                            <div class="doc-thumbnail-line"></div>
                        </div>
                        <!-- Mini Stamp Pill -->
                        <div class="flex justify-end">
                            <span :class="{
                                'doc-status-signed': doc.status === 'signed',
                                'doc-status-pending': doc.status === 'pending',
                                'doc-status-verified': doc.status === 'verified',
                                'doc-status-draft': doc.status === 'draft',
                                'doc-status-archived': doc.status === 'archived'
                            }" class="doc-status text-[9px] py-0.5 px-1.5" x-text="doc.statusLabel"></span>
                        </div>
                    </div>

                    <!-- Title & Details -->
                    <h3 class="font-serif font-bold text-sm text-ink-900 dark:text-parchment-100 line-clamp-2 mb-2" x-text="doc.title"></h3>
                    <div class="text-xs text-slate-warm-500 dark:text-parchment-400 space-y-1 mb-4">
                        <p class="flex items-center gap-1.5">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                            <span x-text="doc.signer + ' (' + doc.signerRole + ')'"></span>
                        </p>
                        <p class="flex items-center gap-1.5 font-mono text-[11px]">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                            <span x-text="doc.date"></span>
                        </p>
                    </div>
                </div>

                <!-- Footer Card Action Buttons -->
                <div class="pt-3 border-t border-parchment-200 dark:border-slate-warm-800 flex items-center justify-between gap-2">
                    <button @click="window.location.href = `/documents/${doc.databaseId}/edit`" class="btn-secondary text-[11px] px-2.5 py-1.5 h-7">
                        Lanjut
                    </button>
                    <button @click="deleteDocument(doc.databaseId)" class="btn-secondary text-[11px] px-2.5 py-1.5 h-7">
                        Hapus
                    </button>
                    <button onclick="window.print()" class="btn-primary text-[11px] px-2.5 py-1.5 h-7">
                        Cetak PDF
                    </button>
                </div>
            </div>
        </template>
    </div>

</div>
@endsection
