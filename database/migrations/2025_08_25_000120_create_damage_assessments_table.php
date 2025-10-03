<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('damage_assessments', function (Blueprint $table) {
            // Summary counts
            $table->unsignedInteger('affected_households')->default(0)->after('incident_id');
            $table->unsignedInteger('totally_damaged')->default(0)->after('affected_households');
            $table->unsignedInteger('partially_damaged')->default(0)->after('totally_damaged');
            $table->unsignedInteger('injuries')->default(0)->after('partially_damaged');
            $table->unsignedInteger('deaths')->default(0)->after('injuries');
            $table->unsignedInteger('missing')->default(0)->after('deaths');
            $table->unsignedInteger('displaced_families')->default(0)->after('missing');

            // Financial (keep legacy estimated_cost; add new loss amount)
            $table->decimal('estimated_loss_amount', 16, 2)->default(0)->after('estimated_cost');

            // Workflow/meta
            $table->string('status', 20)->default('draft')->after('estimated_loss_amount'); // draft|submitted|verified|approved
            $table->foreignId('assessed_by_user_id')->nullable()->after('status')->constrained('users')->nullOnDelete();
            $table->timestamp('assessed_at')->nullable()->after('assessed_by_user_id');

            // existing columns to keep:
            // classrooms_damaged_minor, classrooms_damaged_major, estimated_cost, notes, timestamps...
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('damage_assessments');
    }
};
