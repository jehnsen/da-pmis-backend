<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
   public function up(): void
   {
      Schema::create('program_milestones', function (Blueprint $t) {
         $t->id();
         $t->foreignId('program_id')->constrained('programs')->cascadeOnDelete();
         $t->string('title');
         $t->date('milestone_date')->nullable();
         $t->text('description')->nullable();
         $t->timestamps();
      });
   }
   public function down(): void
   {
      Schema::dropIfExists('program_milestones');
   }
};
