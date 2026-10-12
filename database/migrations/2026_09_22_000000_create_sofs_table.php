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
        Schema::create('sofs', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            $table->string('order_number', 100)->unique();

            $table->string('customer_number', 50);
            $table->string('customer_name', 150);

            $table->string('contract_number', 100)->nullable();
            $table->string('contract_name', 255)->nullable();
            $table->date('active_date')->nullable();
            $table->unsignedSmallInteger('active_months')->nullable();
            $table->date('finish_date')->nullable();

            $table->decimal('total_value', 15, 2)->default(0);

            $table->string('file_path')->nullable();
            $table->string('file_name')->nullable();
            $table->timestamp('file_uploaded_at')->nullable();

            $table->enum('status', ['draft', 'approved'])->default('draft');

            $table->text('revision_notes')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sofs');
    }
};
