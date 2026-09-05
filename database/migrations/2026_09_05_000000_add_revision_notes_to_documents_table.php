<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Kolom riwayat "Alasan Revisi" yang ditulis marketing ketika
     * menolak dokumen (review_marketing → revisi).
     */
    public function up(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->json('revision_notes')->nullable()->after('signature_data');
        });
    }

    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->dropColumn('revision_notes');
        });
    }
};
