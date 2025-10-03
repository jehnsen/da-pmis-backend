<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('report_metrics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('progress_report_id')->constrained('progress_reports')->cascadeOnDelete();
            $table->string('metric_name');
            $table->decimal('metric_value', 15, 2)->nullable();
            $table->decimal('previous_value', 15, 2)->nullable();
            $table->timestamps();

            $table->index('progress_report_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('report_metrics');
    }
};
