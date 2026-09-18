<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tabel Service (nullable).
 *
 * Satu pelanggan boleh punya banyak service. Sama seperti tabel barang,
 * document_id dipertahankan opsional untuk kebutuhan editor/PDF ke depan.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service', function (Blueprint $table) {
            $table->id();

            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->foreignId('document_id')
                ->nullable()
                ->constrained('documents')
                ->nullOnDelete();

            $table->string('name', 150);
            $table->decimal('price', 15, 2)->default(0);

            $table->timestamps();

            $table->index('customer_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service');
    }
};
