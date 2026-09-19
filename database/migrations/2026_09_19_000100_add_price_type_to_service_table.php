<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tambah tipe harga service: one_time (sekali bayar) / monthly (bulanan).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('service', function (Blueprint $table) {
            $table->enum('price_type', ['one_time', 'monthly'])
                ->default('one_time')
                ->after('price');
        });
    }

    public function down(): void
    {
        Schema::table('service', function (Blueprint $table) {
            $table->dropColumn('price_type');
        });
    }
};
