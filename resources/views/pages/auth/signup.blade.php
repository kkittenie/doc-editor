@extends('layouts.fullscreen-layout')

@section('content')
<div class="min-h-screen grid lg:grid-cols-2 bg-white dark:bg-slate-warm-950">

    <!-- ═══ LEFT : form daftar (putih, tanpa card) ═══ -->
    <div class="relative flex items-center justify-center px-6 py-12 sm:px-12 overflow-hidden">
        <!-- tekstur grain sangat halus agar putih tidak flat -->
        <div class="absolute inset-0 pointer-events-none opacity-[0.025]"
            style="background-image: url(&quot;data:image/svg+xml,%3Csvg viewBox='0 0 256 256' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)'/%3E%3C/svg%3E&quot;);">
        </div>

        <div class="w-full max-w-[400px] relative z-10">

            <!-- mini brand — hanya tampil di mobile (panel kanan tersembunyi) -->
            <div class="lg:hidden mb-10">
                <p class="text-[11px] font-bold uppercase tracking-[0.28em] text-crimson-700 dark:text-crimson-300">Aplikasi Legal</p>
                <p class="text-xs text-slate-warm-500 dark:text-parchment-400 mt-1">Kelola Dokumen Resmi</p>
            </div>

            <!-- Sapaan form -->
            <div class="mb-9">
                <h1 class="font-bold text-[28px] leading-9 text-ink-900 dark:text-parchment-50 tracking-tight">
                    Buat Akun Baru
                </h1>
                <p class="text-sm text-slate-warm-500 dark:text-parchment-400 mt-1.5">
                    Mulai susun dokumen resmi &amp; tanda tangan digital.
                </p>
            </div>

            <!-- Register Form tanpa card -->
        <div>
            @if ($errors->any())
            <div
                class="mb-6 border-l-2 border-crimson-600 pl-3 text-xs font-medium text-crimson-700 dark:text-crimson-300">
                <ul class="list-disc list-inside space-y-0.5">
                    @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif
            <form action="{{ route('signup') }}" method="POST" class="space-y-7">
                @csrf
                <div class="grid grid-cols-2 gap-5">
                    <div>
                        <label for="signup-fname"
                            class="block text-[13px] font-medium text-slate-warm-500 dark:text-parchment-400 mb-0.5">Nama
                            Depan</label>
                        <input id="signup-fname" name="fname" type="text" required placeholder=""
                            autocomplete="given-name"
                            class="w-full bg-transparent border-0 border-b border-slate-warm-200 dark:border-slate-warm-700 rounded-none px-0 py-2 text-[15px] text-ink-900 dark:text-parchment-100 placeholder:text-slate-warm-300 focus:border-crimson-700 focus:ring-0 focus:outline-none transition-colors" />
                    </div>
                    <div>
                        <label for="signup-lname"
                            class="block text-[13px] font-medium text-slate-warm-500 dark:text-parchment-400 mb-0.5">Nama
                            Belakang</label>
                        <input id="signup-lname" name="lname" type="text" required placeholder=""
                            autocomplete="family-name"
                            class="w-full bg-transparent border-0 border-b border-slate-warm-200 dark:border-slate-warm-700 rounded-none px-0 py-2 text-[15px] text-ink-900 dark:text-parchment-100 placeholder:text-slate-warm-300 focus:border-crimson-700 focus:ring-0 focus:outline-none transition-colors" />
                    </div>
                </div>

                <div>
                    <label for="signup-email"
                        class="block text-[13px] font-medium text-slate-warm-500 dark:text-parchment-400 mb-0.5">Email</label>
                    <input id="signup-email" name="email" type="email" required placeholder=""
                        autocomplete="email"
                        class="w-full bg-transparent border-0 border-b border-slate-warm-200 dark:border-slate-warm-700 rounded-none px-0 py-2 text-[15px] text-ink-900 dark:text-parchment-100 placeholder:text-slate-warm-300 focus:border-crimson-700 focus:ring-0 focus:outline-none transition-colors" />
                </div>

                <div>
                    <label for="signup-password"
                        class="block text-[13px] font-medium text-slate-warm-500 dark:text-parchment-400 mb-0.5">Kata
                        Sandi</label>
                    <div x-data="{ show: false }" class="relative">
                        <input id="signup-password" name="password" :type="show ? 'text' : 'password'" required
                            placeholder="" autocomplete="new-password"
                            class="w-full bg-transparent border-0 border-b border-slate-warm-200 dark:border-slate-warm-700 rounded-none px-0 py-2 pr-10 text-[15px] text-ink-900 dark:text-parchment-100 placeholder:text-slate-warm-300 focus:border-crimson-700 focus:ring-0 focus:outline-none transition-colors" />
                        <button type="button" @click="show = !show"
                            class="absolute right-0 top-1/2 -translate-y-1/2 text-slate-warm-400 hover:text-crimson-700 dark:hover:text-crimson-300 transition-colors"
                            aria-label="Toggle password visibility">
                            <svg x-show="!show" width="18" height="18" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="1.5">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" />
                                <circle cx="12" cy="12" r="3" />
                            </svg>
                            <svg x-show="show" width="18" height="18" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="1.5">
                                <path
                                    d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24" />
                                <line x1="1" y1="1" x2="23" y2="23" />
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="flex items-start gap-2.5 !mt-5">
                    <input type="checkbox" required id="terms" checked
                        class="mt-0.5 rounded border-slate-warm-300 text-crimson-700 focus:ring-crimson-700/20 dark:border-slate-warm-600 dark:bg-slate-warm-700" />
                    <label for="terms"
                        class="text-xs text-slate-warm-600 dark:text-parchment-300 leading-relaxed select-none">
                        Saya setuju dengan <a href="#"
                            class="underline hover:text-crimson-700 dark:hover:text-crimson-300">Ketentuan Layanan</a> dan
                        <a href="#" class="underline hover:text-crimson-700 dark:hover:text-crimson-300">Kebijakan
                            Privasi</a>.
                    </label>
                </div>

                <button type="submit"
                    class="w-full rounded-lg bg-bronze-950 text-white py-3 text-sm font-semibold shadow-lg shadow-bronze-950/30 hover:bg-bronze-900 hover:brightness-90 active:scale-[0.98] transition-all !mt-5">
                    Buat Akun
                </button>
            </form>
        </div>

        <!-- Footer -->
        <p class="text-xs text-slate-warm-500 dark:text-parchment-400 mt-8">
            Sudah punya akun?
            <a href="/signin" class="font-semibold text-crimson-700 hover:text-crimson-600 hover:underline dark:text-crimson-300">Masuk</a>
        </p>
    </div>
    </div>

    <!-- ═══ RIGHT : panel brand merah (partial bersama, ubah di brand-panel.blade.php) ═══ -->
    @include('pages.auth.brand-panel')

    <!-- Theme Toggle (bottom-right, minimal) -->
    <div class="fixed bottom-6 right-6 z-50">
        <button @click="$store.theme.toggle()"
            class="h-10 w-10 rounded-full border border-parchment-300 bg-white text-slate-warm-500 shadow-theme-sm flex items-center justify-center hover:bg-parchment-100 transition-colors dark:border-slate-warm-700 dark:bg-slate-warm-800 dark:text-parchment-300 dark:hover:bg-slate-warm-700"
            aria-label="Ganti tema">
                <svg class="dark:hidden" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-width="1.5">
                    <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z" />
                </svg>
                <svg class="hidden dark:block" width="18" height="18" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="1.5">
                    <circle cx="12" cy="12" r="5" />
                    <line x1="12" y1="1" x2="12" y2="3" />
                    <line x1="12" y1="21" x2="12" y2="23" />
                    <line x1="4.22" y1="4.22" x2="5.64" y2="5.64" />
                    <line x1="18.36" y1="18.36" x2="19.78" y2="19.78" />
                    <line x1="1" y1="12" x2="3" y2="12" />
                    <line x1="21" y1="12" x2="23" y2="12" />
                    <line x1="4.22" y1="19.78" x2="5.64" y2="18.36" />
                    <line x1="18.36" y1="5.64" x2="19.78" y2="4.22" />
                </svg>
            </button>
        </div>
</div>
@endsection