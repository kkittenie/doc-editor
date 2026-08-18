@extends('layouts.app')

@section('content')

<div class="space-y-6">

     
    {{--HEADER DASHBOARD--}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">

        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                Dashboard
            </h1>

            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                Kelola dan pantau dokumen legal Anda.
            </p>
        </div>

        {{-- Tambah Dokumen --}}

        <a href="{{ route('documents.create') }}" class="btn-primary text-xs shadow-sm">
            <svg
                xmlns="http://www.w3.org/2000/svg"
                class="w-5 h-5"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
            >
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M12 4v16m8-8H4"
                />
            </svg>

            Tambah Dokumen Baru
        </a>

    </div>


    {{-- SUMMARY CARDS --}}
    <div class="grid grid-cols-2 gap-3 lg:grid-cols-4">

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


        {{-- Pending --}}
        <div class="rounded-2xl border border-parchment-300 bg-white p-4 shadow-theme-xs dark:border-slate-warm-800 dark:bg-slate-warm-900">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-[11px] font-medium uppercase tracking-wide text-slate-warm-500">
                        Menunggu TTD
                    </p>

                    <p
                        class="mt-2 text-2xl font-bold text-ink-900 dark:text-parchment-50"
                        x-text="documents.filter(d => d.status === 'pending').length"
                    ></p>
                </div>

                <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-blue-50 text-blue-700 dark:bg-blue-900/20 dark:text-blue-400">
                    <svg width="17" height="17" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2">
                        <path d="M12 2v20"/>
                        <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7H14a3.5 3.5 0 0 1 0 7H6"/>
                    </svg>
                </div>
            </div>
        </div>


        {{-- Signed --}}
        <div class="rounded-2xl border border-parchment-300 bg-white p-4 shadow-theme-xs dark:border-slate-warm-800 dark:bg-slate-warm-900">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-[11px] font-medium uppercase tracking-wide text-slate-warm-500">
                        Terverifikasi
                    </p>

                    <p
                        class="mt-2 text-2xl font-bold text-ink-900 dark:text-parchment-50"
                        x-text="documents.filter(d => d.status === 'signed').length"
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


    {{-- DOKUMEN TERBARU --}}
    <div class="bg-white dark:bg-gray-800
                border border-gray-200 dark:border-gray-700
                rounded-xl overflow-hidden
                border border-gray-200 dark:border-gray-700">

        {{-- Header --}}
        <div class="flex items-center justify-between
                    px-5 py-4
                    border-b border-gray-200 dark:border-gray-700">

            <div>
                <h2 class="font-semibold text-gray-900 dark:text-white">
                    Dokumen Terbaru
                </h2>

                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                    Dokumen yang terakhir Anda buat.
                </p>
            </div>

            <a href="{{ route('documents') }}"
               class="text-sm font-medium
                      text-blue-600 hover:text-blue-700
                      dark:text-blue-400">
                Lihat Semua
            </a>

        </div>


        {{-- List Dokumen --}}
        @if($recentDocuments->isNotEmpty())

            <div class="divide-y divide-gray-200 dark:divide-gray-700">

                @foreach($recentDocuments as $document)

                    <a href="{{ route('documents.edit', $document) }}"
                       class="flex items-center justify-between
                              gap-4 px-5 py-4
                              hover:bg-gray-50
                              dark:hover:bg-gray-700/50
                              transition">

                        {{-- Icon + Info --}}
                        <div class="flex items-center gap-4 min-w-0">

                            <div class="w-10 h-10 shrink-0
                                        rounded-lg
                                        bg-blue-100 dark:bg-blue-900/30
                                        flex items-center justify-center">

                                <svg xmlns="http://www.w3.org/2000/svg"
                                     class="w-5 h-5
                                            text-blue-600
                                            dark:text-blue-400"
                                     fill="none"
                                     viewBox="0 0 24 24"
                                     stroke="currentColor">
                                    <path stroke-linecap="round"
                                          stroke-linejoin="round"
                                          stroke-width="2"
                                          d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a2 2 0 011.414.586l4.414 4.414A2 2 0 0119 9.414V19a2 2 0 01-2 2z"/>
                                </svg>

                            </div>


                            <div class="min-w-0">

                                <p class="font-medium
                                          text-gray-900
                                          dark:text-white
                                          truncate">

                                    {{ $document->title }}

                                </p>

                                <p class="text-sm
                                          text-gray-500
                                          dark:text-gray-400">

                                    {{ ucfirst($document->type ?? 'Umum') }}

                                </p>

                            </div>

                        </div>


                        {{-- Status + Waktu --}}
                        <div class="flex items-center gap-4 shrink-0">

                            @if($document->status === 'draft')

                                <span class="hidden sm:inline-flex
                                             px-2.5 py-1
                                             rounded-full
                                             text-xs font-medium
                                             bg-yellow-100
                                             text-yellow-700
                                             dark:bg-yellow-900/30
                                             dark:text-yellow-400">
                                    Draft
                                </span>

                            @elseif($document->status === 'final')

                                <span class="hidden sm:inline-flex
                                             px-2.5 py-1
                                             rounded-full
                                             text-xs font-medium
                                             bg-green-100
                                             text-green-700
                                             dark:bg-green-900/30
                                             dark:text-green-400">
                                    Final
                                </span>

                            @else

                                <span class="hidden sm:inline-flex
                                             px-2.5 py-1
                                             rounded-full
                                             text-xs font-medium
                                             bg-gray-100
                                             text-gray-600
                                             dark:bg-gray-700
                                             dark:text-gray-300">
                                    Archived
                                </span>

                            @endif


                            <span class="text-sm
                                         text-gray-500
                                         dark:text-gray-400">

                                {{ $document->created_at?->diffForHumans() }}

                            </span>

                        </div>

                    </a>

                @endforeach

            </div>

        @else

            {{-- Empty State --}}
            <div class="px-5 py-12 text-center">

                <div class="w-12 h-12 mx-auto
                            rounded-full
                            bg-gray-100 dark:bg-gray-700
                            flex items-center justify-center">

                    <svg xmlns="http://www.w3.org/2000/svg"
                         class="w-6 h-6
                                text-gray-400"
                         fill="none"
                         viewBox="0 0 24 24"
                         stroke="currentColor">
                        <path stroke-linecap="round"
                              stroke-linejoin="round"
                              stroke-width="2"
                              d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a2 2 0 011.414.586l4.414 4.414A2 2 0 0119 9.414V19a2 2 0 01-2 2z"/>
                    </svg>

                </div>

                <p class="mt-3 text-sm
                          text-gray-500
                          dark:text-gray-400">
                    Belum ada dokumen.
                </p>

                <a href="{{ route('documents.create') }}"
                   class="inline-flex items-center gap-2
                          mt-4 px-4 py-2
                          bg-blue-600 hover:bg-blue-700
                          text-white text-sm font-medium
                          rounded-lg transition">

                    <svg xmlns="http://www.w3.org/2000/svg"
                         class="w-4 h-4"
                         fill="none"
                         viewBox="0 0 24 24"
                         stroke="currentColor">
                        <path stroke-linecap="round"
                              stroke-linejoin="round"
                              stroke-width="2"
                              d="M12 4v16m8-8H4"/>
                    </svg>

                    Buat Dokumen

                </a>

            </div>

        @endif

    </div>

</div>

@endsection

