<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Samakan kosakata status S.O.F dengan istilah operasional "Selesai":
 *   approved -> selesai
 *
 * Langkah 1 memperluas enum agar nilai lama & baru boleh berdampingan,
 * langkah 3 mempersempitnya kembali ke daftar final (draft, selesai).
 */
return new class extends Migration
{
    public function up(): void
    {
        // 1) Perluas enum: 'approved' & 'selesai' berdampingan.
        Schema::table('sofs', function (Blueprint $table) {
            $table->enum('status', ['draft', 'approved', 'selesai'])->default('draft')->change();
        });

        // 2) Petakan status lama ke kosakata baru.
        DB::table('sofs')->where('status', 'approved')->update(['status' => 'selesai']);

        // 3) Kunci enum ke daftar final.
        Schema::table('sofs', function (Blueprint $table) {
            $table->enum('status', ['draft', 'selesai'])->default('draft')->change();
        });
    }

    public function down(): void
    {
        // 1) Perluas enum dulu supaya nilai 'approved' bisa dipakai lagi.
        Schema::table('sofs', function (Blueprint $table) {
            $table->enum('status', ['draft', 'selesai', 'approved'])->default('draft')->change();
        });

        DB::table('sofs')->where('status', 'selesai')->update(['status' => 'approved']);

        Schema::table('sofs', function (Blueprint $table) {
            $table->enum('status', ['draft', 'approved'])->default('draft')->change();
        });
    }
};