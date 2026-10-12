@extends('layouts.app')

@section('content')
@php
    $cards = $cards ?? [];
@endphp

<div class="mx-auto max-w-5xl py-8">

    {{-- TITLE --}}
    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="font-serif text-2xl font-bold text-ink-900 dark:text-parchment-50">
                Uji Coba Template Dokumen
            </h1>
            <p class="mt-1 text-sm text-slate-warm-500 dark:text-parchment-400">
                Pilih template untuk langsung membukanya di editor tanpa data pelanggan.
            </p>
        </div>

        <a href="{{ route('documents') }}"
            class="btn-secondary shrink-0 px-5 py-2.5 text-center text-sm">
            ← Kembali ke Dokumen Saya
        </a>
    </div>

    {{-- GRID TEMPLATE --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
        @forelse ($cards as $card)
            <div class="template-card flex flex-col justify-between rounded-2xl border border-parchment-300 bg-white p-5 shadow-sm dark:border-slate-warm-700 dark:bg-slate-warm-900">
                <div>
                    <div class="mb-3 flex items-center justify-between">
                        <span class="rounded bg-bronze-100 px-2.5 py-0.5 font-mono text-[10px] font-semibold text-bronze-800 dark:bg-bronze-900 dark:text-bronze-300">
                            {{ $card['name'] }}
                        </span>

                        @if ($card['code'])
                            <span class="font-mono text-[10px] text-slate-warm-400">{{ $card['code'] }}</span>
                        @endif
                    </div>

                    <div class="template-card-preview mb-4 flex min-h-[110px] flex-col items-center justify-center rounded bg-parchment-50 p-4 shadow-xs dark:bg-slate-warm-800">
                        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="1.5" class="mb-2 text-parchment-300">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
                            <path d="M14 2v6h6" />
                        </svg>

                        <span class="text-center text-[10px] text-slate-warm-400">{{ $card['name'] }}</span>
                    </div>

                    <h3 class="mb-2 font-serif text-base font-bold text-ink-900 dark:text-parchment-100">
                        {{ $card['name'] }}
                    </h3>

                    <p class="mb-4 text-xs leading-relaxed text-slate-warm-500 dark:text-parchment-400">
                        {{ $card['title'] }}
                    </p>
                </div>

                <a href="{{ route('documents.devPreview', $card['key']) }}"
                    class="btn-primary w-full py-2.5 text-center text-xs">
                    Buka di Editor →
                </a>
            </div>
        @empty
            <div class="col-span-full rounded-2xl border border-parchment-300 bg-white p-8 text-center text-sm text-slate-warm-500 dark:border-slate-warm-700 dark:bg-slate-warm-900 dark:text-parchment-400">
                Tidak ada template kontrak yang tersedia.
            </div>
        @endforelse
    </div>
</div>

@endsection
