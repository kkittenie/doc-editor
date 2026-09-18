@extends('layouts.app')

@section('content')
@php
    $customer = $customer ?? null;
    $existingDocument = $existingDocument ?? null;
@endphp

<div class="mx-auto max-w-4xl py-12">

    {{-- KONTEKS PELANGGAN (muncul saat masuk dari tombol "Lanjut") --}}
    @if ($customer)
        <div
            class="mb-8 flex flex-col gap-4 rounded-2xl border border-parchment-300 bg-white p-5 shadow-theme-xs dark:border-slate-warm-800 dark:bg-slate-warm-900 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-start gap-3">
                <span
                    class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-ink-900 text-white dark:bg-bronze-500 dark:text-ink-900">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="1.8">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" />
                        <circle cx="9" cy="7" r="4" />
                    </svg>
                </span>

                <div>
                    <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-slate-warm-500">
                        Pelanggan Terpilih
                    </p>

                    <h2 class="mt-0.5 font-serif text-lg font-bold text-ink-900 dark:text-parchment-50">
                        {{ $customer->name }}
                    </h2>

                    <div
                        class="mt-2 flex flex-wrap items-center gap-x-4 gap-y-1 text-[11px] text-slate-warm-500 dark:text-parchment-400">
                        <span>
                            Nomer Pelanggan:
                            <span
                                class="font-mono text-ink-800 dark:text-parchment-200">{{ $customer->customer_number }}</span>
                        </span>

                        <span>
                            Nomor Kontrak:
                            <span
                                class="font-mono text-ink-800 dark:text-parchment-200">{{ $customer->contract_number }}</span>
                        </span>

                        @if ($customer->active_date && $customer->finish_date)
                            <span>
                                Periode:
                                <span class="text-ink-800 dark:text-parchment-200">
                                    {{ $customer->active_date->format('d M Y') }} —
                                    {{ $customer->finish_date->format('d M Y') }}
                                </span>
                            </span>
                        @endif
                    </div>
                </div>
            </div>

            <a href="{{ route('documents') }}" class="btn-ghost shrink-0 text-xs">
                ← Dokumen Saya
            </a>
        </div>
    @endif

    <div class="mb-10 text-center">
        <h1 class="text-2xl font-bold text-ink-900 dark:text-parchment-50">
            {{ $customer ? 'Susun Kontrak Pelanggan' : 'Mulai Dokumen Baru' }}
        </h1>

        <p class="mt-2 text-sm text-slate-warm-600 dark:text-parchment-400">
            Buat dokumen dari awal beserta data barang &amp; service, atau impor file PDF/Word untuk diedit dan
            ditandatangani.
        </p>
    </div>

    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
        {{-- OPSI 1: BUAT DOKUMEN BARU --}}
        <div
            class="flex flex-col justify-between rounded-2xl border-2 border-dashed border-parchment-300 bg-white p-8 text-center transition hover:border-bronze-400 hover:shadow-md dark:border-slate-warm-700 dark:bg-slate-warm-900">
            <div>
                <div
                    class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-ink-900 text-white dark:bg-bronze-500 dark:text-ink-900">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2">
                        <line x1="12" y1="5" x2="12" y2="19" />
                        <line x1="5" y1="12" x2="19" y2="12" />
                    </svg>
                </div>

                <h2 class="font-semibold text-ink-900 dark:text-parchment-50">
                    Buat Dokumen Baru
                </h2>

                <p class="mt-1.5 text-xs text-slate-warm-500 dark:text-parchment-400">
                    Isi detail kontrak, data barang/service, lalu pilih template dan mulai menyusun di editor.
                </p>
            </div>

            <div class="mt-5">
                @if ($customer)
                    <a href="{{ route('documents.create', ['customer' => $customer->id]) }}"
                        class="btn-primary w-full text-xs">
                        Susun Kontrak untuk Pelanggan Ini →
                    </a>
                @else
                    <a href="{{ route('documents.create') }}" class="btn-primary w-full text-xs">
                        Buat Dokumen Baru →
                    </a>
                @endif
            </div>
        </div>

        {{-- OPSI 2: UPLOAD FILE (dinonaktifkan sementara) --}}
        <div
            class="relative flex flex-col justify-between rounded-2xl border-2 border-dashed border-parchment-200 bg-parchment-50/60 p-8 text-center opacity-70 dark:border-slate-warm-800 dark:bg-slate-warm-900/40">
            <span
                class="absolute right-4 top-4 rounded-full bg-parchment-200 px-2.5 py-1 text-[10px] font-bold uppercase tracking-wide text-slate-warm-500 dark:bg-slate-warm-800 dark:text-parchment-400">
                Segera Hadir
            </span>

            <div>
                <div
                    class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-slate-warm-200 text-slate-warm-500 dark:bg-slate-warm-800 dark:text-parchment-400">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2">
                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                        <polyline points="17 8 12 3 7 8" />
                        <line x1="12" y1="3" x2="12" y2="15" />
                    </svg>
                </div>

                <h2 class="font-semibold text-slate-warm-600 dark:text-parchment-300">
                    Upload PDF / Word
                </h2>

                <p class="mt-1.5 text-xs text-slate-warm-400 dark:text-parchment-500">
                    Fitur impor dokumen sedang disiapkan dan belum dapat digunakan.
                </p>
            </div>

            <button type="button" disabled class="btn-secondary mt-5 w-full cursor-not-allowed text-xs opacity-60">
                Belum tersedia
            </button>
        </div>
    </div>

    {{-- DOKUMEN TERSIMPAN UNTUK PELANGGAN INI --}}
    @if ($existingDocument)
        <div
            class="mt-8 flex flex-col gap-3 rounded-2xl border border-parchment-300 bg-white p-5 shadow-theme-xs dark:border-slate-warm-800 dark:bg-slate-warm-900 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-slate-warm-500">
                    Dokumen Tersimpan
                </p>

                <p class="mt-1 text-sm font-semibold text-ink-900 dark:text-parchment-50">
                    {{ $existingDocument->title }}
                </p>

                <p class="mt-0.5 text-[11px] text-slate-warm-500 dark:text-parchment-400">
                    Status: {{ $existingDocument->statusLabel() }} ·
                    Diperbarui {{ $existingDocument->updated_at?->format('d M Y H:i') }}
                </p>
            </div>

            <a href="{{ route('documents.edit', $existingDocument) }}" class="btn-secondary shrink-0 text-xs">
                Lanjutkan Edit Dokumen →
            </a>
        </div>
    @endif
</div>
@endsection