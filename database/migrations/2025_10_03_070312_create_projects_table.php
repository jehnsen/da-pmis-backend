<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->foreignId('department_id')->constrained('departments')->cascadeOnDelete();
            $table->foreignId('project_type_id')->constrained('project_types')->cascadeOnDelete();
            $table->foreignId('project_status_id')->constrained('project_statuses')->cascadeOnDelete();
            $table->decimal('budget', 15, 2)->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->decimal('location_lat', 10, 7)->nullable();
            $table->decimal('location_lng', 10, 7)->nullable();
            $table->boolean('is_public')->default(true);
            $table->softDeletes();
            $table->timestamps();

            $table->index('department_id');
            $table->index('project_type_id');
            $table->index('project_status_id');
            $table->index('is_public');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
