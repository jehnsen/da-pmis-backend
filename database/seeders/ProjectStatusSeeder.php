<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ProjectStatus;
use Illuminate\Support\Facades\DB;

class ProjectStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Creates project status definitions with color codes for visual indicators
     */
    public function run(): void
    {
        DB::transaction(function () {
            $statuses = [
                [
                    'name' => 'On Track',
                    'color_code' => '#28a745',
                    'description' => 'Project is progressing according to schedule with no significant issues or delays',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'name' => 'Delayed',
                    'color_code' => '#ffc107',
                    'description' => 'Project is behind schedule and requires monitoring to get back on track',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'name' => 'Critical',
                    'color_code' => '#dc3545',
                    'description' => 'Project is facing major challenges and requires immediate management attention and intervention',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'name' => 'Completed',
                    'color_code' => '#17a2b8',
                    'description' => 'Project has been successfully completed and all deliverables have been met',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'name' => 'On Hold',
                    'color_code' => '#6c757d',
                    'description' => 'Project has been temporarily suspended pending budget allocation, policy decisions, or other factors',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'name' => 'Planning',
                    'color_code' => '#007bff',
                    'description' => 'Project is in the planning and design phase, preparing for implementation',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'name' => 'Under Review',
                    'color_code' => '#6f42c1',
                    'description' => 'Project is under evaluation or assessment by management or relevant authorities',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ];

            foreach ($statuses as $status) {
                ProjectStatus::create($status);
            }
        });

        $this->command->info('Project statuses seeded successfully!');
    }
}
