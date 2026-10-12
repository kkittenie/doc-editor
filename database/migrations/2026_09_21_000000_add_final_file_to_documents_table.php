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
        Schema::table('documents', function (Blueprint $table) {
            $table->string('final_file_path')->nullable()->after('pdf_path');

            $table->string('final_file_name')->nullable()->after('final_file_path');

            $table->timestamp('final_file_uploaded_at')->nullable()->after('final_file_name');
        });
    }

    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->dropColumn(['final_file_path', 'final_file_name', 'final_file_uploaded_at']);
        });
    }
};
