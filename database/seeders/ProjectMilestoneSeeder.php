<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ProjectMilestone;
use App\Models\Project;
use Carbon\Carbon;

class ProjectMilestoneSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Creates realistic milestones for agricultural projects in CARAGA
     */
    public function run(): void
    {
        $projects = Project::all();

        if ($projects->isEmpty()) {
            $this->command->warn('No projects found. Please run ProjectSeeder first.');
            return;
        }

        $milestoneTemplates = [
            'Crop Development' => [
                [
                    'title' => 'Project Launch and Beneficiary Selection',
                    'description' => 'Conduct orientation meetings, finalize list of farmer beneficiaries, and complete registration process.',
                    'offset_start' => 0,
                    'offset_end' => 30,
                ],
                [
                    'title' => 'Distribution of Planting Materials',
                    'description' => 'Procure and distribute certified seeds/seedlings to registered farmers. Includes quality inspection and documentation.',
                    'offset_start' => 30,
                    'offset_end' => 90,
                ],
                [
                    'title' => 'Training on Modern Farming Techniques',
                    'description' => 'Conduct Farmer Field Schools and hands-on training sessions on improved agricultural practices.',
                    'offset_start' => 60,
                    'offset_end' => 120,
                ],
                [
                    'title' => 'Establishment of Demonstration Farms',
                    'description' => 'Set up model farms showcasing best practices and technologies for farmer learning.',
                    'offset_start' => 90,
                    'offset_end' => 180,
                ],
                [
                    'title' => 'Mid-term Monitoring and Evaluation',
                    'description' => 'Assess project progress, document challenges, and implement corrective measures.',
                    'offset_start' => 180,
                    'offset_end' => 240,
                ],
                [
                    'title' => 'Harvest and Post-Harvest Training',
                    'description' => 'Training on proper harvesting techniques, post-harvest handling, and value-adding activities.',
                    'offset_start' => 240,
                    'offset_end' => 300,
                ],
                [
                    'title' => 'Market Linkage Development',
                    'description' => 'Facilitate connections with buyers, establish marketing agreements, and organize selling activities.',
                    'offset_start' => 300,
                    'offset_end' => 360,
                ],
                [
                    'title' => 'Final Evaluation and Documentation',
                    'description' => 'Complete impact assessment, prepare terminal reports, and document best practices and lessons learned.',
                    'offset_start' => 360,
                    'offset_end' => 400,
                ],
            ],
            'Infrastructure Development' => [
                [
                    'title' => 'Detailed Engineering Design',
                    'description' => 'Complete technical plans, specifications, and cost estimates. Obtain necessary approvals.',
                    'offset_start' => 0,
                    'offset_end' => 60,
                ],
                [
                    'title' => 'Procurement of Materials and Equipment',
                    'description' => 'Conduct bidding process, award contracts, and ensure timely delivery of construction materials.',
                    'offset_start' => 60,
                    'offset_end' => 120,
                ],
                [
                    'title' => 'Site Preparation and Mobilization',
                    'description' => 'Clear project site, establish work areas, and mobilize construction teams and equipment.',
                    'offset_start' => 90,
                    'offset_end' => 150,
                ],
                [
                    'title' => 'Foundation and Structural Works',
                    'description' => 'Complete excavation, foundation laying, and main structural construction activities.',
                    'offset_start' => 150,
                    'offset_end' => 300,
                ],
                [
                    'title' => 'Installation of Systems and Equipment',
                    'description' => 'Install electrical, water, and specialized agricultural equipment systems.',
                    'offset_start' => 300,
                    'offset_end' => 420,
                ],
                [
                    'title' => 'Testing and Commissioning',
                    'description' => 'Conduct system tests, trial operations, and address any defects or issues.',
                    'offset_start' => 420,
                    'offset_end' => 480,
                ],
                [
                    'title' => 'Turnover and User Training',
                    'description' => 'Official turnover of facility, train users on operations and maintenance.',
                    'offset_start' => 480,
                    'offset_end' => 540,
                ],
            ],
            'Livestock Development' => [
                [
                    'title' => 'Beneficiary Screening and Selection',
                    'description' => 'Evaluate applicants, conduct site visits, and finalize beneficiary list based on criteria.',
                    'offset_start' => 0,
                    'offset_end' => 45,
                ],
                [
                    'title' => 'Construction of Animal Housing',
                    'description' => 'Build or upgrade livestock pens, shelters, and support facilities to standard specifications.',
                    'offset_start' => 30,
                    'offset_end' => 120,
                ],
                [
                    'title' => 'Procurement and Distribution of Animals',
                    'description' => 'Source quality breeding stock, conduct health screening, and distribute to beneficiaries.',
                    'offset_start' => 90,
                    'offset_end' => 150,
                ],
                [
                    'title' => 'Veterinary Services and Animal Health',
                    'description' => 'Provide vaccination, deworming, and regular veterinary check-ups for distributed animals.',
                    'offset_start' => 120,
                    'offset_end' => 300,
                ],
                [
                    'title' => 'Feed Production and Management Training',
                    'description' => 'Training on feeds formulation, forage production, and proper feeding protocols.',
                    'offset_start' => 150,
                    'offset_end' => 210,
                ],
                [
                    'title' => 'Breeding and Offspring Monitoring',
                    'description' => 'Monitor breeding activities, birth rates, and offspring survival. Document genetic improvements.',
                    'offset_start' => 240,
                    'offset_end' => 360,
                ],
                [
                    'title' => 'Marketing and Value Chain Development',
                    'description' => 'Establish market linkages, facilitate livestock sales, and develop meat processing opportunities.',
                    'offset_start' => 360,
                    'offset_end' => 450,
                ],
            ],
            'Fisheries Development' => [
                [
                    'title' => 'Site Assessment and Beneficiary Organization',
                    'description' => 'Conduct water quality tests, assess site suitability, and organize fisherfolk associations.',
                    'offset_start' => 0,
                    'offset_end' => 60,
                ],
                [
                    'title' => 'Installation of Culture Systems',
                    'description' => 'Install fish cages, fish ponds, or seaweed farming structures according to specifications.',
                    'offset_start' => 45,
                    'offset_end' => 120,
                ],
                [
                    'title' => 'Fingerling/Seedstock Distribution',
                    'description' => 'Procure quality fingerlings or seedstock and distribute to beneficiary fisherfolk.',
                    'offset_start' => 90,
                    'offset_end' => 150,
                ],
                [
                    'title' => 'Aquaculture Training Program',
                    'description' => 'Training on water quality management, feeding, disease prevention, and sustainable practices.',
                    'offset_start' => 120,
                    'offset_end' => 180,
                ],
                [
                    'title' => 'First Harvest and Post-Harvest Handling',
                    'description' => 'Monitor first harvest cycle, train on proper handling, processing, and storage methods.',
                    'offset_start' => 210,
                    'offset_end' => 270,
                ],
                [
                    'title' => 'Market Access and Product Development',
                    'description' => 'Link to buyers, establish cold chain, and explore value-added product opportunities.',
                    'offset_start' => 270,
                    'offset_end' => 360,
                ],
            ],
            'Capacity Building' => [
                [
                    'title' => 'Training Needs Assessment',
                    'description' => 'Survey farmers, identify skill gaps, and design appropriate training modules.',
                    'offset_start' => 0,
                    'offset_end' => 45,
                ],
                [
                    'title' => 'Curriculum Development and Materials Preparation',
                    'description' => 'Prepare training manuals, visual aids, and hands-on learning materials in local language.',
                    'offset_start' => 30,
                    'offset_end' => 90,
                ],
                [
                    'title' => 'Training of Trainers Workshop',
                    'description' => 'Certify agricultural extension workers and lead farmers as trainers.',
                    'offset_start' => 60,
                    'offset_end' => 120,
                ],
                [
                    'title' => 'Batch 1 Farmer Training',
                    'description' => 'Conduct first series of Farmer Field School sessions for initial batch of participants.',
                    'offset_start' => 120,
                    'offset_end' => 210,
                ],
                [
                    'title' => 'Batch 2 Farmer Training',
                    'description' => 'Second batch training rollout with improved curriculum based on feedback.',
                    'offset_start' => 210,
                    'offset_end' => 300,
                ],
                [
                    'title' => 'Skills Application and Mentoring',
                    'description' => 'Field mentoring visits to support farmers in applying learned techniques.',
                    'offset_start' => 300,
                    'offset_end' => 420,
                ],
                [
                    'title' => 'Competency Assessment and Certification',
                    'description' => 'Evaluate farmer competencies and award certificates to qualified graduates.',
                    'offset_start' => 420,
                    'offset_end' => 480,
                ],
            ],
        ];

        $totalMilestones = 0;

        foreach ($projects as $project) {
            $projectType = $project->projectType->name ?? 'Crop Development';

            // Match project type to template category
            $templateKey = 'Crop Development'; // default
            if (str_contains($projectType, 'Infrastructure')) {
                $templateKey = 'Infrastructure Development';
            } elseif (str_contains($projectType, 'Livestock')) {
                $templateKey = 'Livestock Development';
            } elseif (str_contains($projectType, 'Fisheries')) {
                $templateKey = 'Fisheries Development';
            } elseif (str_contains($projectType, 'Capacity') || str_contains($projectType, 'Training')) {
                $templateKey = 'Capacity Building';
            }

            $templates = $milestoneTemplates[$templateKey] ?? $milestoneTemplates['Crop Development'];

            $projectStart = Carbon::parse($project->start_date);
            $projectEnd = Carbon::parse($project->end_date);
            $projectDuration = $projectStart->diffInDays($projectEnd);

            foreach ($templates as $index => $template) {
                // Calculate dates based on offset percentages
                $targetOffset = ($template['offset_end'] / 400) * $projectDuration;
                $targetDate = $projectStart->copy()->addDays($targetOffset);

                // Determine status and completion date
                $now = now();
                $status = 'pending';
                $completionDate = null;

                if ($targetDate->isPast()) {
                    // Past milestones - 80% completed, 15% in progress, 5% delayed
                    $rand = rand(1, 100);
                    if ($rand <= 80) {
                        $status = 'completed';
                        // Completed 0-15 days before or after target
                        $completionDate = $targetDate->copy()->addDays(rand(-15, 15));
                    } elseif ($rand <= 95) {
                        $status = 'in_progress';
                    } else {
                        $status = 'delayed';
                    }
                } elseif ($targetDate->isFuture() && $targetDate->diffInDays($now) < 30) {
                    // Near-future milestones - 40% in progress
                    $status = rand(1, 100) <= 40 ? 'in_progress' : 'pending';
                }

                ProjectMilestone::create([
                    'project_id' => $project->id,
                    'title' => $template['title'],
                    'description' => $template['description'],
                    'target_date' => $targetDate,
                    'completion_date' => $completionDate,
                    'status' => $status,
                    'created_at' => $project->created_at,
                    'updated_at' => $completionDate ?? now(),
                ]);

                $totalMilestones++;
            }
        }

        $this->command->info("Created {$totalMilestones} realistic project milestones!");
    }
}
