<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Workflow kontrak pelanggan (revisi ke-2): berkas final hasil upload user.
 *
 * Alur: dokumen `on_review` → admin klik "Setujui" → popup upload berkas →
 * berkas disimpan di disk publik → path-nya dicatat di sini → status dokumen
 * & kontrak menjadi `disetujui`.
 *
 * Berbeda dari `pdf_path` (berkas S.O.F hasil render sistem), berkas di sini
 * adalah dokumen final yang menggantikan isi dokumen pada Tabel Pelanggan:
 * "Unduh PDF" mengambil berkas ini, sedangkan Menu S.O.F tetap eksklusif untuk
 * berkas hasil render sistem (lihat SofController).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            // Path relatif pada disk 'public' (mis. documents/12/KTR-001-IX-2026.pdf).
            $table->string('final_file_path')->nullable()->after('pdf_path');

            // Nama asli berkas dari komputer user — dipakai sebagai nama unduhan.
            $table->string('final_file_name')->nullable()->after('final_file_path');

            // Kapan berkas final terakhir di-upload (untuk jejak audit).
            $table->timestamp('final_file_uploaded_at')->nullable()->after('final_file_name');
        });
    }

    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->dropColumn(['final_file_path', 'final_file_name', 'final_file_uploaded_at']);
        });
    }
};
