@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto py-16">
    <div class="text-center mb-10">
        <h1 class="text-2xl font-bold text-ink-900 dark:text-parchment-50">Mulai Dokumen Baru</h1>
        <p class="mt-2 text-sm text-slate-warm-600 dark:text-parchment-400">
            Buat dokumen dari awal, atau impor file PDF/Word untuk diedit dan ditandatangani.
        </p>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">

        <a href="{{ route('documents.create') }}"
            class="rounded-2xl border-2 border-dashed border-parchment-300 bg-white p-8 text-center transition hover:border-bronze-400 hover:shadow-md dark:border-slate-warm-700 dark:bg-slate-warm-900">
            <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-ink-900 text-white dark:bg-bronze-500 dark:text-ink-900">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
                </svg>
            </div>
            <h2 class="font-semibold text-ink-900 dark:text-parchment-50">Buat Dokumen Baru</h2>
            <p class="mt-1.5 text-xs text-slate-warm-500 dark:text-parchment-400">Susun header, footer, dan isi dari kosong.</p>
        </a>

        <form action="{{ route('documents.import') }}" method="POST" enctype="multipart/form-data"
            x-data="{ isUploading: false }" @submit="isUploading = true">
            @csrf
            <label
                class="block cursor-pointer rounded-2xl border-2 border-dashed border-parchment-300 bg-white p-8 text-center transition hover:border-bronze-400 hover:shadow-md dark:border-slate-warm-700 dark:bg-slate-warm-900"
                :class="isUploading ? 'opacity-60 pointer-events-none' : ''">
                <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-ink-900 text-white dark:bg-bronze-500 dark:text-ink-900">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/>
                    </svg>
                </div>
                <h2 class="font-semibold text-ink-900 dark:text-parchment-50">
                    <span x-show="!isUploading">Upload PDF / Word</span>
                    <span x-show="isUploading">Memproses...</span>
                </h2>
                <p class="mt-1.5 text-xs text-slate-warm-500 dark:text-parchment-400">
                    Format kompleks mungkin perlu dirapikan lagi setelah diimpor.
                </p>
                <input type="file" name="file" accept=".pdf,.doc,.docx" class="hidden"
                    @change="$event.target.form.requestSubmit()">
            </label>
        </form>

    </div>
</div>
@endsection