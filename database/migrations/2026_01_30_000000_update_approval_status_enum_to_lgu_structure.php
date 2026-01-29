<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Updates existing projects table approval_status enum to RA 7160 LGU structure.
     * This migration is for EXISTING databases that need to be updated.
     */
    public function up(): void
    {
        // Step 1: Update existing data - Map old values to new values
        DB::statement("UPDATE projects SET approval_status = 'pending_governor' WHERE approval_status = 'pending_regional'");
        DB::statement("UPDATE project_approvals SET level = 'governor' WHERE level = 'regional'");
        DB::statement("UPDATE project_approvals SET level = 'barangay' WHERE level = 'field'");

        // Update from_status and to_status in project_approvals
        DB::statement("UPDATE project_approvals SET from_status = 'pending_governor' WHERE from_status = 'pending_regional'");
        DB::statement("UPDATE project_approvals SET to_status = 'pending_governor' WHERE to_status = 'pending_regional'");

        // Step 2: Alter the enum columns to include new values
        DB::statement("ALTER TABLE projects MODIFY COLUMN approval_status ENUM(
            'draft',
            'pending_barangay',
            'pending_municipal',
            'pending_provincial',
            'pending_governor',
            'approved',
            'rejected'
        ) DEFAULT 'draft'");

        DB::statement("ALTER TABLE project_approvals MODIFY COLUMN level ENUM(
            'barangay',
            'municipal',
            'provincial',
            'governor'
        )");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert data changes
        DB::statement("UPDATE projects SET approval_status = 'pending_regional' WHERE approval_status = 'pending_governor'");
        DB::statement("UPDATE project_approvals SET level = 'regional' WHERE level = 'governor'");
        DB::statement("UPDATE project_approvals SET level = 'field' WHERE level = 'barangay'");

        DB::statement("UPDATE project_approvals SET from_status = 'pending_regional' WHERE from_status = 'pending_governor'");
        DB::statement("UPDATE project_approvals SET to_status = 'pending_regional' WHERE to_status = 'pending_governor'");

        // Revert enum changes
        DB::statement("ALTER TABLE projects MODIFY COLUMN approval_status ENUM(
            'draft',
            'pending_municipal',
            'pending_provincial',
            'pending_regional',
            'approved',
            'rejected'
        ) DEFAULT 'draft'");

        DB::statement("ALTER TABLE project_approvals MODIFY COLUMN level ENUM(
            'field',
            'municipal',
            'provincial',
            'regional'
        )");
    }
};
