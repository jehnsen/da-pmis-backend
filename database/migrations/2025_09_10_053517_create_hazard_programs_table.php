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
         Schema::create('hazard_program', function (Blueprint $t) {
            $t->id();
            $t->foreignId('program_id')->constrained('programs')->cascadeOnDelete();
            $t->foreignId('hazard_id')->constrained('hazards')->cascadeOnDelete();
            $t->unique(['program_id','hazard_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hazard_programs');
    }
};
