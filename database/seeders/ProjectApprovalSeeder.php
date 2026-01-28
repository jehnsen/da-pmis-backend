<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ProjectApproval;
use App\Models\Project;
use App\Models\User;
use Carbon\Carbon;

class ProjectApprovalSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Creates realistic project approval workflow records for CARAGA projects
     */
    public function run(): void
    {
        $projects = Project::all();
        $users = User::whereHas('role', function ($query) {
            $query->whereIn('name', ['Regional Director', 'Provincial Officer', 'Municipal Officer', 'Project Manager']);
        })->get();

        if ($projects->isEmpty() || $users->isEmpty()) {
            $this->command->warn('No projects or users found. Please run required seeders first.');
            return;
        }

        $approvalComments = [
            'submitted' => [
                'Project proposal submitted for review and approval.',
                'Submitting complete project documentation for evaluation.',
                'All required documents attached. Ready for approval process.',
                'Project ready for approval workflow.',
            ],
            'approved' => [
                'Project proposal meets all requirements. Approved for implementation.',
                'Technical and financial review completed. Approved to proceed.',
                'All criteria satisfied. Authorization granted for project execution.',
                'Comprehensive review conducted. Project approved as proposed.',
                'Aligned with regional development goals. Approved for funding.',
                'Budget allocation verified. Approved for immediate implementation.',
            ],
            'rejected' => [
                'Project does not meet current priority areas. Recommend revision.',
                'Budget proposal exceeds available allocation for this category.',
                'Technical feasibility concerns identified. Cannot approve at this time.',
                'Beneficiary selection criteria need significant improvement.',
                'Environmental compliance documentation incomplete.',
            ],
            'requested_changes' => [
                'Please revise budget breakdown with more detailed line items.',
                'Clarification needed on implementation timeline and milestones.',
                'Additional beneficiary impact assessment required.',
                'Recommend adjusting project scope to align with available resources.',
                'Please provide more details on monitoring and evaluation plan.',
                'Coordinate with other departments to avoid duplication of efforts.',
            ],
        ];

        $approvalReasons = [
            'approved' => [
                'Meets all technical and financial requirements',
                'Aligned with CARAGA regional development plan',
                'High potential impact on target beneficiaries',
                'Comprehensive implementation plan presented',
                'Adequate budget justification provided',
            ],
            'rejected' => [
                'Insufficient budget justification',
                'Does not align with current priorities',
                'Technical concerns about feasibility',
                'Inadequate risk mitigation strategy',
                'Overlaps with existing programs',
            ],
            'requested_changes' => [
                'Budget revision required',
                'Timeline adjustment needed',
                'Additional documentation requested',
                'Beneficiary criteria refinement',
                'Implementation plan clarification',
            ],
        ];

        $totalApprovals = 0;

        foreach ($projects as $project) {
            $projectStart = Carbon::parse($project->start_date);

            // Submission date: 15-45 days before project start
            $submissionDate = $projectStart->copy()->subDays(rand(15, 45));

            // Approval workflow: Field → Municipal → Provincial → Regional
            $approvalLevels = [
                ['level' => 'field', 'daysAfter' => 0],
                ['level' => 'municipal', 'daysAfter' => rand(2, 5)],
                ['level' => 'provincial', 'daysAfter' => rand(5, 10)],
                ['level' => 'regional', 'daysAfter' => rand(10, 20)],
            ];

            $currentStatus = 'draft';
            $currentDate = $submissionDate;

            // Initial submission
            ProjectApproval::create([
                'project_id' => $project->id,
                'user_id' => $users->random()->id,
                'action' => 'submitted',
                'comments' => $approvalComments['submitted'][array_rand($approvalComments['submitted'])],
                'reason' => null,
                'level' => 'field',
                'from_status' => 'draft',
                'to_status' => 'pending_approval',
                'action_taken_at' => $submissionDate,
                'created_at' => $submissionDate,
                'updated_at' => $submissionDate,
            ]);

            $totalApprovals++;
            $currentStatus = 'pending_approval';

            // Process through approval levels
            foreach ($approvalLevels as $index => $approvalLevel) {
                $actionDate = $currentDate->copy()->addDays($approvalLevel['daysAfter']);

                // Determine action: 70% approved, 20% request changes (first time only), 10% approved directly
                $rand = rand(1, 100);

                if ($rand <= 70 || $index === count($approvalLevels) - 1) {
                    // Approved at this level
                    $action = 'approved';
                    $nextStatus = ($index === count($approvalLevels) - 1) ? 'approved' : 'pending_approval';

                    ProjectApproval::create([
                        'project_id' => $project->id,
                        'user_id' => $users->random()->id,
                        'action' => $action,
                        'comments' => $approvalComments['approved'][array_rand($approvalComments['approved'])],
                        'reason' => $approvalReasons['approved'][array_rand($approvalReasons['approved'])],
                        'level' => $approvalLevel['level'],
                        'from_status' => $currentStatus,
                        'to_status' => $nextStatus,
                        'action_taken_at' => $actionDate,
                        'created_at' => $actionDate,
                        'updated_at' => $actionDate,
                    ]);

                    $totalApprovals++;
                    $currentStatus = $nextStatus;
                    $currentDate = $actionDate;

                } elseif ($rand <= 90 && $index < count($approvalLevels) - 1) {
                    // Request changes (only at non-final levels, only once)
                    $action = 'requested_changes';

                    ProjectApproval::create([
                        'project_id' => $project->id,
                        'user_id' => $users->random()->id,
                        'action' => $action,
                        'comments' => $approvalComments['requested_changes'][array_rand($approvalComments['requested_changes'])],
                        'reason' => $approvalReasons['requested_changes'][array_rand($approvalReasons['requested_changes'])],
                        'level' => $approvalLevel['level'],
                        'from_status' => $currentStatus,
                        'to_status' => 'changes_requested',
                        'action_taken_at' => $actionDate,
                        'created_at' => $actionDate,
                        'updated_at' => $actionDate,
                    ]);

                    $totalApprovals++;
                    $currentStatus = 'changes_requested';

                    // Resubmission after 3-7 days
                    $resubmitDate = $actionDate->copy()->addDays(rand(3, 7));

                    ProjectApproval::create([
                        'project_id' => $project->id,
                        'user_id' => $users->random()->id,
                        'action' => 'submitted',
                        'comments' => 'Revised proposal submitted addressing all requested changes.',
                        'reason' => null,
                        'level' => $approvalLevel['level'],
                        'from_status' => 'changes_requested',
                        'to_status' => 'pending_approval',
                        'action_taken_at' => $resubmitDate,
                        'created_at' => $resubmitDate,
                        'updated_at' => $resubmitDate,
                    ]);

                    $totalApprovals++;
                    $currentDate = $resubmitDate;
                    $currentStatus = 'pending_approval';

                    // Then approve after revision
                    $approvalAfterRevision = $resubmitDate->copy()->addDays(rand(2, 5));
                    $nextStatus = ($index === count($approvalLevels) - 1) ? 'approved' : 'pending_approval';

                    ProjectApproval::create([
                        'project_id' => $project->id,
                        'user_id' => $users->random()->id,
                        'action' => 'approved',
                        'comments' => 'All revisions satisfactory. Approved to proceed.',
                        'reason' => 'Revised proposal meets all requirements',
                        'level' => $approvalLevel['level'],
                        'from_status' => $currentStatus,
                        'to_status' => $nextStatus,
                        'action_taken_at' => $approvalAfterRevision,
                        'created_at' => $approvalAfterRevision,
                        'updated_at' => $approvalAfterRevision,
                    ]);

                    $totalApprovals++;
                    $currentStatus = $nextStatus;
                    $currentDate = $approvalAfterRevision;
                }
            }

            // 5% of projects get rejected at some point (simulate alternate timeline)
            if (rand(1, 100) <= 5) {
                $rejectionDate = $submissionDate->copy()->addDays(rand(15, 30));

                ProjectApproval::create([
                    'project_id' => $project->id,
                    'user_id' => $users->random()->id,
                    'action' => 'rejected',
                    'comments' => $approvalComments['rejected'][array_rand($approvalComments['rejected'])],
                    'reason' => $approvalReasons['rejected'][array_rand($approvalReasons['rejected'])],
                    'level' => ['provincial', 'regional'][array_rand(['provincial', 'regional'])],
                    'from_status' => 'pending_approval',
                    'to_status' => 'rejected',
                    'action_taken_at' => $rejectionDate,
                    'created_at' => $rejectionDate,
                    'updated_at' => $rejectionDate,
                ]);

                $totalApprovals++;
            }
        }

        $this->command->info("Created {$totalApprovals} project approval records with realistic workflow!");
    }
}
