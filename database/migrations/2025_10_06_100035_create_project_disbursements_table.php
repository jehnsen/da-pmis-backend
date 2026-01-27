<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_disbursements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 15, 2);
            $table->date('disbursement_date');
            $table->string('category'); // equipment, labor, materials, supplies, services, etc
            $table->text('description')->nullable();
            $table->string('reference_number')->nullable();
            $table->string('vendor_name')->nullable();
            $table->string('receipt_number')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->enum('status', ['pending', 'completed', 'cancelled'])->default('pending');
            $table->timestamps();

            $table->index(['project_id', 'disbursement_date']);
            $table->index(['project_id', 'category']);
            $table->index('status');
            $table->index('disbursement_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_disbursements');
    }
};
