@extends('layouts.app')

@section('content')

<div x-data="{
        loadingTemplate: null,
        selectedTemplate: '',
        bodyHtml: '',
        headerHtml: '<p></p>',
        footerHtml: '<p></p>',

        async loadTemplate(key) {
            this.loadingTemplate = key;
            this.selectedTemplate = key;

            try {
                const res = await window.axios.get('/documents/template/' + key);
                const t = res.data;

                if (t.title) {
                    const titleInput = document.getElementById('header-judul');
                    if (titleInput) titleInput.value = t.title;
                }

                if (t.header_data?.nomorSurat) {
                    const nomorInput = document.getElementById('header-nomor');
                    if (nomorInput) nomorInput.value = t.header_data.nomorSurat;
                }

                if (t.body_html) {
                    this.bodyHtml = t.body_html;
                }

                // Isi otomatis section HEADER & FOOTER dari template.
                // Template ber-cover mengirim HTML jadi (header_content =
                // ikon pihak pertama, footer_content = identitas pihak +
                // paraf + stample/materai); sisanya dirangkai dari data kop.
                if (t.header_content) {
                    this.headerHtml = t.header_content;
                } else if (t.header_data) {
                    this.headerHtml = this.buildHeaderHtml(t.header_data);
                }
                if (t.footer_content) {
                    this.footerHtml = t.footer_content;
                } else if (t.footer_data) {
                    this.footerHtml = this.buildFooterHtml(t.footer_data);
                }

                Swal.fire({
                    icon: 'success',
                    title: 'Template dimuat',
                    text: 'Judul, nomor, ikon, isi, & footer dokumen sudah diisi otomatis.',
                    confirmButtonColor: '#1B2A4A',
                    timer: 1200,
                    showConfirmButton: false,
                });

            } catch (e) {
                console.error(e);
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: 'Gagal memuat template.',
                    confirmButtonColor: '#1B2A4A',
                });
            } finally {
                this.loadingTemplate = null;
            }
        },

        // Susun HTML untuk section HEADER (kop surat) dari data header template.
        buildHeaderHtml(hd) {
            if (!hd) return '<p></p>';

            const parts = [];

            if (hd.kopInstansi) {
                parts.push('<p style=&quot;text-align:center&quot;><strong>' + hd.kopInstansi + '</strong></p>');
            }
            if (hd.kopAlamat) {
                parts.push('<p style=&quot;text-align:center&quot;>' + hd.kopAlamat + '</p>');
            }
            if (hd.kopKontrak) {
                parts.push('<p style=&quot;text-align:center&quot;>' + hd.kopKontrak + '</p>');
            }
            if (hd.perihalSurat) {
                parts.push('<p style=&quot;text-align:center&quot;><em>' + hd.perihalSurat + '</em></p>');
            }

            return parts.length ? parts.join('') : '<p></p>';
        },

        // Susun HTML untuk section FOOTER dari data footer template.
        buildFooterHtml(fd) {
            if (!fd) return '<p></p>';

            const parts = [];

            if (fd.kotaTtd) {
                parts.push('<p style=&quot;text-align:center&quot;>' + fd.kotaTtd + '</p>');
            }
            if (fd.jabatanPenandatangan) {
                parts.push('<p style=&quot;text-align:center&quot;><strong>' + fd.jabatanPenandatangan + '</strong></p>');
            }
            if (fd.namaPenandatangan) {
                parts.push('<p style=&quot;text-align:center&quot;><strong><u>' + fd.namaPenandatangan + '</u></strong></p>');
            }
            if (fd.nipPenandatangan) {
                parts.push('<p style=&quot;text-align:center&quot;>' + fd.nipPenandatangan + '</p>');
            }
            if (fd.tembusan) {
                parts.push('<p><strong>TEMBUSAN:</strong><br>' + fd.tembusan.replace(/\n/g, '<br>') + '</p>');
            }

            return parts.length ? parts.join('') : '<p></p>';
        },
    }" class="max-w-4xl mx-auto py-8">

    {{-- TITLE --}}

    <div class="mb-6">
        <h1 class="text-2xl font-bold text-ink-900 dark:text-parchment-50">
            Buat Dokumen Baru
        </h1>
        <p class="mt-2 text-sm text-slate-warm-600 dark:text-parchment-400">
            Isi judul & nomor dokumen, lalu pilih template. Kop surat, isi, dan footer
            ditulis bebas nanti di halaman editor.
        </p>
    </div>

    <form action="{{ route('documents.store') }}" method="POST">

        @csrf

        {{-- Kop surat & footer otomatis diisi dari template terpilih lewat
        headerHtml / footerHtml (kalau tanpa template, tetap kosong). --}}
        <input type="hidden" name="header_data[content]" :value="headerHtml">
        <input type="hidden" name="footer_data[content]" :value="footerHtml">
        <input type="hidden" name="template" x-model="selectedTemplate">
        <input type="hidden" name="body_html" x-model="bodyHtml">

        {{-- JUDUL & NOMOR --}}

        <div
            class="rounded-2xl border border-parchment-300 bg-white p-5 shadow-sm dark:border-slate-warm-700 dark:bg-slate-warm-900 mb-6">

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

                <div>
                    <label class="block text-xs font-medium mb-1.5">Judul Dokumen</label>
                    <input type="text" id="header-judul" name="title" required
                        placeholder="Contoh: Surat Keputusan Direksi"
                        class="w-full rounded-xl border border-parchment-300 bg-white px-4 py-2.5 text-sm outline-none focus:border-bronze-500 dark:border-slate-warm-700 dark:bg-slate-warm-800">
                </div>

                <div>
                    <label class="block text-xs font-medium mb-1.5">Nomor Dokumen</label>
                    <input type="text" id="header-nomor" name="header_data[nomorSurat]" required
                        placeholder="001/SK-DIR/VIII/2026"
                        class="w-full rounded-xl border border-parchment-300 bg-white px-4 py-2.5 text-sm outline-none focus:border-bronze-500 dark:border-slate-warm-700 dark:bg-slate-warm-800">
                </div>

            </div>

        </div>

        {{-- 4 TEMPLATE --}}

        <div class="mb-6">

            <h2 class="font-serif font-bold text-lg text-ink-900 dark:text-parchment-50 mb-1">
                Pilih Template Dokumen
            </h2>
            <p class="text-xs text-slate-warm-500 dark:text-parchment-400 mb-4">
                Klik template untuk mengisi judul & nomor otomatis, lalu tekan tombol simpan.
            </p>

            <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">

                {{-- TEMPLATE 5: KONTRAK KEMITRAAN --}}
                <div
                    class="template-card flex flex-col justify-between p-5 border border-parchment-300 bg-white rounded-2xl shadow-sm dark:border-slate-warm-700 dark:bg-slate-warm-900">
                    <div>
                        <div class="flex items-center justify-between mb-3">
                            <span
                                class="px-2.5 py-0.5 rounded text-[10px] font-mono font-semibold bg-bronze-100 text-bronze-800 dark:bg-bronze-900 dark:text-bronze-300">
                                Kontrak Kemitraan
                            </span>
                            <span class="text-[10px] font-mono text-slate-warm-400">TPL-KM-05</span>
                        </div>

                        <div
                            class="template-card-preview rounded p-4 mb-4 flex flex-col justify-center items-center shadow-xs min-h-[120px] bg-parchment-50 dark:bg-slate-warm-800">
                            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="1.5" class="text-parchment-300 mb-2">
                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
                                <path d="M14 2v6h6" />
                            </svg>
                            <span class="text-[10px] text-slate-warm-400">Kontrak Kemitraan</span>
                        </div>

                        <h3 class="font-serif font-bold text-base text-ink-900 dark:text-parchment-100 mb-2">
                            Kontrak Kemitraan
                        </h3>
                        <p class="text-xs text-slate-warm-500 dark:text-parchment-400 mb-4 leading-relaxed">
                            Perjanjian kerjasama jual kembali jasa layanan akses internet.
                        </p>
                    </div>

                    <button type="button" @click="loadTemplate('kontrak-kemitraan')" :disabled="loadingTemplate"
                        class="btn-primary w-full text-xs text-center py-2.5">
                        <span x-text="loadingTemplate === 'kontrak-kemitraan' ? 'Memuat...' : 'Gunakan Template Ini →'"></span>
                    </button>
                </div>

                {{-- TEMPLATE 6: JASA COLOCATION --}}
                <div
                    class="template-card flex flex-col justify-between p-5 border border-parchment-300 bg-white rounded-2xl shadow-sm dark:border-slate-warm-700 dark:bg-slate-warm-900">
                    <div>
                        <div class="flex items-center justify-between mb-3">
                            <span
                                class="px-2.5 py-0.5 rounded text-[10px] font-mono font-semibold bg-bronze-100 text-bronze-800 dark:bg-bronze-900 dark:text-bronze-300">
                                Jasa Colocation
                            </span>
                            <span class="text-[10px] font-mono text-slate-warm-400">TPL-CO-06</span>
                        </div>

                        <div
                            class="template-card-preview rounded p-4 mb-4 flex flex-col justify-center items-center shadow-xs min-h-[120px] bg-parchment-50 dark:bg-slate-warm-800">
                            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="1.5" class="text-parchment-300 mb-2">
                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
                                <path d="M14 2v6h6" />
                            </svg>
                            <span class="text-[10px] text-slate-warm-400">Jasa Colocation</span>
                        </div>

                        <h3 class="font-serif font-bold text-base text-ink-900 dark:text-parchment-100 mb-2">
                            Jasa Colocation
                        </h3>
                        <p class="text-xs text-slate-warm-500 dark:text-parchment-400 mb-4 leading-relaxed">
                            Perjanjian berlangganan jasa colocation data center.
                        </p>
                    </div>

                    <button type="button" @click="loadTemplate('kontrak-colocation')" :disabled="loadingTemplate"
                        class="btn-primary w-full text-xs text-center py-2.5">
                        <span x-text="loadingTemplate === 'kontrak-colocation' ? 'Memuat...' : 'Gunakan Template Ini →'"></span>
                    </button>
                </div>

                {{-- TEMPLATE 7: MANAGED SERVICE --}}
                <div
                    class="template-card flex flex-col justify-between p-5 border border-parchment-300 bg-white rounded-2xl shadow-sm dark:border-slate-warm-700 dark:bg-slate-warm-900">
                    <div>
                        <div class="flex items-center justify-between mb-3">
                            <span
                                class="px-2.5 py-0.5 rounded text-[10px] font-mono font-semibold bg-bronze-100 text-bronze-800 dark:bg-bronze-900 dark:text-bronze-300">
                                Managed Service
                            </span>
                            <span class="text-[10px] font-mono text-slate-warm-400">TPL-MS-07</span>
                        </div>

                        <div
                            class="template-card-preview rounded p-4 mb-4 flex flex-col justify-center items-center shadow-xs min-h-[120px] bg-parchment-50 dark:bg-slate-warm-800">
                            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="1.5" class="text-parchment-300 mb-2">
                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
                                <path d="M14 2v6h6" />
                            </svg>
                            <span class="text-[10px] text-slate-warm-400">Managed Service</span>
                        </div>

                        <h3 class="font-serif font-bold text-base text-ink-900 dark:text-parchment-100 mb-2">
                            Managed Service
                        </h3>
                        <p class="text-xs text-slate-warm-500 dark:text-parchment-400 mb-4 leading-relaxed">
                            Perjanjian berlangganan jasa dedicated / metro / managed service.
                        </p>
                    </div>

                    <button type="button" @click="loadTemplate('kontrak-managed-service')" :disabled="loadingTemplate"
                        class="btn-primary w-full text-xs text-center py-2.5">
                        <span
                            x-text="loadingTemplate === 'kontrak-managed-service' ? 'Memuat...' : 'Gunakan Template Ini →'"></span>
                    </button>
                </div>

                {{-- TEMPLATE 8: JASA SOHO --}}
                <div
                    class="template-card flex flex-col justify-between p-5 border border-parchment-300 bg-white rounded-2xl shadow-sm dark:border-slate-warm-700 dark:bg-slate-warm-900">
                    <div>
                        <div class="flex items-center justify-between mb-3">
                            <span
                                class="px-2.5 py-0.5 rounded text-[10px] font-mono font-semibold bg-bronze-100 text-bronze-800 dark:bg-bronze-900 dark:text-bronze-300">
                                Jasa SOHO
                            </span>
                            <span class="text-[10px] font-mono text-slate-warm-400">TPL-SH-08</span>
                        </div>

                        <div
                            class="template-card-preview rounded p-4 mb-4 flex flex-col justify-center items-center shadow-xs min-h-[120px] bg-parchment-50 dark:bg-slate-warm-800">
                            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="1.5" class="text-parchment-300 mb-2">
                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
                                <path d="M14 2v6h6" />
                            </svg>
                            <span class="text-[10px] text-slate-warm-400">Jasa SOHO</span>
                        </div>

                        <h3 class="font-serif font-bold text-base text-ink-900 dark:text-parchment-100 mb-2">
                            Jasa SOHO
                        </h3>
                        <p class="text-xs text-slate-warm-500 dark:text-parchment-400 mb-4 leading-relaxed">
                            Perjanjian berlangganan jasa SOHO untuk pelanggan.
                        </p>
                    </div>

                    <button type="button" @click="loadTemplate('kontrak-soho')" :disabled="loadingTemplate"
                        class="btn-primary w-full text-xs text-center py-2.5">
                        <span x-text="loadingTemplate === 'kontrak-soho' ? 'Memuat...' : 'Gunakan Template Ini →'"></span>
                    </button>
                </div>

                {{-- TEMPLATE 9: KONTRAK PAYUNG METRO --}}
                <div
                    class="template-card flex flex-col justify-between p-5 border border-parchment-300 bg-white rounded-2xl shadow-sm dark:border-slate-warm-700 dark:bg-slate-warm-900">
                    <div>
                        <div class="flex items-center justify-between mb-3">
                            <span
                                class="px-2.5 py-0.5 rounded text-[10px] font-mono font-semibold bg-bronze-100 text-bronze-800 dark:bg-bronze-900 dark:text-bronze-300">
                                Kontrak Payung Metro
                            </span>
                            <span class="text-[10px] font-mono text-slate-warm-400">TPL-KP-09</span>
                        </div>

                        <div
                            class="template-card-preview rounded p-4 mb-4 flex flex-col justify-center items-center shadow-xs min-h-[120px] bg-parchment-50 dark:bg-slate-warm-800">
                            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="1.5" class="text-parchment-300 mb-2">
                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
                                <path d="M14 2v6h6" />
                            </svg>
                            <span class="text-[10px] text-slate-warm-400">Kontrak Payung Metro</span>
                        </div>

                        <h3 class="font-serif font-bold text-base text-ink-900 dark:text-parchment-100 mb-2">
                            Kontrak Payung Metro
                        </h3>
                        <p class="text-xs text-slate-warm-500 dark:text-parchment-400 mb-4 leading-relaxed">
                            Kontrak payung berlangganan jasa Metro Fiber Optik.
                        </p>
                    </div>

                    <button type="button" @click="loadTemplate('kontrak-payung')" :disabled="loadingTemplate"
                        class="btn-primary w-full text-xs text-center py-2.5">
                        <span
                            x-text="loadingTemplate === 'kontrak-payung' ? 'Memuat...' : 'Gunakan Template Ini →'"></span>
                    </button>
                </div>

            </div>
            {{-- ^ ini penutup grid template card yang kemarin kelewat --}}

        </div>

        {{-- SIMPAN --}}

        <div class="flex justify-end">
            <button type="submit" class="btn-primary px-8 py-3 text-sm">
                Simpan & Lanjut ke Editor →
            </button>
        </div>

    </form>

</div>
@endsection