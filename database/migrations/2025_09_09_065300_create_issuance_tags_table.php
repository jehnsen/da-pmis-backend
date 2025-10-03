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
        Schema::create('issuance_tags', function (Blueprint $table) {
            $table->id();
            $table->foreignId('issuance_id')->constrained('issuances')->cascadeOnDelete();
            $table->foreignId('tag_id')->constrained('tags')->cascadeOnDelete();
            $table->unique(['issuance_id','tag_id']);
        });

        Schema::create('issuance_hazards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('issuance_id')->constrained('issuances')->cascadeOnDelete();
            $table->foreignId('hazard_id')->constrained('hazards')->cascadeOnDelete();
            $table->unique(['issuance_id','hazard_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('issuance_tags');
        Schema::dropIfExists('issuance_hazards');
    }
};
