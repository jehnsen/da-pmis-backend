<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('progress_report_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('progress_report_id')->constrained()->cascadeOnDelete();
            $table->string('file_name');
            $table->string('file_path');
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('file_size')->nullable(); // in bytes
            $table->string('caption')->nullable();
            $table->enum('image_type', ['before', 'after', 'during', 'result', 'documentation', 'other'])->default('documentation');
            $table->unsignedInteger('display_order')->default(0);
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('progress_report_id');
            $table->index('image_type');
            $table->index('display_order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('progress_report_images');
    }
};
