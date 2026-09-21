<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Catat tanggal terakhir kontrak (pelanggan) menyentuh tahap On Progress.
 *
 * Kolom on_progress_at diisi setiap kali status kontrak (pelanggan) naik
 * menjadi on_progress — dipakai Tabel Pelanggan sebagai "Terakhir On Progress".
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->timestamp('on_progress_at')->nullable()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn('on_progress_at');
        });
    }
};
