@extends('layouts.app')

@section('content')
<div x-data="{
    loadingTemplate: null,
    kopInstansi: 'PT NUSANTARA CITRA MEDIA TBBK',
    kopAlamat: 'Gedung Menara Palma Lt. 18, Jl. H.R. Rasuna Said Blok X-2, Jakarta Selatan 12950',
    kopKontrak: 'Telp: (021) 5290-8888 | Email: sekretariat@ncm-media.co.id | Web: www.ncm-media.co.id',
    nomorSurat: '',
    perihalSurat: '',
    sifatSurat: 'Biasa',
    logoUrl: null,
    isUploadingLogo: false,
    bodyHtml: '',

    async loadTemplate(key) {
        this.loadingTemplate = key;
        try {
            const res = await window.axios.get('/documents/template/' + key);
            const t = res.data;
            this.kopInstansi = t.header_data.kopInstansi;
            this.kopAlamat = t.header_data.kopAlamat;
            this.kopKontrak = t.header_data.kopKontrak;
            this.nomorSurat = t.header_data.nomorSurat;
            this.perihalSurat = t.header_data.perihalSurat;
            this.sifatSurat = t.header_data.sifatSurat;

            const b = t.body_content;
            this.bodyHtml = '<p>' + b.tujuanSurat + '</p>'
                + '<p><strong>MENIMBANG:</strong><br>' + b.menimbang.replace(/\n/g, '<br>') + '</p>'
                + '<p><strong>MENGINGAT:</strong><br>' + b.mengingat.replace(/\n/g, '<br>') + '</p>'
                + '<p>' + b.isiPasal1.replace(/\n/g, '<br>') + '</p>'
                + '<p>' + b.isiPasal2.replace(/\n/g, '<br>') + '</p>';
        } catch (e) {
            alert('Gagal memuat template.');
            console.error(e);
        } finally {
            this.loadingTemplate = null;
        }
    },

    uploadLogo(event) {
        const file = event.target.files[0];
        if (!file) return;
        this.isUploadingLogo = true;
        const reader = new FileReader();
        reader.onload = async (e) => {
            try {
                const res = await window.axios.post('/documents/logo', { image: e.target.result });
                this.logoUrl = res.data.url;
            } catch (err) {
                alert('Gagal mengunggah logo.');
                console.error(err);
            } finally {
                this.isUploadingLogo = false;
            }
        };
        reader.readAsDataURL(file);
    }
}" class="max-w-6xl mx-auto py-8">

    <div class="mb-6">
        <h1 class="text-2xl font-bold text-ink-900 dark:text-parchment-50">Buat Dokumen Baru</h1>
        <p class="mt-2 text-sm text-slate-warm-600 dark:text-parchment-400">
            Isi informasi dokumen terlebih dahulu, atau mulai cepat dari template. Setelah dibuat, Anda dapat menulis isi dokumen seperti di Microsoft Word.
        </p>
    </div>

    {{-- Quick Templates --}}
    <div class="mb-6 rounded-2xl border border-parchment-300 bg-parchment-50 p-5 dark:border-slate-warm-700 dark:bg-slate-warm-800">
        <h2 class="text-sm font-semibold text-ink-900 dark:text-parchment-50 mb-3">Mulai Cepat dari Template</h2>
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
            <button type="button" @click="loadTemplate('perjanjian-kerja-sama')" :disabled="loadingTemplate"
                class="rounded-xl border border-parchment-300 bg-white p-3 text-xs font-medium text-ink-900 hover:border-bronze-400 transition-colors dark:bg-slate-warm-900 dark:border-slate-warm-700 dark:text-parchment-100">
                <span x-text="loadingTemplate === 'perjanjian-kerja-sama' ? 'Memuat...' : 'Perjanjian / PKS'"></span>
            </button>
            <button type="button" @click="loadTemplate('kontrak-kerja')" :disabled="loadingTemplate"
                class="rounded-xl border border-parchment-300 bg-white p-3 text-xs font-medium text-ink-900 hover:border-bronze-400 transition-colors dark:bg-slate-warm-900 dark:border-slate-warm-700 dark:text-parchment-100">
                <span x-text="loadingTemplate === 'kontrak-kerja' ? 'Memuat...' : 'Kontrak Kerja'"></span>
            </button>
            <button type="button" @click="loadTemplate('surat-kuasa')" :disabled="loadingTemplate"
                class="rounded-xl border border-parchment-300 bg-white p-3 text-xs font-medium text-ink-900 hover:border-bronze-400 transition-colors dark:bg-slate-warm-900 dark:border-slate-warm-700 dark:text-parchment-100">
                <span x-text="loadingTemplate === 'surat-kuasa' ? 'Memuat...' : 'Surat Kuasa'"></span>
            </button>
            <button type="button" @click="loadTemplate('surat-pernyataan')" :disabled="loadingTemplate"
                class="rounded-xl border border-parchment-300 bg-white p-3 text-xs font-medium text-ink-900 hover:border-bronze-400 transition-colors dark:bg-slate-warm-900 dark:border-slate-warm-700 dark:text-parchment-100">
                <span x-text="loadingTemplate === 'surat-pernyataan' ? 'Memuat...' : 'Surat Pernyataan'"></span>
            </button>
        </div>
    </div>

    <form action="{{ route('documents.store') }}" method="POST">
        @csrf
        <input type="hidden" name="header_data[logoUrl]" :value="logoUrl">
        <input type="hidden" name="body_html" :value="bodyHtml">

        <div class="grid grid-cols-1 lg:grid-cols-5 gap-6">

            {{-- LEFT: Form --}}
            <div class="lg:col-span-3 space-y-5">

                <div class="rounded-2xl border border-parchment-300 bg-white p-6 shadow-sm dark:border-slate-warm-700 dark:bg-slate-warm-900">
                    <h2 class="text-sm font-semibold text-ink-900 dark:text-parchment-50 mb-4">Logo Perusahaan <span class="text-slate-warm-400 font-normal">(opsional)</span></h2>

                    <div class="flex items-center gap-4">
                        <div class="h-16 w-16 rounded-full border border-parchment-300 dark:border-slate-warm-700 flex items-center justify-center overflow-hidden bg-parchment-25 dark:bg-slate-warm-800 shrink-0">
                            <img x-show="logoUrl" :src="logoUrl" class="h-full w-full object-cover" alt="Logo">
                            <span x-show="!logoUrl" class="text-[10px] text-slate-warm-400 text-center">No<br>Logo</span>
                        </div>
                        <div class="flex flex-col gap-1.5">
                            <button type="button" @click="$refs.logoInput.click()" :disabled="isUploadingLogo"
                                class="rounded-lg border border-parchment-300 px-3 py-1.5 text-xs font-medium hover:bg-parchment-100 dark:border-slate-warm-700 dark:hover:bg-slate-warm-800">
                                <span x-text="isUploadingLogo ? 'Mengunggah...' : (logoUrl ? 'Ganti Logo' : 'Unggah Logo')"></span>
                            </button>
                            <button type="button" x-show="logoUrl" @click="logoUrl = null" class="text-xs text-error-600 hover:underline text-left">
                                Hapus Logo
                            </button>
                        </div>
                        <input type="file" x-ref="logoInput" accept="image/png,image/jpeg,image/svg+xml" class="hidden" @change="uploadLogo($event)">
                    </div>
                </div>

                <div class="rounded-2xl border border-parchment-300 bg-white p-6 shadow-sm dark:border-slate-warm-700 dark:bg-slate-warm-900 space-y-5">
                    <h2 class="text-sm font-semibold text-ink-900 dark:text-parchment-50">Identitas Instansi</h2>

                    <div>
                        <label class="block text-xs font-medium mb-1.5">Nama Perusahaan</label>
                        <input type="text" name="header_data[kopInstansi]" x-model="kopInstansi" required
                            class="w-full rounded-xl border border-parchment-300 bg-white px-4 py-2.5 text-sm outline-none focus:border-bronze-500 dark:border-slate-warm-700 dark:bg-slate-warm-800">
                    </div>

                    <div>
                        <label class="block text-xs font-medium mb-1.5">Alamat</label>
                        <textarea name="header_data[kopAlamat]" x-model="kopAlamat" rows="2" required
                            class="w-full rounded-xl border border-parchment-300 bg-white px-4 py-2.5 text-sm outline-none focus:border-bronze-500 dark:border-slate-warm-700 dark:bg-slate-warm-800"></textarea>
                    </div>

                    <div>
                        <label class="block text-xs font-medium mb-1.5">Informasi Kontak</label>
                        <input type="text" name="header_data[kopKontrak]" x-model="kopKontrak"
                            class="w-full rounded-xl border border-parchment-300 bg-white px-4 py-2.5 text-sm outline-none focus:border-bronze-500 dark:border-slate-warm-700 dark:bg-slate-warm-800">
                    </div>
                </div>

                <div class="rounded-2xl border border-parchment-300 bg-white p-6 shadow-sm dark:border-slate-warm-700 dark:bg-slate-warm-900 space-y-5">
                    <h2 class="text-sm font-semibold text-ink-900 dark:text-parchment-50">Detail Dokumen</h2>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium mb-1.5">Nomor Dokumen</label>
                            <input type="text" name="header_data[nomorSurat]" x-model="nomorSurat" required placeholder="001/SK-DIR/VIII/2026"
                                class="w-full rounded-xl border border-parchment-300 bg-white px-4 py-2.5 text-sm outline-none focus:border-bronze-500 dark:border-slate-warm-700 dark:bg-slate-warm-800">
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1.5">Tanggal Dokumen</label>
                            <input type="date" name="header_data[tanggalSurat]" value="{{ now()->format('Y-m-d') }}" required
                                class="w-full rounded-xl border border-parchment-300 bg-white px-4 py-2.5 text-sm outline-none focus:border-bronze-500 dark:border-slate-warm-700 dark:bg-slate-warm-800">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-medium mb-1.5">Perihal Dokumen</label>
                        <input type="text" name="header_data[perihalSurat]" x-model="perihalSurat" required placeholder="Contoh: Surat Keputusan"
                            class="w-full rounded-xl border border-parchment-300 bg-white px-4 py-2.5 text-sm outline-none focus:border-bronze-500 dark:border-slate-warm-700 dark:bg-slate-warm-800">
                    </div>

                    <div>
                        <label class="block text-xs font-medium mb-1.5">Sifat Dokumen</label>
                        <select name="header_data[sifatSurat]" x-model="sifatSurat"
                            class="w-full rounded-xl border border-parchment-300 bg-white px-4 py-2.5 text-sm outline-none focus:border-bronze-500 dark:border-slate-warm-700 dark:bg-slate-warm-800">
                            <option value="Biasa">Biasa</option>
                            <option value="Penting">Penting</option>
                            <option value="Rahasia">Rahasia</option>
                            <option value="Penting / Rahasia">Penting / Rahasia</option>
                        </select>
                    </div>
                </div>

                <div class="flex justify-end">
                    <button type="submit"
                        class="inline-flex items-center gap-2 rounded-xl bg-ink-900 px-6 py-3 text-sm font-semibold text-white transition hover:opacity-90 dark:bg-bronze-500 dark:text-ink-900">
                        Buat Dokumen <span>→</span>
                    </button>
                </div>
            </div>

            {{-- RIGHT: Live Preview --}}
            <div class="lg:col-span-2">
                <div class="sticky top-6 rounded-2xl border border-parchment-300 bg-white p-6 shadow-sm dark:border-slate-warm-700 dark:bg-slate-warm-900">
                    <p class="text-xs font-mono text-slate-warm-500 mb-4">PRATINJAU KOP SURAT</p>

                    <div class="text-center border-b-2 border-ink-900 pb-3">
                        <div class="mx-auto mb-2 h-12 w-12 rounded-full bg-ink-900 flex items-center justify-center overflow-hidden">
                            <img x-show="logoUrl" :src="logoUrl" class="h-full w-full object-cover">
                            <span x-show="!logoUrl" class="text-parchment-100 font-serif font-bold text-sm"
                                x-text="kopInstansi ? kopInstansi.trim().substring(0,2).toUpperCase() : '—'"></span>
                        </div>
                        <p class="font-serif font-bold text-sm uppercase text-ink-900" x-text="kopInstansi"></p>
                        <p class="text-[10px] text-slate-warm-600 mt-1" x-text="kopAlamat"></p>
                        <p class="text-[9px] font-mono text-slate-warm-500 mt-0.5" x-text="kopKontrak"></p>
                    </div>

                    <div class="mt-4 text-xs space-y-1">
                        <p><span class="text-slate-warm-500">Nomor:</span> <span x-text="nomorSurat || '—'"></span></p>
                        <p><span class="text-slate-warm-500">Tanggal:</span> <span x-text="new Date().toLocaleDateString('id-ID')"></span></p>
                        <p><span class="text-slate-warm-500">Perihal:</span> <span x-text="perihalSurat || '—'"></span></p>
                        <p><span class="text-slate-warm-500">Sifat:</span> <span x-text="sifatSurat"></span></p>
                    </div>
                </div>
            </div>

        </div>
    </form>

</div>
@endsection