<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tabel Pelanggan.
 *
 * Satu baris = satu pelanggan + satu kontrak (sesuai alur "Dokumen Saya"):
 * Nomer Pelanggan & Nomor Kontrak diisi di halaman Dokumen Saya, sedangkan
 * Nama Kontrak / Tanggal Aktif / Masa Aktif / Tanggal Selesai baru terisi
 * setelah user menyelesaikan form di Studio Editor.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();

            // Pemilik data (dokumen/pelanggan selalu dibatasi per user).
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            // Form di halaman Dokumen Saya.
            $table->string('customer_number', 50);
            $table->string('name', 150);
            $table->string('contract_number', 100);

            // Diisi dari Studio Editor (kosong saat pelanggan baru dibuat).
            $table->string('contract_name', 255)->nullable();
            $table->date('active_date')->nullable();
            $table->unsignedSmallInteger('active_months')->nullable();
            $table->date('finish_date')->nullable();

            // Status alur kerja kontrak (default Draft).
            $table->enum('status', [
                'draft',
                'on_progress',
                'on_review',
                'revisi',
                'disetujui',
            ])->default('draft');

            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
