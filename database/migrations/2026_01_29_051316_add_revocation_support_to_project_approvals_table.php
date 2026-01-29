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
        Schema::table('project_approvals', function (Blueprint $table) {
            // Track if this approval has been revoked
            $table->boolean('is_revoked')->default(false)->after('action_taken_at');
            $table->foreignId('revoked_by')->nullable()->constrained('users')->nullOnDelete()->after('is_revoked');
            $table->timestamp('revoked_at')->nullable()->after('revoked_by');
            $table->text('revocation_reason')->nullable()->after('revoked_at');

            // Index for querying active (non-revoked) approvals
            $table->index('is_revoked');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('project_approvals', function (Blueprint $table) {
            $table->dropIndex(['is_revoked']);
            $table->dropForeign(['revoked_by']);
            $table->dropColumn(['is_revoked', 'revoked_by', 'revoked_at', 'revocation_reason']);
        });
    }
};
