@extends('layouts.app')

@section('content')

<div class="max-w-4xl mx-auto space-y-6">

    {{-- Header --}}
    <div>
        <h1 class="font-serif text-2xl font-bold text-ink-900 dark:text-parchment-100">
            Edit Profil
        </h1>

        <p class="mt-1 text-sm text-slate-warm-500 dark:text-slate-warm-400">
            Perbarui informasi akun dan identitas profil Anda.
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


    {{-- Profile Card --}}
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

                {{-- Avatar --}}
                @php
                    $words = explode(' ', trim($user->name));

                    $initials = strtoupper(
                        substr($words[0] ?? 'P', 0, 1) .
                        (isset($words[1]) ? substr($words[1], 0, 1) : '')
                    );
                @endphp

                <div class="w-14 h-14 shrink-0
                            rounded-full
                            bg-bronze-100
                            dark:bg-bronze-900/30
                            flex items-center justify-center">

                    <span class="font-serif font-bold
                                 text-lg
                                 text-bronze-700
                                 dark:text-bronze-400">

                        {{ $initials }}

                    </span>
                </div>


                <div>

                    <h2 class="font-serif font-bold
                               text-base
                               text-ink-900
                               dark:text-parchment-100">

                        Informasi Profil

                    </h2>

                    <p class="text-xs
                              text-slate-warm-500
                              dark:text-slate-warm-400">

                        Informasi ini digunakan untuk identitas akun Anda.

                    </p>

                </div>

            </div>

        </div>


        {{-- Form --}}
        <form action="{{ route('profile.update') }}"
              method="POST">

            @csrf
            @method('PUT')

            <div class="p-6 space-y-5">

                {{-- Nama --}}
                <div>

                    <label for="name"
                           class="block mb-2
                                  text-xs font-semibold
                                  text-ink-800
                                  dark:text-parchment-200">

                        Nama Lengkap

                    </label>

                    <input
                        type="text"
                        id="name"
                        name="name"
                        value="{{ old('name', $user->name) }}"
                        required
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


                {{-- Username --}}
                <div>

                    <label for="username"
                           class="block mb-2
                                  text-xs font-semibold
                                  text-ink-800
                                  dark:text-parchment-200">

                        Username

                    </label>

                    <input
                        type="text"
                        id="username"
                        name="username"
                        value="{{ old('username', $user->username) }}"
                        required
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


                {{-- Email --}}
                <div>

                    <label for="email"
                           class="block mb-2
                                  text-xs font-semibold
                                  text-ink-800
                                  dark:text-parchment-200">

                        Email

                    </label>

                    <input
                        type="email"
                        id="email"
                        name="email"
                        value="{{ old('email', $user->email) }}"
                        required
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

                {{-- Password --}}
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


                    {{-- Konfirmasi Password --}}
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

                        <p class="mt-1.5 text-[11px]
                                text-slate-warm-400">
                            Kosongkan kedua kolom password jika tidak ingin mengubah kata sandi.
                        </p>

                    </div>
                    



            {{-- Footer --}}
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

                    Simpan Perubahan

                </button>

            </div>

        </form>

    </div>


    {{-- Keamanan Akun --}}
    <div class="rounded-xl
                border border-parchment-300
                bg-white
                shadow-theme-sm
                dark:border-slate-warm-800
                dark:bg-slate-warm-900">

        <div class="p-6">

            <div class="flex items-start gap-4">

                <div class="w-10 h-10 shrink-0
                            rounded-lg
                            bg-parchment-100
                            dark:bg-slate-warm-800
                            flex items-center justify-center">

                    <svg class="w-5 h-5
                                text-slate-warm-600
                                dark:text-slate-warm-300"
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
                               text-sm
                               text-ink-900
                               dark:text-parchment-100">

                        Keamanan Akun

                    </h2>

                    <p class="mt-1 text-xs
                              text-slate-warm-500
                              dark:text-slate-warm-400">

                        Pengaturan password akan kita tambahkan di sini.

                    </p>

                </div>

            </div>

        </div>

    </div>

</div>

@endsection
 
