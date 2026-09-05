@extends('layouts.app')

@section('content')

<div class="max-w-4xl mx-auto space-y-6">

    {{-- Header --}}
    <div>
        <h1 class="font-serif text-2xl font-bold text-ink-900 dark:text-parchment-100">
            Ubah Kata Sandi
        </h1>

        <p class="mt-1 text-sm text-slate-warm-500 dark:text-slate-warm-400">
            Perbarui kata sandi akun Anda secara berkala demi keamanan.
        </p>
    </div>


    {{-- Success Message --}}
    @if(session('success'))
        <div class="flex items-center gap-3
                    px-4 py-3 rounded-lg
                    border border-green-200 bg-green-50
                    text-green-700
                    dark:border-green-900
                    dark:bg-green-950/30
                    dark:text-green-400">

            <svg class="w-5 h-5 shrink-0"
                 fill="none"
                 stroke="currentColor"
                 viewBox="0 0 24 24">

                <path stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M5 13l4 4L19 7"/>

            </svg>

            <span class="text-sm font-medium">
                {{ session('success') }}
            </span>
        </div>
    @endif


    {{-- Validation Errors --}}
    @if($errors->any())
        <div class="rounded-lg
                    border border-seal-200
                    bg-seal-50
                    px-4 py-3
                    dark:border-seal-900
                    dark:bg-seal-950/30">

            <p class="text-sm font-semibold text-seal-700 dark:text-seal-400">
                Ada data yang perlu diperbaiki:
            </p>

            <ul class="mt-2 list-disc list-inside text-xs
                       text-seal-600 dark:text-seal-400">

                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach

            </ul>
        </div>
    @endif


    {{-- Password Card --}}
    <div class="rounded-xl
                border border-parchment-300
                bg-white
                shadow-theme-sm
                dark:border-slate-warm-800
                dark:bg-slate-warm-900">

        {{-- Card Header --}}
        <div class="px-6 py-5
                    border-b border-parchment-200
                    dark:border-slate-warm-800">

            <div class="flex items-center gap-4">

                <div class="w-14 h-14 shrink-0
                            rounded-full
                            bg-parchment-100
                            dark:bg-slate-warm-800
                            flex items-center justify-center">

                    <svg class="w-6 h-6 text-slate-warm-600 dark:text-parchment-200"
                         fill="none"
                         stroke="currentColor"
                         viewBox="0 0 24 24">

                        <path stroke-linecap="round"
                              stroke-linejoin="round"
                              stroke-width="2"
                              d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>

                    </svg>

                </div>


                <div>

                    <h2 class="font-serif font-bold
                               text-base
                               text-ink-900
                               dark:text-parchment-100">

                        Kata Sandi

                    </h2>

                    <p class="text-xs
                              text-slate-warm-500
                              dark:text-slate-warm-400">

                        Gunakan minimal 8 karakter dan jangan bagikan kepada siapa pun.

                    </p>

                </div>

            </div>

        </div>


        {{-- Form --}}
        <form action="{{ route('profile.password.update') }}"
              method="POST">

            @csrf
            @method('PUT')

            <div class="p-6 space-y-5">

                {{-- Kata sandi saat ini --}}
                <div>

                    <label for="current_password"
                           class="block mb-2
                                  text-xs font-semibold
                                  text-ink-800
                                  dark:text-parchment-200">

                        Kata Sandi Saat Ini

                    </label>

                    <input
                        type="password"
                        id="current_password"
                        name="current_password"
                        required
                        autocomplete="current-password"
                        class="w-full rounded-lg
                               border border-parchment-300
                               bg-white
                               px-3.5 py-2.5
                               text-sm
                               text-ink-900
                               outline-none
                               focus:border-bronze-500
                               focus:ring-2
                               focus:ring-bronze-500/20
                               dark:border-slate-warm-700
                               dark:bg-slate-warm-950
                               dark:text-parchment-100"
                    >

                </div>


                {{-- Kata sandi baru --}}
                <div>

                    <label for="password"
                           class="block mb-2
                                  text-xs font-semibold
                                  text-ink-800
                                  dark:text-parchment-200">

                        Kata Sandi Baru

                    </label>

                    <input
                        type="password"
                        id="password"
                        name="password"
                        required
                        minlength="8"
                        autocomplete="new-password"
                        class="w-full rounded-lg
                               border border-parchment-300
                               bg-white
                               px-3.5 py-2.5
                               text-sm
                               text-ink-900
                               outline-none
                               focus:border-bronze-500
                               focus:ring-2
                               focus:ring-bronze-500/20
                               dark:border-slate-warm-700
                               dark:bg-slate-warm-950
                               dark:text-parchment-100"
                    >

                </div>


                {{-- Konfirmasi kata sandi baru --}}
                <div>

                    <label for="password_confirmation"
                           class="block mb-2
                                  text-xs font-semibold
                                  text-ink-800
                                  dark:text-parchment-200">

                        Konfirmasi Kata Sandi Baru

                    </label>

                    <input
                        type="password"
                        id="password_confirmation"
                        name="password_confirmation"
                        required
                        minlength="8"
                        autocomplete="new-password"
                        class="w-full rounded-lg
                               border border-parchment-300
                               bg-white
                               px-3.5 py-2.5
                               text-sm
                               text-ink-900
                               outline-none
                               focus:border-bronze-500
                               focus:ring-2
                               focus:ring-bronze-500/20
                               dark:border-slate-warm-700
                               dark:bg-slate-warm-950
                               dark:text-parchment-100"
                    >

                </div>

            </div>

            {{-- Form Footer --}}
            <div class="flex items-center justify-end gap-3
                        px-6 py-4
                        border-t border-parchment-200
                        dark:border-slate-warm-800">

                <a href="{{ route(auth()->user()->homeRouteName()) }}"
                   class="px-4 py-2
                          rounded-lg
                          text-xs font-medium
                          text-slate-warm-600
                          hover:bg-parchment-100
                          dark:text-slate-warm-300
                          dark:hover:bg-slate-warm-800
                          transition">

                    Batal

                </a>

                <button
                    type="submit"
                    class="inline-flex items-center gap-2
                           px-4 py-2
                           rounded-lg
                           bg-bronze-600
                           hover:bg-bronze-700
                           text-white
                           text-xs font-semibold
                           transition">

                    <svg class="w-4 h-4"
                         fill="none"
                         stroke="currentColor"
                         viewBox="0 0 24 24">

                        <path stroke-linecap="round"
                              stroke-linejoin="round"
                              stroke-width="2"
                              d="M5 13l4 4L19 7"/>

                    </svg>

                    Simpan Kata Sandi

                </button>

            </div>

        </form>

    </div>

</div>

@endsection