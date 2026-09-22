<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Menu S.O.F — repositori berkas Order Formulir mandiri.
 *
 * Entitas ini TIDAK memiliki relasi ke tabel pelanggan (customers) maupun
 * dokumen (documents): segala data (nomor pelanggan, nomor kontrak, nama,
 * periode, nilai, berkas PDF) diisi langsung di form ini.
 *
 * Alur: buat entri baru → isi data pelanggan & upload berkas PDF → simpan
 * → berkas tersedia di daftar S.O.F untuk diunduh.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sofs', function (Blueprint $table) {
            $table->id();

            // Pemilik data (scoped per user, sama seperti pelanggan & dokumen).
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            // Nomor Order Form (auto-generate), format sama seperti nomor kontrak.
            $table->string('order_number', 100)->unique();

            // Data pelanggan — diisi mandiri di form SOF.
            $table->string('customer_number', 50);
            $table->string('customer_name', 150);

            // Data kontrak.
            $table->string('contract_number', 100)->nullable();
            $table->string('contract_name', 255)->nullable();
            $table->date('active_date')->nullable();
            $table->unsignedSmallInteger('active_months')->nullable();
            $table->date('finish_date')->nullable();

            // Nilai total (one-time + bulanan), dihitung dari barang/service jika ada.
            $table->decimal('total_value', 15, 2)->default(0);

            // Berkas PDF yang discan / di-upload.
            $table->string('file_path')->nullable();
            $table->string('file_name')->nullable();
            $table->timestamp('file_uploaded_at')->nullable();

            // Workflow internal S.O.F.
            $table->enum('status', ['draft', 'approved'])->default('draft');

            // Catatan revision (untuk tracking perubahan).
            $table->text('revision_notes')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sofs');
    }
};
