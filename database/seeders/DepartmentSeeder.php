<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Department;
use Illuminate\Support\Facades\DB;

class DepartmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Creates departments specific to DA-CARAGA organizational structure
     */
    public function run(): void
    {
        DB::transaction(function () {
            $departments = [
                [
                    'name' => 'Office of the Regional Executive Director',
                    'description' => 'Provides overall leadership and direction for all agricultural programs and projects in CARAGA Region. Oversees policy implementation and regional coordination.',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'name' => 'Field Operations Division',
                    'description' => 'Manages field implementation of agricultural programs, farmer assistance, and extension services across CARAGA provinces.',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'name' => 'Agribusiness and Marketing Assistance Division',
                    'description' => 'Facilitates market linkages, agribusiness development, and value chain enhancement for CARAGA agricultural products.',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'name' => 'Rice Program',
                    'description' => 'Implements rice production programs, seed distribution, and irrigation development in Agusan and Surigao provinces.',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'name' => 'High-Value Crops Development Program',
                    'description' => 'Develops cacao, coffee, abaca, banana, and other high-value commercial crops in CARAGA region.',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'name' => 'Livestock Development Division',
                    'description' => 'Promotes livestock production, breeding programs, and animal health services for cattle, carabao, swine, goats, and poultry.',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'name' => 'Fisheries and Aquatic Resources Division',
                    'description' => 'Develops fisheries and aquaculture projects in coastal areas of Surigao and Dinagat Islands.',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'name' => 'Agricultural Engineering Division',
                    'description' => 'Plans and implements farm-to-market roads, irrigation systems, and post-harvest facilities infrastructure.',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'name' => 'Regional Agricultural and Biosystems Engineering Division',
                    'description' => 'Provides farm mechanization, agricultural machinery services, and engineering support for CARAGA farmers.',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'name' => 'Research and Development Division',
                    'description' => 'Conducts agricultural research, technology development, and innovation projects specific to CARAGA conditions.',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'name' => 'Regulatory Division',
                    'description' => 'Enforces agricultural regulations, standards, and compliance monitoring for plant and animal products.',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'name' => 'Planning, Monitoring and Evaluation Division',
                    'description' => 'Handles strategic planning, project monitoring, performance evaluation, and statistical reporting.',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'name' => 'Finance and Administrative Division',
                    'description' => 'Manages budgeting, financial management, procurement, and administrative support services.',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'name' => 'Organic Agriculture Program',
                    'description' => 'Promotes organic farming practices, certification programs, and sustainable agriculture in CARAGA.',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'name' => 'Agricultural Extension Services',
                    'description' => 'Provides farmer training, technology transfer, and capacity building programs throughout CARAGA region.',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ];

            foreach ($departments as $department) {
                Department::create($department);
            }
        });

        $this->command->info('Departments seeded successfully for DA-CARAGA!');
    }
}

