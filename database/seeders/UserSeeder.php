<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use App\Models\Department;
use App\Models\Municipality;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Creates sample users for Provincial LGU Governance Platform (RA 7160 compliant)
     *
     * RA 7160 Approval Workflow:
     * - Level 0: Barangay Development Council (BDC) - Project drafting/proposal entry point
     * - Level 1: Municipal Planning & Development Office (MPDO) - Municipal validation
     * - Level 2: Provincial Planning & Development Office (PPDO) - Provincial technical review
     * - Level 3: Provincial Governor - Final approval authority
     *
     * Default password for all users: Password123!
     */
    public function run(): void
    {
        DB::transaction(function () {
            // Get roles and departments
            $roles = Role::all()->keyBy('name');
            $departments = Department::all()->keyBy('name');

            // Get municipalities for RA 7160 territorial jurisdiction assignment
            $municipalities = Municipality::all()->keyBy('name');

            $users = [
                // System Administrator
                [
                    'full_name' => 'Juan Dela Cruz',
                    'username' => 'admin',
                    'email' => 'admin@da-caraga.gov.ph',
                    'password' => Hash::make('Password123!'),
                    'role_id' => $roles['System Administrator']->id ?? null,
                    'department_id' => $departments['Office of the Regional Executive Director']->id ?? null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],

                // Provincial Governor (RA 7160 Approval Level 3 - Final Authority)
                [
                    'full_name' => 'Maria Santos-Rodriguez',
                    'username' => 'governor_caraga',
                    'email' => 'governor@caraga.gov.ph',
                    'password' => Hash::make('Password123!'),
                    'role_id' => $roles['Provincial Governor']->id ?? null,
                    'department_id' => $departments['Office of the Regional Executive Director']->id ?? null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],

                // Provincial Planning & Development Officers (RA 7160 Approval Level 2 - PPDO)
                [
                    'full_name' => 'Carlos Mendez-Silva',
                    'username' => 'ppdo_agusannorte',
                    'email' => 'ppdo.agusannorte@caraga.gov.ph',
                    'password' => Hash::make('Password123!'),
                    'role_id' => $roles['Provincial Planning Officer (PPDO)']->id ?? null,
                    'department_id' => $departments['Planning, Monitoring and Evaluation Division']->id ?? null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'full_name' => 'Sofia Ramirez-Torres',
                    'username' => 'ppdo_agusansur',
                    'email' => 'ppdo.agusansur@caraga.gov.ph',
                    'password' => Hash::make('Password123!'),
                    'role_id' => $roles['Provincial Planning Officer (PPDO)']->id ?? null,
                    'department_id' => $departments['Planning, Monitoring and Evaluation Division']->id ?? null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'full_name' => 'Benjamin Cruz-Flores',
                    'username' => 'ppdo_surigaonorte',
                    'email' => 'ppdo.surigaonorte@caraga.gov.ph',
                    'password' => Hash::make('Password123!'),
                    'role_id' => $roles['Provincial Planning Officer (PPDO)']->id ?? null,
                    'department_id' => $departments['Planning, Monitoring and Evaluation Division']->id ?? null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],

                // Municipal Planning & Development Officers (RA 7160 Approval Level 1 - MPDO)
                // RA 7160 Territorial Jurisdiction: MPDOs can only approve projects from their municipality
                [
                    'full_name' => 'Rafael Santos-Aquino',
                    'username' => 'mpdo_butuan',
                    'email' => 'mpdo.butuan@caraga.gov.ph',
                    'password' => Hash::make('Password123!'),
                    'role_id' => $roles['Municipal Planning Officer (MPDO)']->id ?? null,
                    'department_id' => $departments['Planning, Monitoring and Evaluation Division']->id ?? null,
                    'municipality_id' => $municipalities['Butuan City']->id ?? null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'full_name' => 'Isabella Garcia-Reyes',
                    'username' => 'mpdo_cabadbaran',
                    'email' => 'mpdo.cabadbaran@caraga.gov.ph',
                    'password' => Hash::make('Password123!'),
                    'role_id' => $roles['Municipal Planning Officer (MPDO)']->id ?? null,
                    'department_id' => $departments['Planning, Monitoring and Evaluation Division']->id ?? null,
                    'municipality_id' => $municipalities['Cabadbaran City']->id ?? null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'full_name' => 'Gabriel Fernandez-Lopez',
                    'username' => 'mpdo_surigao',
                    'email' => 'mpdo.surigao@caraga.gov.ph',
                    'password' => Hash::make('Password123!'),
                    'role_id' => $roles['Municipal Planning Officer (MPDO)']->id ?? null,
                    'department_id' => $departments['Planning, Monitoring and Evaluation Division']->id ?? null,
                    'municipality_id' => $municipalities['Surigao City']->id ?? null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],

                // Barangay Development Council Officers (RA 7160 Level 0 - Project Drafting/Proposal Entry Point)
                // RA 7160 Territorial Jurisdiction: BDC officers can only approve projects from their municipality
                [
                    'full_name' => 'Diana Rodriguez-Castro',
                    'username' => 'bdc_bunawan',
                    'email' => 'bdc.bunawan@caraga.gov.ph',
                    'password' => Hash::make('Password123!'),
                    'role_id' => $roles['Barangay Development Council Officer']->id ?? null,
                    'department_id' => $departments['Field Operations Division']->id ?? null,
                    'municipality_id' => $municipalities['Bunawan']->id ?? null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'full_name' => 'Marco Villa-Santos',
                    'username' => 'bdc_bayugan',
                    'email' => 'bdc.bayugan@caraga.gov.ph',
                    'password' => Hash::make('Password123!'),
                    'role_id' => $roles['Barangay Development Council Officer']->id ?? null,
                    'department_id' => $departments['Field Operations Division']->id ?? null,
                    'municipality_id' => $municipalities['Bayugan']->id ?? null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'full_name' => 'Anna Marie Gonzales',
                    'username' => 'bdc_prosperidad',
                    'email' => 'bdc.prosperidad@caraga.gov.ph',
                    'password' => Hash::make('Password123!'),
                    'role_id' => $roles['Barangay Development Council Officer']->id ?? null,
                    'department_id' => $departments['Field Operations Division']->id ?? null,
                    'municipality_id' => $municipalities['Prosperidad']->id ?? null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'full_name' => 'Ricardo Magpantay',
                    'username' => 'bdc_sanfrancisco',
                    'email' => 'bdc.sanfrancisco@caraga.gov.ph',
                    'password' => Hash::make('Password123!'),
                    'role_id' => $roles['Barangay Development Council Officer']->id ?? null,
                    'department_id' => $departments['Field Operations Division']->id ?? null,
                    'municipality_id' => $municipalities['San Francisco']->id ?? null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],

                // LGU Sector Heads (Managing SS/ES/IEM/GPS sectors)
                [
                    'full_name' => 'Roberto Villanueva',
                    'username' => 'rvillanueva',
                    'email' => 'rvillanueva@da-caraga.gov.ph',
                    'password' => Hash::make('Password123!'),
                    'role_id' => $roles['Sector Head']->id ?? null,
                    'department_id' => $departments['Rice Program']->id ?? null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'full_name' => 'Carmen Reyes-Lopez',
                    'username' => 'clopez',
                    'email' => 'clopez@da-caraga.gov.ph',
                    'password' => Hash::make('Password123!'),
                    'role_id' => $roles['Sector Head']->id ?? null,
                    'department_id' => $departments['High-Value Crops Development Program']->id ?? null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'full_name' => 'Antonio Mendoza',
                    'username' => 'amendoza',
                    'email' => 'amendoza@da-caraga.gov.ph',
                    'password' => Hash::make('Password123!'),
                    'role_id' => $roles['Sector Head']->id ?? null,
                    'department_id' => $departments['Livestock Development Division']->id ?? null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],

                // Project Managers
                [
                    'full_name' => 'Elena Garcia-Cruz',
                    'username' => 'ecruz',
                    'email' => 'ecruz@da-caraga.gov.ph',
                    'password' => Hash::make('Password123!'),
                    'role_id' => $roles['Project Manager']->id ?? null,
                    'department_id' => $departments['Field Operations Division']->id ?? null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'full_name' => 'Ferdinand Aquino',
                    'username' => 'faquino',
                    'email' => 'faquino@da-caraga.gov.ph',
                    'password' => Hash::make('Password123!'),
                    'role_id' => $roles['Project Manager']->id ?? null,
                    'department_id' => $departments['Agricultural Engineering Division']->id ?? null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'full_name' => 'Rosalinda Fernandez',
                    'username' => 'rfernandez',
                    'email' => 'rfernandez@da-caraga.gov.ph',
                    'password' => Hash::make('Password123!'),
                    'role_id' => $roles['Project Manager']->id ?? null,
                    'department_id' => $departments['Fisheries and Aquatic Resources Division']->id ?? null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],

                // Technical Officers (Field implementation & data collection)
                [
                    'full_name' => 'Jose Ramos',
                    'username' => 'jramos',
                    'email' => 'jramos@da-caraga.gov.ph',
                    'password' => Hash::make('Password123!'),
                    'role_id' => $roles['Technical Officer']->id ?? null,
                    'department_id' => $departments['Agricultural Extension Services']->id ?? null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'full_name' => 'Luisa Bautista',
                    'username' => 'lbautista',
                    'email' => 'lbautista@da-caraga.gov.ph',
                    'password' => Hash::make('Password123!'),
                    'role_id' => $roles['Technical Officer']->id ?? null,
                    'department_id' => $departments['Field Operations Division']->id ?? null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'full_name' => 'Miguel Torres',
                    'username' => 'mtorres',
                    'email' => 'mtorres@da-caraga.gov.ph',
                    'password' => Hash::make('Password123!'),
                    'role_id' => $roles['Technical Officer']->id ?? null,
                    'department_id' => $departments['Organic Agriculture Program']->id ?? null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],

                // Data Encoders
                [
                    'full_name' => 'Patricia Santos',
                    'username' => 'psantos',
                    'email' => 'psantos@da-caraga.gov.ph',
                    'password' => Hash::make('Password123!'),
                    'role_id' => $roles['Data Encoder']->id ?? null,
                    'department_id' => $departments['Planning, Monitoring and Evaluation Division']->id ?? null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'full_name' => 'Ricardo Castillo',
                    'username' => 'rcastillo',
                    'email' => 'rcastillo@da-caraga.gov.ph',
                    'password' => Hash::make('Password123!'),
                    'role_id' => $roles['Data Encoder']->id ?? null,
                    'department_id' => $departments['Planning, Monitoring and Evaluation Division']->id ?? null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],

                // Public Viewer (for testing)
                [
                    'full_name' => 'Public User',
                    'username' => 'public',
                    'email' => 'public@example.com',
                    'password' => Hash::make('Password123!'),
                    'role_id' => $roles['Public Viewer']->id ?? null,
                    'department_id' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ];

            foreach ($users as $userData) {
                User::create($userData);
            }
        });

        $this->command->info('Users seeded successfully!');
        $this->command->warn('Default password for all users: Password123!');
    }
}

