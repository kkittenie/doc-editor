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
        Schema::create('customers', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            $table->string('customer_number', 50);
            $table->string('name', 150);
            $table->string('contract_number', 100);

            $table->string('contract_name', 255)->nullable();
            $table->date('active_date')->nullable();
            $table->unsignedSmallInteger('active_months')->nullable();
            $table->date('finish_date')->nullable();

            $table->enum('status', [
                'draft',
                'on_progress',
                'on_review',
                'revisi',
                'disetujui',
            ])->default('draft');

            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
