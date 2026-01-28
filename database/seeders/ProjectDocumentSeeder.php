<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Document;
use App\Models\DocumentCategory;
use App\Models\Project;
use App\Models\Department;
use App\Models\User;
use Carbon\Carbon;

class ProjectDocumentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Creates project-related documents for CARAGA agricultural projects
     */
    public function run(): void
    {
        $projects = Project::all();
        $users = User::all();
        $categories = DocumentCategory::all()->keyBy('name');

        if ($projects->isEmpty() || $users->isEmpty()) {
            $this->command->warn('No projects or users found. Please run required seeders first.');
            return;
        }

        $documentTypes = [
            'Project Proposal' => [
                'category' => 'Project Proposals',
                'file_types' => ['PDF', 'DOCX'],
                'descriptions' => [
                    'Detailed project proposal including objectives, methodology, budget, and implementation plan',
                    'Technical project proposal with feasibility study and cost-benefit analysis',
                    'Comprehensive proposal document with project rationale and expected outcomes',
                ],
            ],
            'Implementation Plan' => [
                'category' => 'Project Proposals',
                'file_types' => ['PDF', 'XLSX'],
                'descriptions' => [
                    'Detailed implementation plan with timeline, activities, and resource allocation',
                    'Project work plan showing milestones, deliverables, and responsible parties',
                    'Operational plan with activity schedule and monitoring framework',
                ],
            ],
            'Progress Report' => [
                'category' => 'Monitoring Reports',
                'file_types' => ['PDF', 'DOCX'],
                'descriptions' => [
                    'Quarterly project progress report showing accomplishments and challenges',
                    'Monthly monitoring report documenting project activities and financial status',
                    'Semi-annual progress report with performance indicators and recommendations',
                ],
            ],
            'Financial Report' => [
                'category' => 'Budget Documents',
                'file_types' => ['PDF', 'XLSX'],
                'descriptions' => [
                    'Detailed financial report showing budget utilization and disbursements',
                    'Quarterly financial statement with variance analysis',
                    'Project budget performance report with expenditure breakdown',
                ],
            ],
            'Technical Specifications' => [
                'category' => 'Technical Papers',
                'file_types' => ['PDF', 'DOCX'],
                'descriptions' => [
                    'Technical specifications for equipment, materials, and infrastructure',
                    'Detailed technical requirements and quality standards',
                    'Engineering design specifications and construction guidelines',
                ],
            ],
            'Procurement Documents' => [
                'category' => 'Legal Documents',
                'file_types' => ['PDF'],
                'descriptions' => [
                    'Procurement plan and bidding documents for project supplies',
                    'Purchase orders and supplier contracts',
                    'Procurement compliance documentation and vendor evaluation',
                ],
            ],
            'Training Materials' => [
                'category' => 'Training Materials',
                'file_types' => ['PDF', 'PPTX'],
                'descriptions' => [
                    'Training modules and presentation materials for beneficiaries',
                    'Farmer field school curriculum and learning materials',
                    'Technical training handouts and reference guides',
                ],
            ],
            'Completion Report' => [
                'category' => 'Monitoring Reports',
                'file_types' => ['PDF'],
                'descriptions' => [
                    'Final project completion report with achievements and lessons learned',
                    'Terminal report documenting project outcomes and sustainability plans',
                    'End-of-project evaluation report with impact assessment',
                ],
            ],
        ];

        $totalDocuments = 0;

        foreach ($projects as $project) {
            // Each project gets 3-6 documents
            $docCount = rand(3, 6);
            $selectedTypes = array_rand($documentTypes, min($docCount, count($documentTypes)));

            if (!is_array($selectedTypes)) {
                $selectedTypes = [$selectedTypes];
            }

            foreach ($selectedTypes as $docType) {
                $typeData = $documentTypes[$docType];
                $fileType = $typeData['file_types'][array_rand($typeData['file_types'])];
                $description = $typeData['descriptions'][array_rand($typeData['descriptions'])];

                // Generate filename
                $projectSlug = strtolower(str_replace(' ', '-', substr($project->title, 0, 40)));
                $typeSlug = strtolower(str_replace(' ', '-', $docType));
                $fileName = sprintf('%s-%s.%s', $projectSlug, $typeSlug, strtolower($fileType));

                // File path
                $filePath = sprintf('documents/projects/%d/%s', $project->id, $fileName);

                // File size between 200KB and 8MB
                $fileSize = rand(200000, 8000000);

                // Determine mime type
                $mimeTypes = [
                    'PDF' => 'application/pdf',
                    'DOCX' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                    'XLSX' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                    'PPTX' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
                ];

                // Document created date (between project start and now)
                $projectStart = Carbon::parse($project->created_at);
                $docCreatedAt = $projectStart->copy()->addDays(rand(5, 90));

                // Published date (5-30 days after creation)
                $publishedDate = $docCreatedAt->copy()->addDays(rand(5, 30));

                // Download count (realistic based on document age)
                $documentAge = $publishedDate->diffInDays(now());
                $downloadCount = max(0, rand(0, min($documentAge * 2, 500)));

                // Featured status (20% chance)
                $isFeatured = rand(1, 100) <= 20;

                // Fiscal year
                $fiscalYear = $publishedDate->year;

                $document = Document::create([
                    'title' => sprintf('%s - %s', substr($project->title, 0, 60), $docType),
                    'description' => $description,
                    'file_path' => $filePath,
                    'file_name' => $fileName,
                    'mime_type' => $mimeTypes[$fileType],
                    'file_size' => $fileSize,
                    'download_count' => $downloadCount,
                    'document_type' => $fileType,
                    'category_id' => $categories[$typeData['category']]->id ?? null,
                    'department_id' => $project->department_id,
                    'project_id' => $project->id,
                    'fiscal_year' => $fiscalYear,
                    'is_featured' => $isFeatured,
                    'published_date' => $publishedDate,
                    'created_by' => $users->random()->id,
                    'created_at' => $docCreatedAt,
                    'updated_at' => now(),
                ]);

                // Attach category via pivot table
                if (isset($categories[$typeData['category']])) {
                    $document->categories()->attach($categories[$typeData['category']]->id);
                }

                $totalDocuments++;
            }
        }

        $this->command->info("Created {$totalDocuments} project-related documents!");
    }
}
