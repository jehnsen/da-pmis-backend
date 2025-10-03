<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_category_pivot', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')->constrained('documents')->cascadeOnDelete();
            $table->foreignId('document_category_id')->constrained('document_categories')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['document_id', 'document_category_id'], 'doc_cat_unique');
            $table->index('document_id');
            $table->index('document_category_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_category_pivot');
    }
};
