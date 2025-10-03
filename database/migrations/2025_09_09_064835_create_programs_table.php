<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
   public function up(): void
   {
      Schema::table('programs', function (Blueprint $t) {
         // From “Program Information”
         $t->string('program_type')->nullable()->after('title');        // dropdown
         $t->string('priority_level')->nullable()->after('program_type'); // e.g., High/Med/Low

         // Use FK if you already have divisions table
         if (Schema::hasTable('divisions')) {
            $t->foreignId('division_id')->nullable()->after('priority_level')
               ->constrained('divisions')->nullOnDelete();
         } else {
            $t->string('division')->nullable()->after('priority_level');
         }

         // From “Timeline & Budget”
         $t->date('start_date')->nullable()->change();
         $t->date('end_date')->nullable()->change();
         $t->decimal('budget_php', 14, 2)->default(0)->change(); // Total Budget
         $t->string('funding_source')->nullable()->after('budget_php');
         $t->boolean('is_qrf_funded')->default(false)->after('funding_source');

         // From “Program Details”
         $t->string('program_coordinator')->nullable()->after('owner_office');
         $t->unsignedInteger('expected_participants')->default(0)->after('program_coordinator');

         // Program objectives (UI supports multiple) – store as JSON array
         $t->json('objectives')->nullable()->after('description');
      });
   }

   public function down(): void
   {
      Schema::table('programs', function (Blueprint $t) {
         if (Schema::hasColumn('programs', 'division_id'))
            $t->dropConstrainedForeignId('division_id');
         $t->dropColumn([
            'program_type',
            'priority_level',
            'division',
            'funding_source',
            'is_qrf_funded',
            'program_coordinator',
            'expected_participants',
            'objectives'
         ]);
      });
   }
};
