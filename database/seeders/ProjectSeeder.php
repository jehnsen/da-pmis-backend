<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Project;
use App\Models\ProjectType;
use App\Models\ProjectStatus;
use App\Models\Department;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ProjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Creates realistic agricultural projects for CARAGA Region XIII
     */
    public function run(): void
    {
        DB::transaction(function () {
            // Get reference data
            $projectTypes = ProjectType::all()->keyBy('name');
            $projectStatuses = ProjectStatus::all()->keyBy('name');
            $departments = Department::all()->keyBy('name');
            $users = User::all();

            $projects = [
                // Rice Production Projects
                [
                    'title' => 'Hybrid Rice Production Enhancement in Agusan del Norte',
                    'description' => 'Comprehensive program to increase hybrid rice adoption and production in Agusan del Norte province. Includes distribution of certified hybrid rice seeds, training on modern rice farming techniques, provision of farm inputs, and establishment of demonstration farms. Target beneficiaries: 2,500 rice farmers across 15 municipalities.',
                    'department_id' => $departments['Rice Program']->id ?? null,
                    'project_type_id' => $projectTypes['Crop Development']->id ?? null,
                    'project_status_id' => $projectStatuses['On Track']->id ?? null,
                    'budget' => 45000000.00,
                    'start_date' => Carbon::parse('2024-01-15'),
                    'end_date' => Carbon::parse('2025-12-31'),
                    'location_lat' => 8.9475,
                    'location_lng' => 125.5283,
                    'is_public' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'title' => 'Irrigation System Rehabilitation - Agusan del Sur',
                    'description' => 'Rehabilitation and modernization of existing irrigation systems serving 3,200 hectares of rice farmlands in Agusan del Sur. Project includes canal lining, installation of water control structures, repair of headworks, and farmer irrigation association strengthening. Expected to improve water efficiency and increase rice yield by 30%.',
                    'department_id' => $departments['Agricultural Engineering Division']->id ?? null,
                    'project_type_id' => $projectTypes['Infrastructure Development']->id ?? null,
                    'project_status_id' => $projectStatuses['On Track']->id ?? null,
                    'budget' => 78000000.00,
                    'start_date' => Carbon::parse('2023-06-01'),
                    'end_date' => Carbon::parse('2025-05-31'),
                    'location_lat' => 8.5613,
                    'location_lng' => 125.9699,
                    'is_public' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],

                // High-Value Crops Projects
                [
                    'title' => 'CARAGA Cacao Development Program - Surigao del Sur',
                    'description' => 'Large-scale cacao development initiative in Surigao del Sur covering 1,500 hectares. Program provides quality cacao seedlings, technical assistance, fermentation and drying facilities, and market linkages. Partnering with chocolate manufacturers for assured buyback. Target: 800 cacao farmer beneficiaries with sustainable livelihoods.',
                    'department_id' => $departments['High-Value Crops Development Program']->id ?? null,
                    'project_type_id' => $projectTypes['High-Value Crops']->id ?? null,
                    'project_status_id' => $projectStatuses['On Track']->id ?? null,
                    'budget' => 62000000.00,
                    'start_date' => Carbon::parse('2023-03-01'),
                    'end_date' => Carbon::parse('2026-02-28'),
                    'location_lat' => 8.5833,
                    'location_lng' => 126.2333,
                    'is_public' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'title' => 'Coffee Production and Processing Project - Mountain Communities',
                    'description' => 'Arabica and Robusta coffee production enhancement in upland communities of CARAGA. Establishment of 800 hectares of coffee plantations, provision of improved coffee varieties, installation of coffee processing equipment, barista training, and cafe establishment. Promotes sustainable mountain agriculture and eco-tourism.',
                    'department_id' => $departments['High-Value Crops Development Program']->id ?? null,
                    'project_type_id' => $projectTypes['High-Value Crops']->id ?? null,
                    'project_status_id' => $projectStatuses['On Track']->id ?? null,
                    'budget' => 38000000.00,
                    'start_date' => Carbon::parse('2024-02-01'),
                    'end_date' => Carbon::parse('2027-01-31'),
                    'location_lat' => 8.8333,
                    'location_lng' => 125.9333,
                    'is_public' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'title' => 'Abaca Fiber Production Enhancement - Dinagat Islands',
                    'description' => 'Revitalization of abaca industry in Dinagat Islands province. Distribution of tissue-cultured abaca planting materials resistant to bunchy top disease, establishment of decorticating facilities, fiber processing training, and market development. Target: 600 abaca farmers with 1,200 hectares planted area.',
                    'department_id' => $departments['High-Value Crops Development Program']->id ?? null,
                    'project_type_id' => $projectTypes['High-Value Crops']->id ?? null,
                    'project_status_id' => $projectStatuses['On Track']->id ?? null,
                    'budget' => 28000000.00,
                    'start_date' => Carbon::parse('2024-04-01'),
                    'end_date' => Carbon::parse('2026-03-31'),
                    'location_lat' => 10.1286,
                    'location_lng' => 125.6053,
                    'is_public' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'title' => 'Coconut Rehabilitation and Replanting Program - CARAGA Wide',
                    'description' => 'Region-wide coconut farm rehabilitation targeting 5,000 hectares of senile and typhoon-damaged coconut plantations. Provision of hybrid coconut seedlings, intercropping with high-value crops, coco-net installation, farm insurance, and coconut product diversification training. Benefiting 3,500 coconut farmers across all CARAGA provinces.',
                    'department_id' => $departments['High-Value Crops Development Program']->id ?? null,
                    'project_type_id' => $projectTypes['Crop Development']->id ?? null,
                    'project_status_id' => $projectStatuses['On Track']->id ?? null,
                    'budget' => 95000000.00,
                    'start_date' => Carbon::parse('2023-08-01'),
                    'end_date' => Carbon::parse('2026-07-31'),
                    'location_lat' => 9.1667,
                    'location_lng' => 125.8333,
                    'is_public' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],

                // Livestock Projects
                [
                    'title' => 'Cattle Dispersal and Fattening Program - Agusan Valley',
                    'description' => 'Cattle production enhancement through dispersal of 500 heads of quality cattle breeds to farmer cooperatives. Includes construction of cattle housing, pasture development, veterinary services, feeds and supplements provision, and cattle fattening technology training. Market linkages with meat processors established.',
                    'department_id' => $departments['Livestock Development Division']->id ?? null,
                    'project_type_id' => $projectTypes['Livestock Development']->id ?? null,
                    'project_status_id' => $projectStatuses['On Track']->id ?? null,
                    'budget' => 42000000.00,
                    'start_date' => Carbon::parse('2024-01-01'),
                    'end_date' => Carbon::parse('2025-12-31'),
                    'location_lat' => 8.7500,
                    'location_lng' => 125.7500,
                    'is_public' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'title' => 'Swine Production and Biosecurity Enhancement Program',
                    'description' => 'Upgrading of swine production facilities with biosecurity measures to prevent African Swine Fever (ASF). Construction of 50 biosecure piggery units, provision of quality breeding stock, feeds formulation training, waste management systems, and establishment of swine production clusters. Target: 250 small-scale swine raisers.',
                    'department_id' => $departments['Livestock Development Division']->id ?? null,
                    'project_type_id' => $projectTypes['Livestock Development']->id ?? null,
                    'project_status_id' => $projectStatuses['Delayed']->id ?? null,
                    'budget' => 35000000.00,
                    'start_date' => Carbon::parse('2023-10-01'),
                    'end_date' => Carbon::parse('2025-09-30'),
                    'location_lat' => 9.0500,
                    'location_lng' => 125.6500,
                    'is_public' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'title' => 'Goat and Sheep Livelihood Project - Upland Communities',
                    'description' => 'Livelihood program providing goats and sheep to upland farmer families. Distribution of 1,000 heads of improved goat breeds, construction of housing facilities, forage establishment, animal health services, and meat processing training. Focus on women and indigenous peoples participation.',
                    'department_id' => $departments['Livestock Development Division']->id ?? null,
                    'project_type_id' => $projectTypes['Livestock Development']->id ?? null,
                    'project_status_id' => $projectStatuses['On Track']->id ?? null,
                    'budget' => 18000000.00,
                    'start_date' => Carbon::parse('2024-03-01'),
                    'end_date' => Carbon::parse('2025-12-31'),
                    'location_lat' => 8.9000,
                    'location_lng' => 126.0000,
                    'is_public' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],

                // Fisheries Projects
                [
                    'title' => 'Mariculture Development - Surigao del Norte Coastal Areas',
                    'description' => 'Marine cage aquaculture development for milkfish, grouper, and seaweed farming in coastal municipalities of Surigao del Norte. Installation of 100 marine cages, seaweed farming training, post-harvest handling facilities, and market access support. Benefits 300 fisherfolk families.',
                    'department_id' => $departments['Fisheries and Aquatic Resources Division']->id ?? null,
                    'project_type_id' => $projectTypes['Fisheries Development']->id ?? null,
                    'project_status_id' => $projectStatuses['On Track']->id ?? null,
                    'budget' => 32000000.00,
                    'start_date' => Carbon::parse('2023-11-01'),
                    'end_date' => Carbon::parse('2025-10-31'),
                    'location_lat' => 9.7833,
                    'location_lng' => 125.5000,
                    'is_public' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'title' => 'Freshwater Aquaculture and Tilapia Production',
                    'description' => 'Establishment of freshwater fish ponds and tilapia cage culture in inland waters and reservoirs. Construction of 80 fishpond units, tilapia fingerling production, aquaculture technology training, and feeds production. Targets food security and income generation for 200 inland farmer families.',
                    'department_id' => $departments['Fisheries and Aquatic Resources Division']->id ?? null,
                    'project_type_id' => $projectTypes['Fisheries Development']->id ?? null,
                    'project_status_id' => $projectStatuses['On Track']->id ?? null,
                    'budget' => 24000000.00,
                    'start_date' => Carbon::parse('2024-02-15'),
                    'end_date' => Carbon::parse('2025-12-31'),
                    'location_lat' => 8.6500,
                    'location_lng' => 125.8500,
                    'is_public' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],

                // Infrastructure Projects
                [
                    'title' => 'Farm-to-Market Road Network Development - Phase II',
                    'description' => 'Construction and improvement of 45 kilometers of farm-to-market roads connecting agricultural production areas to trading centers. Includes concrete pavement, bridges, drainage systems, and road signage. Facilitates transport of agricultural products and reduces post-harvest losses.',
                    'department_id' => $departments['Agricultural Engineering Division']->id ?? null,
                    'project_type_id' => $projectTypes['Infrastructure Development']->id ?? null,
                    'project_status_id' => $projectStatuses['On Track']->id ?? null,
                    'budget' => 125000000.00,
                    'start_date' => Carbon::parse('2023-07-01'),
                    'end_date' => Carbon::parse('2025-06-30'),
                    'location_lat' => 8.8500,
                    'location_lng' => 125.7000,
                    'is_public' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'title' => 'Regional Rice Processing Complex - Butuan City',
                    'description' => 'Establishment of modern rice processing facility in Butuan City with milling capacity of 5 tons per hour. Includes rice mill equipment, grain dryers, warehouse with 5,000-ton capacity, quality testing laboratory, and packaging equipment. Serves as central processing hub for CARAGA rice production.',
                    'department_id' => $departments['Rice Program']->id ?? null,
                    'project_type_id' => $projectTypes['Post-Harvest Facilities']->id ?? null,
                    'project_status_id' => $projectStatuses['Planning']->id ?? null,
                    'budget' => 85000000.00,
                    'start_date' => Carbon::parse('2025-01-01'),
                    'end_date' => Carbon::parse('2026-12-31'),
                    'location_lat' => 8.9475,
                    'location_lng' => 125.5408,
                    'is_public' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'title' => 'Cold Storage and Pack House Facilities for High-Value Crops',
                    'description' => 'Construction of 5 strategically located cold storage facilities and pack houses for fruits, vegetables, and high-value crops. Each facility includes cold rooms, pre-cooling areas, sorting and grading equipment, packaging materials, and refrigerated transport. Reduces post-harvest losses by 40%.',
                    'department_id' => $departments['Agribusiness and Marketing Assistance Division']->id ?? null,
                    'project_type_id' => $projectTypes['Post-Harvest Facilities']->id ?? null,
                    'project_status_id' => $projectStatuses['Under Review']->id ?? null,
                    'budget' => 68000000.00,
                    'start_date' => Carbon::parse('2025-03-01'),
                    'end_date' => Carbon::parse('2026-12-31'),
                    'location_lat' => 9.0000,
                    'location_lng' => 125.8000,
                    'is_public' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],

                // Farm Mechanization
                [
                    'title' => 'Farm Machinery and Equipment Distribution Program',
                    'description' => 'Distribution of agricultural machinery and equipment to farmer cooperatives and associations. Includes 50 units of hand tractors, 20 units of 4-wheel tractors, rice transplanters, mechanical dryers, and threshers. Includes operator training and maintenance services. Increases farming efficiency and reduces labor costs.',
                    'department_id' => $departments['Regional Agricultural and Biosystems Engineering Division']->id ?? null,
                    'project_type_id' => $projectTypes['Farm Mechanization']->id ?? null,
                    'project_status_id' => $projectStatuses['On Track']->id ?? null,
                    'budget' => 72000000.00,
                    'start_date' => Carbon::parse('2024-01-15'),
                    'end_date' => Carbon::parse('2025-06-30'),
                    'location_lat' => 9.0500,
                    'location_lng' => 125.7500,
                    'is_public' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],

                // Capacity Building and Training
                [
                    'title' => 'Farmers Field School and Agricultural Extension Program',
                    'description' => 'Comprehensive training and capacity building program for 5,000 farmers across CARAGA. Establishment of 100 Farmer Field Schools covering crop production, pest management, organic farming, climate-smart agriculture, and agribusiness. Includes training materials, demonstration farms, and certified trainers.',
                    'department_id' => $departments['Agricultural Extension Services']->id ?? null,
                    'project_type_id' => $projectTypes['Capacity Building']->id ?? null,
                    'project_status_id' => $projectStatuses['On Track']->id ?? null,
                    'budget' => 28000000.00,
                    'start_date' => Carbon::parse('2023-09-01'),
                    'end_date' => Carbon::parse('2025-08-31'),
                    'location_lat' => 9.1000,
                    'location_lng' => 125.8000,
                    'is_public' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],

                // Sustainable Agriculture
                [
                    'title' => 'Organic Agriculture Development and Certification Program',
                    'description' => 'Promotion of organic farming practices and organic certification support in CARAGA. Training on organic production methods, provision of organic inputs, establishment of composting facilities, organic certification assistance, and organic market development. Target: 800 organic farmers with 2,000 hectares certified organic farms.',
                    'department_id' => $departments['Organic Agriculture Program']->id ?? null,
                    'project_type_id' => $projectTypes['Sustainable Agriculture']->id ?? null,
                    'project_status_id' => $projectStatuses['On Track']->id ?? null,
                    'budget' => 32000000.00,
                    'start_date' => Carbon::parse('2024-01-01'),
                    'end_date' => Carbon::parse('2026-12-31'),
                    'location_lat' => 8.9500,
                    'location_lng' => 125.9000,
                    'is_public' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],

                // Research and Development
                [
                    'title' => 'Climate-Resilient Crop Varieties Research and Development',
                    'description' => 'Research program for developing and testing climate-resilient crop varieties adapted to CARAGA conditions. Includes variety trials, seed production, farmer participatory research, and demonstration farms. Focus on drought-tolerant rice, flood-resistant corn, and pest-resistant vegetables. Partnership with PhilRice and universities.',
                    'department_id' => $departments['Research and Development Division']->id ?? null,
                    'project_type_id' => $projectTypes['Research and Development']->id ?? null,
                    'project_status_id' => $projectStatuses['On Track']->id ?? null,
                    'budget' => 22000000.00,
                    'start_date' => Carbon::parse('2023-05-01'),
                    'end_date' => Carbon::parse('2026-04-30'),
                    'location_lat' => 8.9000,
                    'location_lng' => 125.8500,
                    'is_public' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ];

            foreach ($projects as $projectData) {
                Project::create($projectData);
            }
        });

        $this->command->info('20 realistic CARAGA agricultural projects seeded successfully!');
    }
}

