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
        Schema::create('program_tag', function (Blueprint $t) {
            $t->id();
            $t->foreignId('program_id')->constrained('programs')->cascadeOnDelete();
            $t->foreignId('tag_id')->constrained('tags')->cascadeOnDelete();
            $t->unique(['program_id','tag_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('program_tags');
    }
};
