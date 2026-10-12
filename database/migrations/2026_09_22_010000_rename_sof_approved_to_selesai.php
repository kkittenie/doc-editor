<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sofs', function (Blueprint $table) {
            $table->enum('status', ['draft', 'approved', 'selesai'])->default('draft')->change();
        });

        DB::table('sofs')->where('status', 'approved')->update(['status' => 'selesai']);

        Schema::table('sofs', function (Blueprint $table) {
            $table->enum('status', ['draft', 'selesai'])->default('draft')->change();
        });
    }

    public function down(): void
    {
        Schema::table('sofs', function (Blueprint $table) {
            $table->enum('status', ['draft', 'selesai', 'approved'])->default('draft')->change();
        });

        DB::table('sofs')->where('status', 'selesai')->update(['status' => 'approved']);

        Schema::table('sofs', function (Blueprint $table) {
            $table->enum('status', ['draft', 'approved'])->default('draft')->change();
        });
    }
};