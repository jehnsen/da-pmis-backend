<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ProjectType;
use Illuminate\Support\Facades\DB;

class ProjectTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Creates project types relevant to CARAGA Region agricultural development
     */
    public function run(): void
    {
        DB::transaction(function () {
            $types = [
                [
                    'name' => 'Crop Development',
                    'description' => 'Projects focused on crop improvement, production enhancement, and modern farming techniques for rice, corn, coconut, and other major crops in CARAGA region',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'name' => 'Livestock Development',
                    'description' => 'Projects for livestock breeding, management, and production improvement including cattle, carabao, swine, goats, and poultry in CARAGA provinces',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'name' => 'Fisheries Development',
                    'description' => 'Aquaculture and marine resource development projects for coastal communities in Surigao del Norte, Surigao del Sur, and Dinagat Islands',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'name' => 'Infrastructure Development',
                    'description' => 'Farm-to-market roads, irrigation systems, and agricultural infrastructure to support CARAGA farmers and communities',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'name' => 'Post-Harvest Facilities',
                    'description' => 'Storage warehouses, processing centers, drying facilities, and packaging equipment for CARAGA agricultural products',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'name' => 'Research and Development',
                    'description' => 'Agricultural research, innovation projects, and technology development specific to CARAGA region\'s agricultural conditions',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'name' => 'Capacity Building',
                    'description' => 'Training programs, farmer field schools, and education initiatives for CARAGA agricultural stakeholders',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'name' => 'Sustainable Agriculture',
                    'description' => 'Organic farming, climate-smart agriculture, and eco-friendly farming initiatives promoting environmental sustainability in CARAGA',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'name' => 'High-Value Crops',
                    'description' => 'Development projects for cacao, coffee, abaca, banana, and other high-value commercial crops in CARAGA region',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'name' => 'Farm Mechanization',
                    'description' => 'Agricultural machinery and equipment provision, mechanization programs to improve farming efficiency in CARAGA',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ];

            foreach ($types as $type) {
                ProjectType::create($type);
            }
        });

        $this->command->info('Project types seeded successfully for CARAGA Region!');
    }
}
