<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('affected_populations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('incident_id')->constrained('incidents')->cascadeOnDelete();
            $table->unsignedInteger('students_affected')->default(0);
            $table->unsignedInteger('teachers_affected')->default(0);
            $table->unsignedInteger('staff_affected')->default(0);
            $table->unsignedInteger('injured')->default(0);
            $table->unsignedInteger('missing')->default(0);
            $table->unsignedInteger('deceased')->default(0);
            $table->unsignedInteger('evacuees')->default(0);
            $table->timestamps();

            $table->index('incident_id');
        });
    }
    public function down(): void {
        Schema::dropIfExists('affected_populations');
    }
};