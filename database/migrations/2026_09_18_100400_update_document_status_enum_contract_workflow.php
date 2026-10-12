<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/*
 */
return new class extends Migration
{
    public function up(): void
    {
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

        DB::table('documents')->whereIn('status', ['pending', 'review_marketing'])
            ->update(['status' => 'on_review']);
        DB::table('documents')->where('status', 'signed')
            ->update(['status' => 'disetujui']);

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
