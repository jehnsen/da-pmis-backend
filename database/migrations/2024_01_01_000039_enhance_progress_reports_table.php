<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('progress_reports', function (Blueprint $table) {
            // Add project_id for project-level tracking (nullable for backward compatibility)
            $table->foreignId('project_id')->nullable()->after('id')->constrained()->cascadeOnDelete();

            // Physical and financial progress percentages
            $table->unsignedTinyInteger('physical_progress')->default(0)->after('report_period'); // 0-100
            $table->unsignedTinyInteger('financial_progress')->default(0)->after('physical_progress'); // 0-100

            // Detailed progress tracking fields
            $table->text('accomplishments')->nullable()->after('summary');
            $table->text('issues')->nullable()->after('accomplishments');
            $table->text('risks')->nullable()->after('issues');
            $table->text('next_steps')->nullable()->after('risks');

            // Track completed milestones (array of milestone IDs)
            $table->json('milestones_completed')->nullable()->after('next_steps');

            // Progress status
            $table->enum('progress_status', ['on_track', 'at_risk', 'delayed', 'ahead'])->default('on_track')->after('milestones_completed');

            // Rename report_date to reporting_date for clarity
            $table->renameColumn('report_date', 'reporting_date');

            // Indexes
            $table->index('project_id');
            $table->index('physical_progress');
            $table->index('financial_progress');
            $table->index('progress_status');
        });
    }

    public function down(): void
    {
        Schema::table('progress_reports', function (Blueprint $table) {
            $table->dropForeign(['project_id']);
            $table->dropIndex(['project_id']);
            $table->dropIndex(['physical_progress']);
            $table->dropIndex(['financial_progress']);
            $table->dropIndex(['progress_status']);

            $table->dropColumn([
                'project_id',
                'physical_progress',
                'financial_progress',
                'accomplishments',
                'issues',
                'risks',
                'next_steps',
                'milestones_completed',
                'progress_status',
            ]);

            $table->renameColumn('reporting_date', 'report_date');
        });
    }
};
