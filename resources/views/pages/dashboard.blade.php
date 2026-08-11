@extends('layouts.app')

@section('content')

<div class="space-y-6">

    {{-- =========================
        HEADER DASHBOARD
    ========================== --}}
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

        <a href="{{ route('editor') }}" class="btn-primary text-xs shadow-sm">
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


    {{-- =========================
        STATISTIK
    ========================== --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">

        {{-- Total Dokumen --}}
        <div class="bg-white dark:bg-gray-800
                    border border-gray-200 dark:border-gray-700
                    rounded-xl p-5">

            <div class="flex items-center justify-between">

                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        Total Dokumen
                    </p>

                    <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">
                        {{ $totalDocuments }}
                    </p>
                </div>

                <div class="w-11 h-11 rounded-lg
                            bg-blue-100 dark:bg-blue-900/30
                            flex items-center justify-center">

                    <svg xmlns="http://www.w3.org/2000/svg"
                         class="w-6 h-6 text-blue-600 dark:text-blue-400"
                         fill="none"
                         viewBox="0 0 24 24"
                         stroke="currentColor">
                        <path stroke-linecap="round"
                              stroke-linejoin="round"
                              stroke-width="2"
                              d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a2 2 0 011.414.586l4.414 4.414A2 2 0 0119 9.414V19a2 2 0 01-2 2z"/>
                    </svg>

                </div>

            </div>

        </div>


        {{-- Draft --}}
        <div class="bg-white dark:bg-gray-800
                    border border-gray-200 dark:border-gray-700
                    rounded-xl p-5">

            <div class="flex items-center justify-between">

                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        Dokumen Draft
                    </p>

                    <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">
                        {{ $draftDocuments }}
                    </p>
                </div>

                <div class="w-11 h-11 rounded-lg
                            bg-yellow-100 dark:bg-yellow-900/30
                            flex items-center justify-center">

                    <svg xmlns="http://www.w3.org/2000/svg"
                         class="w-6 h-6 text-yellow-600 dark:text-yellow-400"
                         fill="none"
                         viewBox="0 0 24 24"
                         stroke="currentColor">
                        <path stroke-linecap="round"
                              stroke-linejoin="round"
                              stroke-width="2"
                              d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 011.414 1.414v.586a2 2 0 01-.586 1.414l-7.5 7.5L11 15l-.5-2.5 7.5-7.5z"/>
                    </svg>

                </div>

            </div>

        </div>


        {{-- Final --}}
        <div class="bg-white dark:bg-gray-800
                    border border-gray-200 dark:border-gray-700
                    rounded-xl p-5">

            <div class="flex items-center justify-between">

                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        Dokumen Final
                    </p>

                    <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">
                        {{ $finalDocuments }}
                    </p>
                </div>

                <div class="w-11 h-11 rounded-lg
                            bg-green-100 dark:bg-green-900/30
                            flex items-center justify-center">

                    <svg xmlns="http://www.w3.org/2000/svg"
                         class="w-6 h-6 text-green-600 dark:text-green-400"
                         fill="none"
                         viewBox="0 0 24 24"
                         stroke="currentColor">
                        <path stroke-linecap="round"
                              stroke-linejoin="round"
                              stroke-width="2"
                              d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>

                </div>

            </div>

        </div>

    </div>


    {{-- =========================
        DOKUMEN TERBARU
    ========================== --}}
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

                <a href="{{ route('editor') }}"
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

