<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Reverse the marketer workflow migrations:
     *   1) Expand the enum so old & new values coexist.
     *   2) Map the marketer-only statuses back to original statuses.
     *   3) Drop marketer_id (FK + column) and revision_notes.
     *   4) Narrow the enum back to the original four statuses.
     *
     * Status mapping:
     *   review_marketing -> pending
     *   revisi           -> draft
     *   disetujui        -> signed
     *   draft            -> draft  (unchanged)
     *   archived         -> archived (unchanged)
     */
    public function up(): void
    {
        // 1) Perluas enum dulu agar semua nilai hidup berdampingan.
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

        // 2) Petakan status marketer ke status asli.
        DB::table('documents')->where('status', 'review_marketing')->update(['status' => 'pending']);
        DB::table('documents')->where('status', 'revisi')->update(['status' => 'draft']);
        DB::table('documents')->where('status', 'disetujui')->update(['status' => 'signed']);

        // 3) Drop kolom yang hanya dipakai workflow marketer.
        Schema::table('documents', function (Blueprint $table) {
            $table->dropForeign(['marketer_id']);
            $table->dropColumn(['marketer_id', 'revision_notes']);
        });

        // 4) Persempit enum kembali ke empat status asli.
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
        // 1) Perluas enum agar nilai asli bisa dipetakan ke workflow baru.
        Schema::table('documents', function (Blueprint $table) {
            $table->enum('status', [
                'draft',
                'review_marketing',
                'revisi',
                'disetujui',
            ])->default('draft')->change();
        });

        // 2) Petakan status asli ke workflow marketer.
        DB::table('documents')->where('status', 'pending')->update(['status' => 'review_marketing']);
        DB::table('documents')->where('status', 'signed')->update(['status' => 'disetujui']);
        DB::table('documents')->where('status', 'archived')->update(['status' => 'disetujui']);

        // 3) Tambah kembali kolom marketer_id dan revision_notes.
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