<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
   public function up(): void
   {
      Schema::create('programs', function (Blueprint $t) {
         $t->id();

         // Basic information
         $t->string('title');
         $t->string('program_type')->nullable();
         $t->string('priority_level')->nullable(); // High/Med/Low
         $t->text('description')->nullable();
         $t->json('objectives')->nullable();

         // Division/Department
         $t->foreignId('division_id')->nullable()->constrained('divisions')->nullOnDelete();
         $t->string('division')->nullable(); // fallback if no division_id

         // Timeline & Budget
         $t->date('start_date')->nullable();
         $t->date('end_date')->nullable();
         $t->decimal('budget_php', 14, 2)->default(0);
         $t->string('funding_source')->nullable();
         $t->boolean('is_qrf_funded')->default(false);

         // Program Details
         $t->string('owner_office')->nullable();
         $t->string('program_coordinator')->nullable();
         $t->unsignedInteger('expected_participants')->default(0);

         // Status
         $t->string('status')->default('planning'); // planning|active|completed|cancelled

         $t->timestamps();
      });
   }

   public function down(): void
   {
      Schema::dropIfExists('programs');
   }
};
