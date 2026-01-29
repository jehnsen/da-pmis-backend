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
     * Creates realistic project approval workflow records for Provincial LGU (RA 7160)
     */
    public function run(): void
    {
        $projects = Project::all();
        $users = User::whereHas('role', function ($query) {
            $query->whereIn('name', [
                'Provincial Governor',
                'Provincial Planning Officer (PPDO)',
                'Provincial Officer',
                'Municipal Planning Officer (MPDO)',
                'Municipal Officer',
                'Barangay Development Council Officer',
                'Project Manager'
            ]);
        })->get();

        if ($projects->isEmpty() || $users->isEmpty()) {
            $this->command->warn('No projects or users found. Please run required seeders first.');
            return;
        }

        $approvalComments = [
            'submitted' => [
                'Project proposal submitted to Barangay Development Council as per RA 7160 Section 106.',
                'Barangay Development Plan (BDP) documentation complete. Community consultation records attached.',
                'All required documents attached: project proposal, budget breakdown, beneficiary list, and environmental compliance certificate.',
                'Community-based project proposal aligned with sector priorities. Ready for LGU approval workflow.',
                'Project proposal submitted with full documentation per Local Budget Circular requirements.',
                'Complete submission package includes BDP alignment, sustainability plan, and community endorsement.',
            ],
            'approved' => [
                'BDC review complete. Project aligns with Barangay Development Plan. Forwarding to MPDO for validation.',
                'MPDO validation passed. Project consistent with Annual Investment Plan (AIP). Recommending for PPDO technical review.',
                'PPDO technical review completed. Project meets LDIP criteria and development targets. Approved for Governor consideration.',
                'Provincial Governor approval granted. Project authorized for implementation per RA 7160 Section 455.',
                'Sector alignment verified. Budget allocation confirmed per 20% Development Fund requirement. Approved.',
                'COA compliance check passed. Project approved with proper audit trail and transparency measures.',
                'NEDA development framework alignment confirmed. Project supports Sustainable Development Goals. Approved.',
                'DBM budget guidelines satisfied. Cost-benefit analysis favorable. Project approved for fund release.',
            ],
            'rejected' => [
                'Project does not align with current Provincial Development Plan priorities. Revision recommended.',
                'Budget proposal exceeds sector allocation ceiling. Recommend rescoping or phased implementation.',
                'Technical feasibility study incomplete. Engineering assessment required before approval.',
                'Beneficiary targeting methodology needs improvement per DSWD poverty database standards.',
                'Environmental Compliance Certificate (ECC) from DENR required. Cannot proceed without clearance.',
                'Overlap with existing LGU programs identified. Recommend coordination with implementing office.',
                'Insufficient justification for budget allocation. Cost-efficiency concerns noted.',
                'Project timeline conflicts with Provincial Annual Investment Program (AIP) schedule.',
            ],
            'requested_changes' => [
                'Please revise budget breakdown to align with DBM Chart of Accounts and UACS coding.',
                'Clarification needed on implementation timeline. Align milestones with PPDO monitoring schedule.',
                'Additional beneficiary impact assessment required per DILG Local Governance Performance Management System.',
                'Recommend adjusting project scope to maximize cost-per-beneficiary efficiency ratio.',
                'Please provide detailed Monitoring and Evaluation (M&E) framework with SMART indicators.',
                'Coordinate with Provincial Planning & Development Office to avoid duplication with other sector programs.',
                'Revise procurement plan to comply with RA 9184 (Government Procurement Reform Act).',
                'Update sustainability plan showing post-project operations and maintenance arrangements.',
                'Include community participation plan demonstrating bottom-up budgeting compliance.',
                'Strengthen risk management matrix addressing climate change adaptation measures.',
            ],
        ];

        $approvalReasons = [
            'approved' => [
                'Meets all technical and financial requirements per RA 7160 Local Government Code',
                'Aligned with Provincial Development Plan, LDIP, and AIP targets for the sector',
                'High poverty impact potential - targets 4Ps beneficiaries and vulnerable populations',
                'Comprehensive implementation plan with clear deliverables and sustainability measures',
                'Adequate budget justification with favorable cost-benefit ratio per DBM standards',
                'Supports national development goals: AmBisyon Natin 2040 and Philippine Development Plan',
                'Climate change resilience features integrated per Climate Change Act (RA 9729)',
                'Participatory planning process demonstrates bottom-up budgeting compliance',
                'Strong multi-sectoral coordination mechanisms with LGU departments',
                'Gender and Development (GAD) mainstreaming evident in project design',
            ],
            'rejected' => [
                'Insufficient budget justification - cost estimates exceed sector benchmark by 30%+',
                'Does not align with priority sectors identified in Provincial Investment Plan',
                'Technical feasibility concerns - lacks proper engineering or technical assessment',
                'Inadequate risk mitigation strategy for identified implementation challenges',
                'Significant overlap with existing LGU programs - recommend consolidation',
                'Fails to meet minimum beneficiary targeting thresholds per DSWD LISTAHANAN',
                'Environmental safeguards insufficient - DENR clearance requirements not addressed',
                'Procurement plan violates RA 9184 timelines and procedures',
                'Sustainability plan inadequate - no clear post-implementation maintenance strategy',
                'Community participation evidence lacking - insufficient barangay resolution support',
            ],
            'requested_changes' => [
                'Budget revision required to align with UACS coding and DBM Chart of Accounts',
                'Timeline adjustment needed to synchronize with LGU fiscal year and budget cycle',
                'Additional documentation requested: detailed technical specifications and BOQ',
                'Beneficiary targeting criteria refinement per DSWD poverty database standards',
                'Implementation plan clarification - detailed Gantt chart with critical path analysis required',
                'Procurement plan revision - must comply with RA 9184 competitive bidding requirements',
                'M&E framework enhancement - include baseline data and impact indicators',
                'Risk assessment update - address climate vulnerability and disaster risk reduction',
                'Gender audit needed - demonstrate compliance with GAD budget requirement (5% minimum)',
                'Coordination protocols clarification with provincial offices and NGAs',
            ],
        ];

        // Level-specific approval comments (for more realism)
        $levelSpecificComments = [
            'barangay' => [
                'approved' => [
                    'Barangay Development Council endorses this project. Community consultation conducted with 85% support.',
                    'BDC review complete. Project addresses identified community needs per Barangay Development Plan.',
                    'Community participation verified. Project aligns with barangay priorities. Forwarding to MPDO.',
                    'Barangay resolution attached. Project has strong community backing. Recommended for municipal validation.',
                ],
                'requested_changes' => [
                    'BDC requests additional community consultation with senior citizens and PWD sectors.',
                    'Please include barangay counterpart contribution commitment letter from Punong Barangay.',
                    'Clarify beneficiary selection - ensure equitable distribution across all puroks/sitios.',
                ],
            ],
            'municipal' => [
                'approved' => [
                    'MPDO validation complete. Project consistent with Municipal Development Plan and AIP.',
                    'Municipal Planning & Development Office confirms alignment with local development priorities.',
                    'Technical review by MPDO engineering staff passed. Forwarding to PPDO for provincial assessment.',
                    'MPDO endorses project. Budget allocation feasible within municipal IRA allotment framework.',
                ],
                'requested_changes' => [
                    'MPDO requests detailed Bill of Quantities (BOQ) and updated cost estimates.',
                    'Please coordinate with Municipal Engineering Office for technical specifications review.',
                    'Align implementation schedule with municipal procurement calendar and budget availability.',
                ],
            ],
            'provincial' => [
                'approved' => [
                    'PPDO technical review completed. Project meets LDIP criteria and provincial development targets.',
                    'Provincial Planning & Development Office confirms alignment with sector investment program.',
                    'PPDO assessment: Project demonstrates high development impact per cost-benefit analysis.',
                    'Technical evaluation satisfactory. Environmental and social safeguards adequate. Forwarding to Governor.',
                ],
                'requested_changes' => [
                    'PPDO recommends integration of climate change adaptation measures per Provincial DRRM Plan.',
                    'Please revise M&E framework to include provincial performance indicators and reporting templates.',
                    'Coordinate with Provincial Agriculturist/Engineer/Health Officer for sector-specific technical input.',
                ],
            ],
            'governor' => [
                'approved' => [
                    'Office of the Provincial Governor grants final approval. Project authorized for implementation.',
                    'Governor approval issued per RA 7160 Section 455. Fund release authorized upon completion of pre-procurement.',
                    'Provincial Governor endorses project. Allocate funds from 20% Development Fund as recommended by PPDO.',
                    'Executive approval granted. Direct implementing offices to commence procurement per RA 9184 requirements.',
                ],
                'requested_changes' => [
                    'Office of the Governor requests comprehensive briefing on project sustainability and long-term fiscal impact.',
                    'Please provide updated justification addressing potential overlaps with national government agency programs.',
                ],
            ],
        ];

        $totalApprovals = 0;

        foreach ($projects as $project) {
            $projectStart = Carbon::parse($project->start_date);

            // Submission date: 15-45 days before project start
            $submissionDate = $projectStart->copy()->subDays(rand(15, 45));

            // Approval workflow (RA 7160): Barangay → Municipal (MPDO) → Provincial (PPDO) → Governor
            $approvalLevels = [
                ['level' => 'barangay', 'daysAfter' => 0],
                ['level' => 'municipal', 'daysAfter' => rand(2, 5)],
                ['level' => 'provincial', 'daysAfter' => rand(5, 10)],
                ['level' => 'governor', 'daysAfter' => rand(10, 20)],
            ];

            $currentStatus = 'draft';
            $currentDate = $submissionDate;

            // Initial submission (add random minutes to avoid duplicate timestamps)
            $submissionDateTime = $submissionDate->copy()->addMinutes(rand(0, 240));
            ProjectApproval::create([
                'project_id' => $project->id,
                'user_id' => $users->random()->id,
                'action' => 'submitted',
                'comments' => $approvalComments['submitted'][array_rand($approvalComments['submitted'])],
                'reason' => null,
                'level' => 'barangay',
                'from_status' => 'draft',
                'to_status' => 'pending_barangay',
                'action_taken_at' => $submissionDateTime,
                'created_at' => $submissionDateTime,
                'updated_at' => $submissionDateTime,
            ]);

            $totalApprovals++;
            $currentStatus = 'pending_barangay';

            // Process through approval levels
            foreach ($approvalLevels as $index => $approvalLevel) {
                $actionDate = $currentDate->copy()->addDays($approvalLevel['daysAfter'])->addMinutes(rand(0, 480));

                // Determine action: 70% approved, 20% request changes (first time only), 10% approved directly
                $rand = rand(1, 100);

                if ($rand <= 70 || $index === count($approvalLevels) - 1) {
                    // Approved at this level
                    $action = 'approved';
                    // Determine next status based on RA 7160 levels
                    if ($index === count($approvalLevels) - 1) {
                        $nextStatus = 'approved';
                    } else {
                        $nextLevel = $approvalLevels[$index + 1]['level'];
                        $nextStatus = "pending_{$nextLevel}";
                    }

                    // Use level-specific comments for more realism
                    $currentLevelName = $approvalLevel['level'];
                    $levelComments = $levelSpecificComments[$currentLevelName]['approved'] ?? $approvalComments['approved'];
                    $selectedComment = $levelComments[array_rand($levelComments)];

                    ProjectApproval::create([
                        'project_id' => $project->id,
                        'user_id' => $users->random()->id,
                        'action' => $action,
                        'comments' => $selectedComment,
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

                    // Use level-specific comments for requested changes
                    $currentLevelName = $approvalLevel['level'];
                    $levelChangeComments = $levelSpecificComments[$currentLevelName]['requested_changes'] ?? $approvalComments['requested_changes'];
                    $selectedChangeComment = $levelChangeComments[array_rand($levelChangeComments)];

                    ProjectApproval::create([
                        'project_id' => $project->id,
                        'user_id' => $users->random()->id,
                        'action' => $action,
                        'comments' => $selectedChangeComment,
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

                    // Resubmission after 3-7 days (add random hours to avoid duplicate timestamps)
                    $resubmitDate = $actionDate->copy()->addDays(rand(3, 7))->addHours(rand(0, 23))->addMinutes(rand(0, 59));

                    ProjectApproval::create([
                        'project_id' => $project->id,
                        'user_id' => $users->random()->id,
                        'action' => 'submitted',
                        'comments' => $approvalComments['submitted'][array_rand($approvalComments['submitted'])],
                        'reason' => null,
                        'level' => $approvalLevel['level'],
                        'from_status' => 'changes_requested',
                        'to_status' => "pending_{$approvalLevel['level']}",
                        'action_taken_at' => $resubmitDate,
                        'created_at' => $resubmitDate,
                        'updated_at' => $resubmitDate,
                    ]);

                    $totalApprovals++;
                    $currentDate = $resubmitDate;
                    $currentStatus = "pending_{$approvalLevel['level']}";

                    // Then approve after revision (add random hours to avoid duplicate timestamps)
                    $approvalAfterRevision = $resubmitDate->copy()->addDays(rand(2, 5))->addHours(rand(0, 23))->addMinutes(rand(0, 59));
                    if ($index === count($approvalLevels) - 1) {
                        $nextStatus = 'approved';
                    } else {
                        $nextLevel = $approvalLevels[$index + 1]['level'];
                        $nextStatus = "pending_{$nextLevel}";
                    }

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
                $rejectionDate = $submissionDate->copy()->addDays(rand(15, 30))->addHours(rand(0, 23))->addMinutes(rand(0, 59));
                $rejectionLevel = ['provincial', 'governor'][array_rand(['provincial', 'governor'])];

                ProjectApproval::create([
                    'project_id' => $project->id,
                    'user_id' => $users->random()->id,
                    'action' => 'rejected',
                    'comments' => $approvalComments['rejected'][array_rand($approvalComments['rejected'])],
                    'reason' => $approvalReasons['rejected'][array_rand($approvalReasons['rejected'])],
                    'level' => $rejectionLevel,
                    'from_status' => "pending_{$rejectionLevel}",
                    'to_status' => 'rejected',
                    'action_taken_at' => $rejectionDate,
                    'created_at' => $rejectionDate,
                    'updated_at' => $rejectionDate,
                ]);

                $totalApprovals++;
            }
        }

        $this->command->info("✅ Created {$totalApprovals} project approval records with realistic RA 7160 LGU workflow!");
        $this->command->info("   Approval chain: Barangay → Municipal (MPDO) → Provincial (PPDO) → Governor");
        $this->command->info("   Includes: BDC endorsements, MPDO validations, PPDO technical reviews, and Governor approvals");
    }
}
