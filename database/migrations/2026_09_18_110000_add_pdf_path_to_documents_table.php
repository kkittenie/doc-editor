<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Workflow Tahap 2 (Ekstensi): simpan berkas PDF S.O.F hasil approval.
 *
 * Alur: dokumen `on_review` → admin klik "Setujui" → sistem merender PDF
 * (header + identitas kontrak + tabel barang/service + canvas editor + footer)
 * → file disimpan di disk publik → path-nya dicatat di sini bersama waktu
 * generate, sehingga Menu S.O.F bisa mengunduh berkas yang sudah final
 * tanpa perlu render ulang setiap kali.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            // Path relatif pada disk 'public' (mis. sof/12/SOF-KTR-001-IX-2026.pdf).
            $table->string('pdf_path')->nullable()->after('signature_data');

            // Kapan PDF terakhir kali dihasilkan (untuk fallback regenerate).
            $table->timestamp('pdf_generated_at')->nullable()->after('pdf_path');
        });
    }

    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->dropColumn(['pdf_path', 'pdf_generated_at']);
        });
    }
};
