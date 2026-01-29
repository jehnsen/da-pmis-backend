<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // First, remove any duplicate records (keep only the earliest one)
        DB::statement("
            DELETE pa1 FROM project_approvals pa1
            INNER JOIN project_approvals pa2
            WHERE pa1.id > pa2.id
            AND pa1.project_id = pa2.project_id
            AND pa1.level = pa2.level
            AND pa1.action = pa2.action
        ");

        Schema::table('project_approvals', function (Blueprint $table) {
            // Add unique constraint to prevent duplicate approvals at the same level and time
            // This prevents race conditions where two officers approve simultaneously
            // Includes action_taken_at to allow resubmissions after changes are requested
            $table->unique(['project_id', 'level', 'action', 'action_taken_at'], 'unique_project_level_action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('project_approvals', function (Blueprint $table) {
            $table->dropUnique('unique_project_level_action');
        });
    }
};
