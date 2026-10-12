<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /*
     */
    public function up(): void
    {
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
        DB::table('documents')->where('status', 'revisi')->update(['status' => 'draft']);
        DB::table('documents')->where('status', 'disetujui')->update(['status' => 'signed']);

        Schema::table('documents', function (Blueprint $table) {
            $table->dropForeign(['marketer_id']);
            $table->dropColumn(['marketer_id', 'revision_notes']);
        });

        Schema::table('documents', function (Blueprint $table) {
            $table->enum('status', ['draft', 'pending', 'signed', 'archived'])
                ->default('draft')->change();
        });
    }

    /**
     * Re-apply the marketer workflow (reverse of up).
     */
    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->enum('status', [
                'draft',
                'review_marketing',
                'revisi',
                'disetujui',
            ])->default('draft')->change();
        });

        DB::table('documents')->where('status', 'pending')->update(['status' => 'review_marketing']);
        DB::table('documents')->where('status', 'signed')->update(['status' => 'disetujui']);
        DB::table('documents')->where('status', 'archived')->update(['status' => 'disetujui']);

        Schema::table('documents', function (Blueprint $table) {
            $table->foreignId('marketer_id')
                ->nullable()
                ->after('user_id')
                ->constrained('users')
                ->nullOnDelete();
            $table->json('revision_notes')->nullable()->after('signature_data');
        });
    }
};