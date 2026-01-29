<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->enum('approval_status', [
                'draft',
                'pending_barangay',
                'pending_municipal',
                'pending_provincial',
                'pending_governor',
                'approved',
                'rejected',
            ])->default('draft')->after('project_status_id');
            $table->foreignId('submitted_by')->nullable()->constrained('users')->nullOnDelete()->after('approval_status');
            $table->timestamp('submitted_at')->nullable()->after('submitted_by');

            $table->index('approval_status');
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropIndex(['approval_status']);
            $table->dropForeign(['submitted_by']);
            $table->dropColumn(['approval_status', 'submitted_by', 'submitted_at']);
        });
    }
};
