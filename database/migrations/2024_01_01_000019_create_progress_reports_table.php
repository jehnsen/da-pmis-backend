<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('progress_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('department_id')->constrained('departments')->cascadeOnDelete();
            $table->enum('report_period', ['monthly', 'quarterly', 'annual'])->default('monthly');
            $table->date('report_date');
            $table->text('summary')->nullable();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->softDeletes();
            $table->timestamps();

            $table->index('department_id');
            $table->index('report_period');
            $table->index('report_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('progress_reports');
    }
};
