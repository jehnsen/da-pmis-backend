<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use App\Models\Department;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Creates sample users for DA-CARAGA PMIS with hashed passwords
     * Default password for all users: Password123!
     */
    public function run(): void
    {
        DB::transaction(function () {
            // Get roles and departments
            $roles = Role::all()->keyBy('name');
            $departments = Department::all()->keyBy('name');

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

                // Regional Director
                [
                    'full_name' => 'Maria Santos-Rodriguez',
                    'username' => 'mrodriguez',
                    'email' => 'director@da-caraga.gov.ph',
                    'password' => Hash::make('Password123!'),
                    'role_id' => $roles['Regional Director']->id ?? null,
                    'department_id' => $departments['Office of the Regional Executive Director']->id ?? null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],

                // Provincial Officers (Approval Workflow Level 2)
                [
                    'full_name' => 'Carlos Mendez-Silva',
                    'username' => 'provincial_agusannorte',
                    'email' => 'provincial.agusannorte@da-caraga.gov.ph',
                    'password' => Hash::make('Password123!'),
                    'role_id' => $roles['Provincial Officer']->id ?? null,
                    'department_id' => $departments['Office of the Regional Executive Director']->id ?? null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'full_name' => 'Sofia Ramirez-Torres',
                    'username' => 'provincial_agusansur',
                    'email' => 'provincial.agusansur@da-caraga.gov.ph',
                    'password' => Hash::make('Password123!'),
                    'role_id' => $roles['Provincial Officer']->id ?? null,
                    'department_id' => $departments['Office of the Regional Executive Director']->id ?? null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'full_name' => 'Benjamin Cruz-Flores',
                    'username' => 'provincial_surigaonorte',
                    'email' => 'provincial.surigaonorte@da-caraga.gov.ph',
                    'password' => Hash::make('Password123!'),
                    'role_id' => $roles['Provincial Officer']->id ?? null,
                    'department_id' => $departments['Office of the Regional Executive Director']->id ?? null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],

                // Municipal Officers (Approval Workflow Level 1)
                [
                    'full_name' => 'Rafael Santos-Aquino',
                    'username' => 'municipal_butuan',
                    'email' => 'municipal.butuan@da-caraga.gov.ph',
                    'password' => Hash::make('Password123!'),
                    'role_id' => $roles['Municipal Officer']->id ?? null,
                    'department_id' => $departments['Field Operations Division']->id ?? null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'full_name' => 'Isabella Garcia-Reyes',
                    'username' => 'municipal_cabadbaran',
                    'email' => 'municipal.cabadbaran@da-caraga.gov.ph',
                    'password' => Hash::make('Password123!'),
                    'role_id' => $roles['Municipal Officer']->id ?? null,
                    'department_id' => $departments['Field Operations Division']->id ?? null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'full_name' => 'Gabriel Fernandez-Lopez',
                    'username' => 'municipal_surigao',
                    'email' => 'municipal.surigao@da-caraga.gov.ph',
                    'password' => Hash::make('Password123!'),
                    'role_id' => $roles['Municipal Officer']->id ?? null,
                    'department_id' => $departments['Field Operations Division']->id ?? null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],

                // Field Officers (Project Submission & Field Operations)
                [
                    'full_name' => 'Diana Rodriguez-Castro',
                    'username' => 'field_bunawan',
                    'email' => 'field.bunawan@da-caraga.gov.ph',
                    'password' => Hash::make('Password123!'),
                    'role_id' => $roles['Field Officer']->id ?? null,
                    'department_id' => $departments['Field Operations Division']->id ?? null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'full_name' => 'Marco Villa-Santos',
                    'username' => 'field_bayugan',
                    'email' => 'field.bayugan@da-caraga.gov.ph',
                    'password' => Hash::make('Password123!'),
                    'role_id' => $roles['Field Officer']->id ?? null,
                    'department_id' => $departments['Field Operations Division']->id ?? null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],

                // Department Heads
                [
                    'full_name' => 'Roberto Villanueva',
                    'username' => 'rvillanueva',
                    'email' => 'rvillanueva@da-caraga.gov.ph',
                    'password' => Hash::make('Password123!'),
                    'role_id' => $roles['Department Head']->id ?? null,
                    'department_id' => $departments['Rice Program']->id ?? null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'full_name' => 'Carmen Reyes-Lopez',
                    'username' => 'clopez',
                    'email' => 'clopez@da-caraga.gov.ph',
                    'password' => Hash::make('Password123!'),
                    'role_id' => $roles['Department Head']->id ?? null,
                    'department_id' => $departments['High-Value Crops Development Program']->id ?? null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'full_name' => 'Antonio Mendoza',
                    'username' => 'amendoza',
                    'email' => 'amendoza@da-caraga.gov.ph',
                    'password' => Hash::make('Password123!'),
                    'role_id' => $roles['Department Head']->id ?? null,
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

                // Agricultural Technicians
                [
                    'full_name' => 'Jose Ramos',
                    'username' => 'jramos',
                    'email' => 'jramos@da-caraga.gov.ph',
                    'password' => Hash::make('Password123!'),
                    'role_id' => $roles['Agricultural Technician']->id ?? null,
                    'department_id' => $departments['Agricultural Extension Services']->id ?? null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'full_name' => 'Luisa Bautista',
                    'username' => 'lbautista',
                    'email' => 'lbautista@da-caraga.gov.ph',
                    'password' => Hash::make('Password123!'),
                    'role_id' => $roles['Agricultural Technician']->id ?? null,
                    'department_id' => $departments['Field Operations Division']->id ?? null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'full_name' => 'Miguel Torres',
                    'username' => 'mtorres',
                    'email' => 'mtorres@da-caraga.gov.ph',
                    'password' => Hash::make('Password123!'),
                    'role_id' => $roles['Agricultural Technician']->id ?? null,
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

