<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('evacuation_occupancies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('incident_id')->constrained('incidents')->cascadeOnDelete();
            $table->foreignId('evacuation_center_id')->constrained('evacuation_centers')->cascadeOnDelete();
            $table->unsignedInteger('households')->default(0);
            $table->unsignedInteger('individuals')->default(0);
            $table->timestamp('reported_at')->nullable();
            $table->timestamps();

            $table->index(['incident_id', 'evacuation_center_id']);
        });
    }
    public function down(): void {
        Schema::dropIfExists('evacuation_occupancies');
    }
};