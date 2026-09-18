<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Samakan kosakata status dokumen dengan alur kontrak pelanggan:
 *   draft → on_progress → on_review → disetujui  (+ revisi, archived)
 *
 * Pemetaan data lama:
 *   pending           -> on_review
 *   review_marketing  -> on_review
 *   signed            -> disetujui
 *   revisi            -> revisi (tetap)
 *   draft/archived    -> tetap
 *
 * Langkah 1 memperluas enum agar semua nilai hidup berdampingan, langkah 3
 * mempersempitnya kembali ke daftar final.
 */
return new class extends Migration
{
    public function up(): void
    {
        // 1) Perluas enum: semua nilai lama & baru boleh berdampingan.
        Schema::table('documents', function (Blueprint $table) {
            $table->enum('status', [
                'draft',
                'on_progress',
                'on_review',
                'revisi',
                'disetujui',
                'archived',
                'pending',
                'signed',
                'review_marketing',
            ])->default('draft')->change();
        });

        // 2) Petakan status lama ke kosakata baru.
        DB::table('documents')->whereIn('status', ['pending', 'review_marketing'])
            ->update(['status' => 'on_review']);
        DB::table('documents')->where('status', 'signed')
            ->update(['status' => 'disetujui']);

        // 3) Kunci enum ke daftar final.
        Schema::table('documents', function (Blueprint $table) {
            $table->enum('status', [
                'draft',
                'on_progress',
                'on_review',
                'revisi',
                'disetujui',
                'archived',
            ])->default('draft')->change();
        });
    }

    public function down(): void
    {
        // Perluas lagi supaya nilai baru & lama berdampingan saat dibalik.
        Schema::table('documents', function (Blueprint $table) {
            $table->enum('status', [
                'draft',
                'on_progress',
                'on_review',
                'revisi',
                'disetujui',
                'archived',
                'pending',
                'signed',
            ])->default('draft')->change();
        });

        DB::table('documents')->where('status', 'on_review')->update(['status' => 'pending']);
        DB::table('documents')->where('status', 'disetujui')->update(['status' => 'signed']);
        DB::table('documents')->where('status', 'on_progress')->update(['status' => 'draft']);

        Schema::table('documents', function (Blueprint $table) {
            $table->enum('status', ['draft', 'pending', 'signed', 'archived'])
                ->default('draft')->change();
        });
    }
};
