<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('damage_assessments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('incident_id')->constrained('incidents')->cascadeOnDelete();

            // Summary counts
            $table->unsignedInteger('affected_households')->default(0);
            $table->unsignedInteger('totally_damaged')->default(0);
            $table->unsignedInteger('partially_damaged')->default(0);
            $table->unsignedInteger('injuries')->default(0);
            $table->unsignedInteger('deaths')->default(0);
            $table->unsignedInteger('missing')->default(0);
            $table->unsignedInteger('displaced_families')->default(0);

            // Infrastructure damage
            $table->unsignedInteger('classrooms_damaged_minor')->default(0);
            $table->unsignedInteger('classrooms_damaged_major')->default(0);

            // Financial
            $table->decimal('estimated_cost', 16, 2)->default(0);
            $table->decimal('estimated_loss_amount', 16, 2)->default(0);

            // Workflow/meta
            $table->string('status', 20)->default('draft'); // draft|submitted|verified|approved
            $table->foreignId('assessed_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('assessed_at')->nullable();

            // Additional notes
            $table->text('notes')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('damage_assessments');
    }
};
