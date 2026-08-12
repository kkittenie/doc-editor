@extends('layouts.app')

@section('content')
<div x-data="{
    activeTab: 'draw',
    signerName: 'Drs. H. Aris Budiman, M.B.A.',
    signerRole: 'Direktur Utama',
    signerNip: '19780412 200312 1 002',
    certificateId: 'BSRE-TTE-2026-981412A',
    certStatus: 'Aktif Hingga 2028',

    isDrawing: false,
    hasDrawn: false,
    isSaving: false,
    signatures: @js($signatures),

    initCanvas() {
        const canvas = document.getElementById('signatureCanvas');
        if (!canvas) return;
        const ctx = canvas.getContext('2d');
        ctx.strokeStyle = '#1B2A4A';
        ctx.lineWidth = 2.5;
        ctx.lineCap = 'round';

        let drawing = false;

        const getPos = (e) => {
            const rect = canvas.getBoundingClientRect();
            return {
                x: (e.clientX || e.touches[0].clientX) - rect.left,
                y: (e.clientY || e.touches[0].clientY) - rect.top
            };
        };

        const start = (e) => {
            drawing = true;
            this.hasDrawn = true;
            const pos = getPos(e);
            ctx.beginPath();
            ctx.moveTo(pos.x, pos.y);
        };

        const move = (e) => {
            if (!drawing) return;
            const pos = getPos(e);
            ctx.lineTo(pos.x, pos.y);
            ctx.stroke();
        };

        const stop = () => { drawing = false; };

        canvas.addEventListener('mousedown', start);
        canvas.addEventListener('mousemove', move);
        canvas.addEventListener('mouseup', stop);
        canvas.addEventListener('touchstart', start);
        canvas.addEventListener('touchmove', move);
        canvas.addEventListener('touchend', stop);
    },

    clearCanvas() {
        const canvas = document.getElementById('signatureCanvas');
        if (!canvas) return;
        const ctx = canvas.getContext('2d');
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        this.hasDrawn = false;
    },

    async saveSignature(type, dataUrl) {
        this.isSaving = true;
        try {
            const res = await window.axios.post('/signatures', {
                name: 'Tanda Tangan ' + new Date().toLocaleDateString('id-ID'),
                type: type,
                image: dataUrl,
            });
            this.signatures.unshift(res.data.signature);
            this.activeTab = 'saved';
            if (type === 'draw') this.clearCanvas();
        } catch (e) {
            Swal.fire({ icon: 'error', title: 'Gagal', text: 'Gagal menyimpan tanda tangan.', confirmButtonColor: '#1B2A4A' });
            console.error(e);
        } finally {
            this.isSaving = false;
        }
    },

    saveDrawnSignature() {
        if (!this.hasDrawn) {
            Swal.fire({ icon: 'warning', title: 'Belum ada goresan', text: 'Silakan gores tanda tangan terlebih dahulu.', confirmButtonColor: '#1B2A4A' });
            return;
        }
        const canvas = document.getElementById('signatureCanvas');
        this.saveSignature('draw', canvas.toDataURL('image/png'));
    },

    handleFileUpload(event) {
        const file = event.target.files[0];
        if (!file) return;
        const reader = new FileReader();
        reader.onload = (e) => this.saveSignature('upload', e.target.result);
        reader.readAsDataURL(file);
    },

    async deleteSignature(id) {
    const result = await Swal.fire({
        icon: 'warning',
        title: 'Hapus tanda tangan ini?',
        showCancelButton: true,
        confirmButtonText: 'Ya, hapus',
        cancelButtonText: 'Batal',
        confirmButtonColor: '#dc2626',
    });
    if (!result.isConfirmed) return;
    await window.axios.delete('/signatures/' + id);
    this.signatures = this.signatures.filter(s => s.id !== id);
}
}" x-init="$nextTick(() => initCanvas())">

    <!-- Page Header -->
    <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="font-serif font-bold text-xl text-ink-900 dark:text-parchment-50">
                Studio Tanda Tangan & Sertifikat e-Sign
            </h1>
            <p class="text-xs text-slate-warm-500 dark:text-parchment-400 mt-1">
                Kelola coretan tanda tangan resmi, stempel basah instansi, dan verifikasi sertifikat BSRE.
            </p>
        </div>
        <div class="flex items-center gap-2">
            <span class="doc-status doc-status-verified text-xs">Sertifikat TTE BSRE Aktif</span>
        </div>
    </div>

    <!-- Main Grid: Left Profile Card & Right Signature Studio Pad -->
    <div class="grid grid-cols-12 gap-6">

        <!-- Profile & Certificate Info (4 / 12) -->
        <div class="col-span-12 lg:col-span-4 space-y-5">
            <div
                class="rounded-xl border border-parchment-300 bg-white p-5 shadow-theme-sm dark:border-slate-warm-800 dark:bg-slate-warm-900 text-center">
                <div
                    class="mx-auto mb-3 flex h-20 w-20 items-center justify-center rounded-full bg-ink-900 text-parchment-100 font-serif font-bold text-2xl shadow-md border-4 border-parchment-100 dark:border-slate-warm-800">
                    AB
                </div>
                <h3 class="font-serif font-bold text-base text-ink-900 dark:text-parchment-100" x-text="signerName">
                </h3>
                <p class="text-xs text-bronze-700 dark:text-bronze-400 font-medium" x-text="signerRole"></p>
                <p class="text-[11px] font-mono text-slate-warm-400 mt-1" x-text="signerNip"></p>

                <div
                    class="mt-4 pt-4 border-t border-parchment-200 dark:border-slate-warm-800 text-left space-y-2 text-xs">
                    <div class="flex items-center justify-between">
                        <span class="text-slate-warm-500">ID Sertifikat:</span>
                        <span class="font-mono font-semibold text-ink-900 dark:text-parchment-200"
                            x-text="certificateId"></span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-slate-warm-500">Otoritas Penerbit:</span>
                        <span class="font-semibold text-ink-900 dark:text-parchment-200">BSRE / BSSN RI</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-slate-warm-500">Masa Kuasa TTD:</span>
                        <span class="font-mono text-success-600 font-medium" x-text="certStatus"></span>
                    </div>
                </div>
            </div>

            <div
                class="rounded-xl border border-parchment-300 bg-parchment-100 p-4 dark:border-slate-warm-800 dark:bg-slate-warm-800 space-y-3">
                <h4 class="font-mono text-xs font-bold uppercase text-ink-900 dark:text-parchment-100 tracking-wider">
                    Aset Stempel Tersedia</h4>

                <div
                    class="flex items-center justify-between p-2.5 rounded-lg bg-white border border-parchment-200 dark:bg-slate-warm-900 dark:border-slate-warm-700 text-xs">
                    <div class="flex items-center gap-2">
                        <div
                            class="w-7 h-7 rounded-full border border-seal-700 flex items-center justify-center text-[8px] font-bold text-seal-700">
                            PT</div>
                        <div>
                            <span class="font-semibold block text-ink-900 dark:text-parchment-100">Stempel Basah
                                Utama</span>
                            <span class="text-[10px] text-slate-warm-500">Format PNG Transparan</span>
                        </div>
                    </div>
                    <span class="text-success-600 font-bold text-xs">✓ Ready</span>
                </div>

                <div
                    class="flex items-center justify-between p-2.5 rounded-lg bg-white border border-parchment-200 dark:bg-slate-warm-900 dark:border-slate-warm-700 text-xs">
                    <div class="flex items-center gap-2">
                        <div
                            class="w-7 h-7 rounded bg-bronze-100 flex items-center justify-center text-[8px] font-bold text-bronze-800">
                            10k</div>
                        <div>
                            <span class="font-semibold block text-ink-900 dark:text-parchment-100">e-Materai
                                Peruri</span>
                            <span class="text-[10px] text-slate-warm-500">Saldo: 24 Kuota</span>
                        </div>
                    </div>
                    <span class="text-bronze-700 font-bold text-xs">Aktif</span>
                </div>
            </div>
        </div>

        <!-- Signature Studio Drawing Pad (8 / 12) -->
        <div class="col-span-12 lg:col-span-8 space-y-4">

            <div
                class="flex flex-wrap sm:flex-nowrap rounded-xl border border-parchment-300 bg-white p-1 shadow-theme-xs dark:border-slate-warm-800 dark:bg-slate-warm-900 gap-1">
                <button @click="activeTab = 'draw'; $nextTick(() => initCanvas())"
                    :class="activeTab === 'draw' ? 'bg-ink-900 text-white font-semibold' : 'text-slate-warm-600 hover:text-ink-900 dark:text-parchment-400'"
                    class="flex-1 py-2 text-[11px] sm:text-xs rounded-lg transition-all min-w-[120px]">
                    ✍️ Gores Tanda Tangan
                </button>
                <button @click="activeTab = 'upload'"
                    :class="activeTab === 'upload' ? 'bg-ink-900 text-white font-semibold' : 'text-slate-warm-600 hover:text-ink-900 dark:text-parchment-400'"
                    class="flex-1 py-2 text-[11px] sm:text-xs rounded-lg transition-all min-w-[120px]">
                    📁 Upload File
                </button>
                <button @click="activeTab = 'saved'"
                    :class="activeTab === 'saved' ? 'bg-ink-900 text-white font-semibold' : 'text-slate-warm-600 hover:text-ink-900 dark:text-parchment-400'"
                    class="flex-1 py-2 text-[11px] sm:text-xs rounded-lg transition-all min-w-[120px]">
                    🎨 Koleksi TTD
                </button>
            </div>

            <!-- TAB 1: DRAW CANVAS -->
            <div x-show="activeTab === 'draw'" x-transition
                class="rounded-xl border border-parchment-300 bg-white p-5 shadow-theme-sm dark:border-slate-warm-800 dark:bg-slate-warm-900 space-y-4">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <div>
                        <h3 class="font-serif font-bold text-base text-ink-900 dark:text-parchment-100">Canvas Gores
                            Tanda Tangan Tinta</h3>
                        <p class="text-xs text-slate-warm-500">Goreskan tanda tangan Anda menggunakan kursor mouse atau
                            layar sentuh.</p>
                    </div>
                    <button @click="clearCanvas()" class="btn-ghost text-xs text-error-600 hover:bg-error-50">
                        Bersihkan Canvas
                    </button>
                </div>

                <div
                    class="signature-pad-container h-[220px] w-full flex items-center justify-center overflow-hidden relative">
                    <canvas id="signatureCanvas" width="550" height="200" class="max-w-full"></canvas>
                    <div x-show="!hasDrawn"
                        class="absolute pointer-events-none text-center text-slate-warm-400 text-xs px-2">
                        <span class="block font-serif italic mb-1">Goreskan tanda tangan di sini...</span>
                        <span class="text-[10px] font-mono">Sensitivitas Tinta TTE Aktif</span>
                    </div>
                </div>

                <div class="flex flex-wrap items-center justify-between gap-3 pt-2">
                    <div class="flex items-center gap-2">
                        <span class="w-3 h-3 rounded-full bg-ink-900 border border-parchment-300"></span>
                        <span class="text-xs text-slate-warm-600">Tinta Biru Tua (Deep Ink Navy)</span>
                    </div>
                    <button @click="saveDrawnSignature()" :disabled="isSaving"
                        class="btn-primary text-xs shadow-sm w-full sm:w-auto">
                        <span x-text="isSaving ? 'Menyimpan...' : 'Simpan ke Vault TTD →'"></span>
                    </button>
                </div>
            </div>

            <!-- TAB 2: UPLOAD FILE -->
            <div x-show="activeTab === 'upload'" x-transition
                class="rounded-xl border border-parchment-300 bg-white p-5 shadow-theme-sm dark:border-slate-warm-800 dark:bg-slate-warm-900 space-y-4">
                <h3 class="font-serif font-bold text-base text-ink-900 dark:text-parchment-100">Upload File Hasil Scan /
                    Vector</h3>

                <div @click="$refs.fileInput.click()"
                    class="cursor-pointer border-2 border-dashed border-parchment-300 rounded-xl p-8 text-center bg-parchment-25 dark:bg-slate-warm-800 dark:border-slate-warm-700 hover:border-bronze-400 transition-colors">
                    <svg class="mx-auto text-slate-warm-400 mb-3" width="36" height="36" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="1.5">
                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                        <polyline points="17 8 12 3 7 8" />
                        <line x1="12" y1="3" x2="12" y2="15" />
                    </svg>
                    <p class="text-xs font-semibold text-ink-900 dark:text-parchment-100 mb-1"
                        x-text="isSaving ? 'Mengunggah...' : 'Tarik & Lepas Gambar TTD atau Klik untuk Cari'"></p>
                    <p class="text-[11px] text-slate-warm-500">Mendukung format PNG Transparan, SVG, atau JPG (Max 5MB)
                    </p>
                </div>
                <input type="file" x-ref="fileInput" accept="image/png,image/jpeg,image/svg+xml" class="hidden"
                    @change="handleFileUpload($event)">
            </div>

            <!-- TAB 3: SAVED SIGNATURES -->
            <div x-show="activeTab === 'saved'" x-transition
                class="rounded-xl border border-parchment-300 bg-white p-5 shadow-theme-sm dark:border-slate-warm-800 dark:bg-slate-warm-900 space-y-4">
                <h3 class="font-serif font-bold text-base text-ink-900 dark:text-parchment-100">Koleksi Tanda Tangan
                    Tersimpan</h3>

                <div x-show="signatures.length === 0" class="text-xs text-slate-warm-500 text-center py-8">
                    Belum ada tanda tangan tersimpan. Coba gores atau upload dulu di tab sebelah.
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <template x-for="sig in signatures" :key="sig.id">
                        <div
                            class="border border-parchment-300 rounded-xl p-4 bg-white dark:bg-slate-warm-800 relative">
                            <button @click="deleteSignature(sig.id)"
                                class="absolute top-2 right-2 text-[10px] text-error-600 hover:underline">Hapus</button>
                            <div class="h-20 flex items-center justify-center my-2">
                                <img :src="sig.url" class="max-h-20 max-w-full object-contain" alt="Tanda tangan">
                            </div>
                            <p class="text-xs font-bold text-center text-ink-900 dark:text-parchment-100"
                                x-text="sig.name"></p>
                        </div>
                    </template>
                </div>
            </div>

        </div>

    </div>
</div>
@endsection