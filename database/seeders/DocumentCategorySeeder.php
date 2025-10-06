<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\DocumentCategory;
use Illuminate\Support\Facades\DB;

class DocumentCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Creates document categories for CARAGA agricultural documentation
     */
    public function run(): void
    {
        DB::transaction(function () {
            $categories = [
                [
                    'name' => 'Annual Reports',
                    'description' => 'Yearly performance reports, accomplishment reports, and annual summaries of CARAGA agricultural activities',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'name' => 'Policy Guidelines',
                    'description' => 'Agricultural policies, regulations, circulars, and administrative issuances for CARAGA Region XIII',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'name' => 'Technical Papers',
                    'description' => 'Research studies, technical documentation, and scientific papers on CARAGA agricultural development',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'name' => 'Budget Documents',
                    'description' => 'Financial reports, budget allocations, fund utilization reports, and disbursement documents',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'name' => 'Project Proposals',
                    'description' => 'Project concept papers, detailed proposals, and feasibility studies for agricultural projects',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'name' => 'Monitoring Reports',
                    'description' => 'Progress monitoring reports, quarterly assessments, and project evaluation documents',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'name' => 'Statistical Reports',
                    'description' => 'Agricultural statistics, crop production data, livestock census, and fisheries production reports for CARAGA',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'name' => 'Training Materials',
                    'description' => 'Educational resources, training modules, and capacity building materials for farmers and agricultural workers',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'name' => 'Legal Documents',
                    'description' => 'Memoranda of agreement, contracts, legal instruments, and partnership agreements',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'name' => 'Extension Services',
                    'description' => 'Farmer advisories, pest and disease alerts, seasonal guides, and agricultural extension materials',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ];

            foreach ($categories as $category) {
                DocumentCategory::create($category);
            }
        });

        $this->command->info('Document categories seeded successfully for CARAGA Region!');
    }
}
