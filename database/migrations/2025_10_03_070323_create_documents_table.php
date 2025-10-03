<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('file_path');
            $table->enum('document_type', ['report', 'whitepaper', 'policy', 'guideline', 'other'])->default('other');
            $table->date('published_date')->nullable();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->softDeletes();
            $table->timestamps();

            $table->index('document_type');
            $table->index('published_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
