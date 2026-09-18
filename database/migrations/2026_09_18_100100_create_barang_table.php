<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tabel Barang (nullable).
 *
 * Satu pelanggan boleh punya banyak barang. Kolom document_id disiapkan
 * (opsional) supaya data barang bisa dihubungkan ke dokumen/kontrak yang
 * dibuat di Studio Editor tanpa perlu migrasi tambahan nanti.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('barang', function (Blueprint $table) {
            $table->id();

            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->foreignId('document_id')
                ->nullable()
                ->constrained('documents')
                ->nullOnDelete();

            $table->string('name', 150);
            $table->unsignedInteger('quantity')->default(1);
            $table->decimal('price', 15, 2)->default(0);

            $table->timestamps();

            $table->index('customer_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('barang');
    }
};
