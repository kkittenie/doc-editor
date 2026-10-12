<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('barang', function (Blueprint $table) {
            $table->enum('price_type', ['one_time', 'monthly'])
                ->default('one_time')
                ->after('price');
            $table->enum('ownership', ['disewa', 'dipinjamkan', 'dibeli'])
                ->default('dibeli')
                ->after('price_type');
        });
    }

    public function down(): void
    {
        Schema::table('barang', function (Blueprint $table) {
            $table->dropColumn(['price_type', 'ownership']);
        });
    }
};
