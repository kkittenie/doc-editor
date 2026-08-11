@extends('layouts.app')

@section('content')

<div class="max-w-3xl mx-auto py-8">

 
<div class="mb-8">
    <h1 class="text-2xl font-bold text-ink-900 dark:text-parchment-50">
        Buat Dokumen Baru
    </h1>

    <p class="mt-2 text-sm text-slate-warm-600 dark:text-parchment-400">
        Isi informasi dokumen terlebih dahulu. Setelah dibuat, Anda dapat menulis isi dokumen seperti di Microsoft Word.
    </p>
</div>

<form action="{{ route('documents.store') }}" method="POST"
    class="space-y-6">

    @csrf

    {{-- Informasi Dasar --}}
    <div class="rounded-2xl border border-parchment-300 bg-white p-6 shadow-sm dark:border-slate-warm-700 dark:bg-slate-warm-900">

        <h2 class="text-lg font-semibold text-ink-900 dark:text-parchment-50">
            Informasi Dokumen
        </h2>

        <p class="mt-1 text-sm text-slate-warm-500">
            Informasi ini akan digunakan untuk membentuk kop dokumen.
        </p>

        <div class="mt-6 space-y-5">

            {{-- Nama Perusahaan --}}
            <div>
                <label class="block text-sm font-medium mb-2">
                    Nama Perusahaan
                </label>

                <input
                    type="text"
                    name="header_data[kopInstansi]"
                    value="PT NUSANTARA CITRA MEDIA TBBK"
                    required
                    class="w-full rounded-xl border border-parchment-300 bg-white px-4 py-3 text-sm outline-none focus:border-bronze-500 dark:border-slate-warm-700 dark:bg-slate-warm-800"
                    placeholder="Contoh: PT NUSANTARA CITRA MEDIA TBBK"
                >
            </div>

            {{-- Alamat --}}
            <div>
                <label class="block text-sm font-medium mb-2">
                    Alamat
                </label>

                <textarea
                    name="header_data[kopAlamat]"
                    rows="2"
                    required
                    class="w-full rounded-xl border border-parchment-300 bg-white px-4 py-3 text-sm outline-none focus:border-bronze-500 dark:border-slate-warm-700 dark:bg-slate-warm-800"
                    placeholder="Alamat lengkap perusahaan"
                >Gedung Menara Palma Lt. 18, Jl. H.R. Rasuna Said Blok X-2, Jakarta Selatan 12950</textarea>
            </div>

            {{-- Informasi Kontak --}}
            <div>
                <label class="block text-sm font-medium mb-2">
                    Informasi Kontak
                </label>

                <input
                    type="text"
                    name="header_data[kopKontrak]"
                    value="Telp: (021) 5290-8888 | Email: sekretariat@ncm-media.co.id | Web: www.ncm-media.co.id"
                    class="w-full rounded-xl border border-parchment-300 bg-white px-4 py-3 text-sm outline-none focus:border-bronze-500 dark:border-slate-warm-700 dark:bg-slate-warm-800"
                    placeholder="Telepon, email, website, dll."
                >
            </div>

            {{-- Nomor Dokumen --}}
            <div>
                <label class="block text-sm font-medium mb-2">
                    Nomor Dokumen
                </label>

                <input
                    type="text"
                    name="header_data[nomorSurat]"
                    required
                    class="w-full rounded-xl border border-parchment-300 bg-white px-4 py-3 text-sm outline-none focus:border-bronze-500 dark:border-slate-warm-700 dark:bg-slate-warm-800"
                    placeholder="Contoh: 001/SK-DIR/VIII/2026"
                >
            </div>

            {{-- Tanggal --}}
            <div>
                <label class="block text-sm font-medium mb-2">
                    Tanggal Dokumen
                </label>

                <input
                    type="date"
                    name="header_data[tanggalSurat]"
                    value="{{ now()->format('Y-m-d') }}"
                    required
                    class="w-full rounded-xl border border-parchment-300 bg-white px-4 py-3 text-sm outline-none focus:border-bronze-500 dark:border-slate-warm-700 dark:bg-slate-warm-800"
                >
            </div>

            {{-- Perihal --}}
            <div>
                <label class="block text-sm font-medium mb-2">
                    Perihal Dokumen
                </label>

                <input
                    type="text"
                    name="header_data[perihalSurat]"
                    required
                    class="w-full rounded-xl border border-parchment-300 bg-white px-4 py-3 text-sm outline-none focus:border-bronze-500 dark:border-slate-warm-700 dark:bg-slate-warm-800"
                    placeholder="Contoh: Surat Keputusan"
                >
            </div>

            {{-- Sifat --}}
            <div>
                <label class="block text-sm font-medium mb-2">
                    Sifat Dokumen
                </label>

                <select
                    name="header_data[sifatSurat]"
                    class="w-full rounded-xl border border-parchment-300 bg-white px-4 py-3 text-sm outline-none focus:border-bronze-500 dark:border-slate-warm-700 dark:bg-slate-warm-800"
                >
                    <option value="Biasa">Biasa</option>
                    <option value="Penting">Penting</option>
                    <option value="Rahasia">Rahasia</option>
                    <option value="Penting / Rahasia">Penting / Rahasia</option>
                </select>
            </div>

        </div>
    </div>

    {{-- Tombol --}}
    <div class="flex justify-end">

        <button
            type="submit"
            class="inline-flex items-center gap-2 rounded-xl bg-ink-900 px-6 py-3 text-sm font-semibold text-white transition hover:opacity-90 dark:bg-bronze-500 dark:text-ink-900"
        >
            Buat Dokumen
            <span>→</span>
        </button>

    </div>

</form>
 

</div>

@endsection
