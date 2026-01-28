<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->string('file_name')->nullable()->after('file_path');
            $table->string('mime_type')->nullable()->after('file_name');
            $table->unsignedBigInteger('file_size')->nullable()->after('mime_type');
            $table->unsignedInteger('download_count')->default(0)->after('file_size');
            $table->foreignId('category_id')->nullable()->after('download_count')->constrained('document_categories')->nullOnDelete();
            $table->foreignId('department_id')->nullable()->after('category_id')->constrained('departments')->nullOnDelete();
            $table->year('fiscal_year')->nullable()->after('department_id');
            $table->boolean('is_featured')->default(false)->after('fiscal_year');

            $table->index('category_id');
            $table->index('department_id');
            $table->index('fiscal_year');
            $table->index('is_featured');
            $table->index('download_count');
        });
    }

    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
            $table->dropForeign(['department_id']);
            $table->dropIndex(['category_id']);
            $table->dropIndex(['department_id']);
            $table->dropIndex(['fiscal_year']);
            $table->dropIndex(['is_featured']);
            $table->dropIndex(['download_count']);
            $table->dropColumn([
                'file_name',
                'mime_type',
                'file_size',
                'download_count',
                'category_id',
                'department_id',
                'fiscal_year',
                'is_featured',
            ]);
        });
    }
};
