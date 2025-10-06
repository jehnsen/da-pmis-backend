<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Document;
use App\Models\DocumentCategory;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DocumentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Creates sample documents related to CARAGA agriculture
     */
    public function run(): void
    {
        DB::transaction(function () {
            // Get categories and users
            $categories = DocumentCategory::all()->keyBy('name');
            $admin = User::where('username', 'admin')->first();
            $creator = $admin ?? User::first();

            $documents = [
                // Annual Reports
                [
                    'title' => 'CARAGA Agricultural Accomplishment Report 2024',
                    'description' => 'Comprehensive annual report detailing the accomplishments, programs, and projects implemented by DA-CARAGA in 2024. Includes production statistics, project updates, and financial reports.',
                    'file_path' => '/storage/documents/annual-reports/caraga-accomplishment-2024.pdf',
                    'document_type' => 'PDF',
                    'published_date' => Carbon::create(2024, 12, 15),
                    'created_by' => $creator->id ?? 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                    'categories' => ['Annual Reports', 'Statistical Reports']
                ],
                [
                    'title' => 'CARAGA Regional Development Plan 2023-2028',
                    'description' => 'Five-year regional development plan for agricultural sector in CARAGA. Outlines strategic directions, priority programs, and development targets for the region.',
                    'file_path' => '/storage/documents/annual-reports/rdp-2023-2028.pdf',
                    'document_type' => 'PDF',
                    'published_date' => Carbon::create(2023, 3, 1),
                    'created_by' => $creator->id ?? 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                    'categories' => ['Annual Reports', 'Policy Guidelines']
                ],

                // Policy Guidelines
                [
                    'title' => 'Guidelines on Rice Production Enhancement Program Implementation',
                    'description' => 'Comprehensive guidelines for implementing rice production enhancement programs in CARAGA. Covers seed selection, farm inputs distribution, and beneficiary selection criteria.',
                    'file_path' => '/storage/documents/policies/rice-program-guidelines.pdf',
                    'document_type' => 'PDF',
                    'published_date' => Carbon::create(2024, 1, 10),
                    'created_by' => $creator->id ?? 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                    'categories' => ['Policy Guidelines']
                ],
                [
                    'title' => 'Organic Agriculture Certification Standards and Procedures',
                    'description' => 'Standards and procedures for organic agriculture certification in CARAGA. Details requirements, inspection processes, and certification maintenance.',
                    'file_path' => '/storage/documents/policies/organic-certification-guidelines.pdf',
                    'document_type' => 'PDF',
                    'published_date' => Carbon::create(2024, 2, 5),
                    'created_by' => $creator->id ?? 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                    'categories' => ['Policy Guidelines', 'Extension Services']
                ],

                // Technical Papers
                [
                    'title' => 'Climate-Resilient Rice Varieties for CARAGA Region',
                    'description' => 'Research paper on the performance of climate-resilient rice varieties in CARAGA conditions. Includes variety trials, yield analysis, and farmer adoption studies.',
                    'file_path' => '/storage/documents/technical/climate-resilient-rice-research.pdf',
                    'document_type' => 'PDF',
                    'published_date' => Carbon::create(2024, 6, 20),
                    'created_by' => $creator->id ?? 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                    'categories' => ['Technical Papers']
                ],
                [
                    'title' => 'Cacao Production Technology Manual for Surigao del Sur',
                    'description' => 'Technical manual on cacao production covering planting, maintenance, pest management, harvesting, and post-harvest processing specific to Surigao del Sur conditions.',
                    'file_path' => '/storage/documents/technical/cacao-production-manual.pdf',
                    'document_type' => 'PDF',
                    'published_date' => Carbon::create(2023, 9, 15),
                    'created_by' => $creator->id ?? 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                    'categories' => ['Technical Papers', 'Training Materials']
                ],

                // Budget Documents
                [
                    'title' => 'CARAGA Agricultural Budget Utilization Report Q1 2025',
                    'description' => 'First quarter budget utilization report showing financial performance, fund releases, and disbursements for agricultural programs and projects.',
                    'file_path' => '/storage/documents/budget/budget-utilization-q1-2025.pdf',
                    'document_type' => 'PDF',
                    'published_date' => Carbon::create(2025, 4, 5),
                    'created_by' => $creator->id ?? 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                    'categories' => ['Budget Documents']
                ],
                [
                    'title' => 'Program Budget Allocation 2025',
                    'description' => 'Detailed budget allocation for all agricultural programs and projects in CARAGA for fiscal year 2025.',
                    'file_path' => '/storage/documents/budget/program-budget-2025.pdf',
                    'document_type' => 'PDF',
                    'published_date' => Carbon::create(2024, 11, 30),
                    'created_by' => $creator->id ?? 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                    'categories' => ['Budget Documents']
                ],

                // Project Proposals
                [
                    'title' => 'Regional Rice Processing Complex Project Proposal',
                    'description' => 'Detailed project proposal for the establishment of a modern rice processing complex in Butuan City. Includes technical specifications, cost estimates, and feasibility study.',
                    'file_path' => '/storage/documents/proposals/rice-processing-complex-proposal.pdf',
                    'document_type' => 'PDF',
                    'published_date' => Carbon::create(2024, 8, 10),
                    'created_by' => $creator->id ?? 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                    'categories' => ['Project Proposals']
                ],
                [
                    'title' => 'Cold Storage Facilities Project Concept Paper',
                    'description' => 'Concept paper for the construction of cold storage and pack house facilities for high-value crops in strategic locations across CARAGA.',
                    'file_path' => '/storage/documents/proposals/cold-storage-concept-paper.pdf',
                    'document_type' => 'PDF',
                    'published_date' => Carbon::create(2024, 7, 25),
                    'created_by' => $creator->id ?? 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                    'categories' => ['Project Proposals']
                ],

                // Monitoring Reports
                [
                    'title' => 'CARAGA Agricultural Projects Monitoring Report September 2024',
                    'description' => 'Monthly monitoring report on the status of all ongoing agricultural projects in CARAGA. Includes progress updates, challenges, and recommendations.',
                    'file_path' => '/storage/documents/monitoring/projects-monitoring-sep-2024.pdf',
                    'document_type' => 'PDF',
                    'published_date' => Carbon::create(2024, 10, 5),
                    'created_by' => $creator->id ?? 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                    'categories' => ['Monitoring Reports']
                ],
                [
                    'title' => 'Farm-to-Market Roads Project Progress Report',
                    'description' => 'Progress report on the implementation of farm-to-market roads construction projects across CARAGA provinces.',
                    'file_path' => '/storage/documents/monitoring/fmr-progress-report.pdf',
                    'document_type' => 'PDF',
                    'published_date' => Carbon::create(2024, 9, 15),
                    'created_by' => $creator->id ?? 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                    'categories' => ['Monitoring Reports']
                ],

                // Statistical Reports
                [
                    'title' => 'CARAGA Crop Production Statistics 2023',
                    'description' => 'Comprehensive crop production statistics for CARAGA Region covering rice, corn, coconut, banana, cacao, coffee, and abaca. Includes provincial breakdowns and trend analysis.',
                    'file_path' => '/storage/documents/statistics/crop-production-2023.pdf',
                    'document_type' => 'PDF',
                    'published_date' => Carbon::create(2024, 2, 28),
                    'created_by' => $creator->id ?? 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                    'categories' => ['Statistical Reports']
                ],
                [
                    'title' => 'CARAGA Livestock and Poultry Census Report 2024',
                    'description' => 'Annual livestock and poultry census report showing population, distribution, and trends for cattle, carabao, swine, goats, and poultry in CARAGA.',
                    'file_path' => '/storage/documents/statistics/livestock-census-2024.pdf',
                    'document_type' => 'PDF',
                    'published_date' => Carbon::create(2024, 7, 30),
                    'created_by' => $creator->id ?? 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                    'categories' => ['Statistical Reports']
                ],

                // Training Materials
                [
                    'title' => 'Integrated Pest Management Training Module',
                    'description' => 'Training module for Integrated Pest Management (IPM) for farmers field schools. Covers pest identification, monitoring, and control methods.',
                    'file_path' => '/storage/documents/training/ipm-training-module.pdf',
                    'document_type' => 'PDF',
                    'published_date' => Carbon::create(2024, 3, 10),
                    'created_by' => $creator->id ?? 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                    'categories' => ['Training Materials', 'Extension Services']
                ],
                [
                    'title' => 'Climate-Smart Agriculture Practices for CARAGA Farmers',
                    'description' => 'Training material on climate-smart agriculture practices including water management, soil conservation, and crop diversification strategies.',
                    'file_path' => '/storage/documents/training/climate-smart-agriculture.pdf',
                    'document_type' => 'PDF',
                    'published_date' => Carbon::create(2024, 4, 18),
                    'created_by' => $creator->id ?? 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                    'categories' => ['Training Materials', 'Extension Services']
                ],

                // Legal Documents
                [
                    'title' => 'Memorandum of Agreement - DA-CARAGA and Local Government Units',
                    'description' => 'Memorandum of Agreement between DA-CARAGA and local government units for the implementation of agricultural programs and convergence initiatives.',
                    'file_path' => '/storage/documents/legal/moa-lgus-2024.pdf',
                    'document_type' => 'PDF',
                    'published_date' => Carbon::create(2024, 5, 20),
                    'created_by' => $creator->id ?? 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                    'categories' => ['Legal Documents']
                ],

                // Extension Services
                [
                    'title' => 'Fall Armyworm Management Advisory',
                    'description' => 'Agricultural advisory on fall armyworm identification, monitoring, and integrated management strategies for corn farmers in CARAGA.',
                    'file_path' => '/storage/documents/advisories/fall-armyworm-advisory.pdf',
                    'document_type' => 'PDF',
                    'published_date' => Carbon::create(2024, 6, 5),
                    'created_by' => $creator->id ?? 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                    'categories' => ['Extension Services']
                ],
                [
                    'title' => 'Seasonal Crop Calendar for CARAGA Region',
                    'description' => 'Comprehensive seasonal crop calendar showing optimal planting and harvesting times for major crops in CARAGA based on climatic conditions.',
                    'file_path' => '/storage/documents/advisories/seasonal-crop-calendar.pdf',
                    'document_type' => 'PDF',
                    'published_date' => Carbon::create(2024, 1, 15),
                    'created_by' => $creator->id ?? 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                    'categories' => ['Extension Services']
                ],
            ];

            foreach ($documents as $docData) {
                // Extract categories
                $categoryNames = $docData['categories'];
                unset($docData['categories']);

                // Create document
                $document = Document::create($docData);

                // Attach categories
                $categoryIds = [];
                foreach ($categoryNames as $categoryName) {
                    if (isset($categories[$categoryName])) {
                        $categoryIds[] = $categories[$categoryName]->id;
                    }
                }
                $document->categories()->attach($categoryIds);
            }
        });

        $this->command->info('Sample CARAGA agricultural documents seeded successfully!');
    }
}

