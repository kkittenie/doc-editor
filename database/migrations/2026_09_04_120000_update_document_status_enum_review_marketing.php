<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Alur kerja status dokumen baru:
     * draft → review_marketing → disetujui | revisi.
     *
     * - draft, revisi          : dilihat & diedit admin
     * - review_marketing       : direview marketer (setujui / minta revisi)
     * - disetujui              : final, dilihat marketer
     */
    public function up(): void
    {
        // 1) Perluas enum dulu agar nilai lama & baru hidup berdampingan.
        Schema::table('documents', function (Blueprint $table) {
            $table->enum('status', [
                'draft',
                'pending',
                'signed',
                'archived',
                'review_marketing',
                'revisi',
                'disetujui',
            ])->default('draft')->change();
        });

        // 2) Petakan data lama ke alur kerja baru.
        DB::table('documents')->where('status', 'pending')->update(['status' => 'review_marketing']);
        DB::table('documents')->where('status', 'signed')->update(['status' => 'disetujui']);
        DB::table('documents')->where('status', 'archived')->update(['status' => 'disetujui']);

        // 3) Persempit enum ke 4 status final.
        Schema::table('documents', function (Blueprint $table) {
            $table->enum('status', [
                'draft',
                'review_marketing',
                'revisi',
                'disetujui',
            ])->default('draft')->change();
        });
    }

    public function down(): void
    {
        // Perluas enum agar nilai baru bisa dipetakan balik.
        Schema::table('documents', function (Blueprint $table) {
            $table->enum('status', [
                'draft',
                'pending',
                'signed',
                'archived',
                'review_marketing',
                'revisi',
                'disetujui',
            ])->default('draft')->change();
        });

        DB::table('documents')->where('status', 'review_marketing')->update(['status' => 'pending']);
        DB::table('documents')->where('status', 'disetujui')->update(['status' => 'signed']);
        // 'revisi' tidak punya padanan lama → kembali jadi draft.
        DB::table('documents')->where('status', 'revisi')->update(['status' => 'draft']);

        Schema::table('documents', function (Blueprint $table) {
            $table->enum('status', ['draft', 'pending', 'signed', 'archived'])
                ->default('draft')->change();
        });
    }
};
