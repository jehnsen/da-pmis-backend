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
            $table->string('file_path', 500);
            $table->string('document_type')->default('PDF'); // PDF, DOC, DOCX, XLS, XLSX, etc.
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
