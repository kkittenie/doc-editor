@extends('layouts.fullscreen-layout')

@section('content')
<div class="min-h-screen flex items-center justify-center p-6 bg-parchment-100 dark:bg-slate-warm-950 relative overflow-hidden">

    <!-- Subtle background texture -->
    <div class="absolute inset-0 pointer-events-none opacity-[0.03]" style="background-image: url(&quot;data:image/svg+xml,%3Csvg viewBox='0 0 256 256' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)'/%3E%3C/svg%3E&quot;);"></div>

    <div class="w-full max-w-[400px] relative z-10">

        <!-- Brand Mark -->
        <div class="text-center mb-10">
            <div class="inline-flex items-center justify-center h-12 w-12 rounded-2xl bg-ink-900 text-parchment-100 font-serif font-bold text-lg shadow-lg dark:bg-parchment-100 dark:text-ink-900 mx-auto mb-4">
                P
            </div>
            <h1 class="font-serif font-bold text-2xl text-ink-900 dark:text-parchment-50 tracking-tight">
                Buat Akun Papercraft
            </h1>
            <p class="text-sm text-slate-warm-500 dark:text-parchment-400 mt-1.5">
                Mulai susun dokumen resmi & tanda tangan digital.
            </p>
        </div>

        <!-- Register Form Card -->
        <div class="rounded-2xl border border-parchment-300 bg-white p-8 shadow-paper dark:bg-slate-warm-900 dark:border-slate-warm-800">
            @if ($errors->any())
                <div class="mb-5 p-3 rounded-lg border border-seal-200 bg-seal-50 text-seal-800 text-xs font-medium dark:bg-seal-950 dark:border-seal-800 dark:text-seal-200">
                    <ul class="list-disc list-inside space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('do-register') }}" method="POST" class="space-y-4">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label for="signup-fname" class="block text-xs font-semibold text-ink-900 dark:text-parchment-200 mb-1.5">Nama Depan</label>
                        <input id="signup-fname" name="first_name" type="text" required value="{{ old('first_name') }}" placeholder="Aris" autocomplete="given-name"
                            class="w-full text-sm rounded-lg border border-parchment-300 bg-parchment-50 p-3 text-ink-900 placeholder:text-slate-warm-400 focus:border-ink-900 focus:ring-2 focus:ring-ink-900/10 focus:outline-none transition-colors dark:border-slate-warm-700 dark:bg-slate-warm-800 dark:text-parchment-100 dark:placeholder:text-slate-warm-500 dark:focus:border-parchment-300" />
                    </div>
                    <div>
                        <label for="signup-lname" class="block text-xs font-semibold text-ink-900 dark:text-parchment-200 mb-1.5">Nama Belakang</label>
                        <input id="signup-lname" name="last_name" type="text" value="{{ old('last_name') }}" placeholder="Budiman" autocomplete="family-name"
                            class="w-full text-sm rounded-lg border border-parchment-300 bg-parchment-50 p-3 text-ink-900 placeholder:text-slate-warm-400 focus:border-ink-900 focus:ring-2 focus:ring-ink-900/10 focus:outline-none transition-colors dark:border-slate-warm-700 dark:bg-slate-warm-800 dark:text-parchment-100 dark:placeholder:text-slate-warm-500 dark:focus:border-parchment-300" />
                    </div>
                </div>

                <div>
                    <label for="signup-username" class="block text-xs font-semibold text-ink-900 dark:text-parchment-200 mb-1.5">Username</label>
                    <input id="signup-username" name="username" type="text" required value="{{ old('username') }}" placeholder="arisbudiman" autocomplete="username"
                        class="w-full text-sm rounded-lg border border-parchment-300 bg-parchment-50 p-3 text-ink-900 placeholder:text-slate-warm-400 focus:border-ink-900 focus:ring-2 focus:ring-ink-900/10 focus:outline-none transition-colors dark:border-slate-warm-700 dark:bg-slate-warm-800 dark:text-parchment-100 dark:placeholder:text-slate-warm-500 dark:focus:border-parchment-300" />
                </div>

                <div>
                    <label for="signup-email" class="block text-xs font-semibold text-ink-900 dark:text-parchment-200 mb-1.5">Email</label>
                    <input id="signup-email" name="email" type="email" required value="{{ old('email') }}" placeholder="nama@perusahaan.co.id" autocomplete="email"
                        class="w-full text-sm rounded-lg border border-parchment-300 bg-parchment-50 p-3 text-ink-900 placeholder:text-slate-warm-400 focus:border-ink-900 focus:ring-2 focus:ring-ink-900/10 focus:outline-none transition-colors dark:border-slate-warm-700 dark:bg-slate-warm-800 dark:text-parchment-100 dark:placeholder:text-slate-warm-500 dark:focus:border-parchment-300" />
                </div>

                <div>
                    <label for="signup-password" class="block text-xs font-semibold text-ink-900 dark:text-parchment-200 mb-1.5">Kata Sandi</label>
                    <div x-data="{ show: false }" class="relative">
                        <input id="signup-password" name="password" :type="show ? 'text' : 'password'" required placeholder="Minimal 8 karakter" autocomplete="new-password"
                            class="w-full text-sm rounded-lg border border-parchment-300 bg-parchment-50 p-3 pr-10 text-ink-900 placeholder:text-slate-warm-400 focus:border-ink-900 focus:ring-2 focus:ring-ink-900/10 focus:outline-none transition-colors dark:border-slate-warm-700 dark:bg-slate-warm-800 dark:text-parchment-100 dark:placeholder:text-slate-warm-500 dark:focus:border-parchment-300" />
                        <button type="button" @click="show = !show" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-warm-400 hover:text-ink-700 dark:hover:text-parchment-300 transition-colors" aria-label="Toggle password visibility">
                            <svg x-show="!show" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                            <svg x-show="show" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                        </button>
                    </div>
                </div>

                <div class="flex items-start gap-2.5 pt-1">
                    <input type="checkbox" required id="terms" checked class="mt-0.5 rounded border-parchment-400 text-ink-900 focus:ring-ink-900/20 dark:border-slate-warm-600 dark:bg-slate-warm-700" />
                    <label for="terms" class="text-xs text-slate-warm-600 dark:text-parchment-300 leading-relaxed select-none">
                        Saya setuju dengan <a href="#" class="underline hover:text-ink-900 dark:hover:text-parchment-100">Ketentuan Layanan</a> dan <a href="#" class="underline hover:text-ink-900 dark:hover:text-parchment-100">Kebijakan Privasi</a>.
                    </label>
                </div>

                <button type="submit" class="w-full rounded-lg bg-ink-900 text-parchment-50 py-3 text-sm font-semibold shadow-md hover:bg-ink-800 active:scale-[0.98] transition-all dark:bg-parchment-100 dark:text-ink-900 dark:hover:bg-parchment-200 mt-1">
                    Buat Akun
                </button>
            </form>
        </div>

        <!-- Footer -->
        <p class="text-center text-xs text-slate-warm-500 dark:text-parchment-400 mt-6">
            Sudah punya akun?
            <a href="/signin" class="font-semibold text-ink-900 hover:underline dark:text-parchment-200">Masuk</a>
        </p>

        <!-- Theme Toggle (bottom-right, minimal) -->
        <div class="fixed bottom-6 right-6 z-50">
            <button @click="$store.theme.toggle()" class="h-10 w-10 rounded-full border border-parchment-300 bg-white text-slate-warm-500 shadow-theme-sm flex items-center justify-center hover:bg-parchment-100 transition-colors dark:border-slate-warm-700 dark:bg-slate-warm-800 dark:text-parchment-300 dark:hover:bg-slate-warm-700" aria-label="Ganti tema">
                <svg class="dark:hidden" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>
                <svg class="hidden dark:block" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/></svg>
            </button>
        </div>
    </div>
</div>
@endsection
