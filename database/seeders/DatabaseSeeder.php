<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * Seeds comprehensive data for DA-CARAGA PMIS system
     * Run with: php artisan db:seed
     */
    public function run(): void
    {
        $this->command->info('Starting CARAGA PMIS database seeding...');
        $this->command->newLine();

        // Seed in proper order (respecting foreign key dependencies)
        $this->call([
            // 1. Base Configuration Data (no dependencies)
            ProjectTypeSeeder::class,
            ProjectStatusSeeder::class,
            DocumentCategorySeeder::class,
            RegionSeeder::class,

            // 2. User Management (depends on nothing)
            PermissionSeeder::class,
            RoleSeeder::class,
            DepartmentSeeder::class,

            // 3. Users (depends on roles and departments)
            UserSeeder::class,

            // 4. Main Content (depends on types, statuses, departments, regions)
            ProjectSeeder::class,
            CropProductionSeeder::class,
            LivestockStatisticSeeder::class,

            // 5. Content that depends on users
            NewsUpdateSeeder::class,
            DocumentSeeder::class,
        ]);

        $this->command->newLine();
        $this->command->info('=========================================');
        $this->command->info('CARAGA PMIS Database Seeding Completed!');
        $this->command->info('=========================================');
        $this->command->newLine();
        $this->command->info('Summary of seeded data:');
        $this->command->info('- 10 Project Types');
        $this->command->info('- 7 Project Statuses');
        $this->command->info('- 10 Document Categories');
        $this->command->info('- 6 Regions (CARAGA + 5 provinces)');
        $this->command->info('- 29 Permissions');
        $this->command->info('- 7 Roles with assigned permissions');
        $this->command->info('- 15 Departments');
        $this->command->info('- 15 Users (various roles)');
        $this->command->info('- 20 Agricultural Projects');
        $this->command->info('- Crop production data (2023-2025)');
        $this->command->info('- Livestock statistics (2023-2025)');
        $this->command->info('- 15 News Updates');
        $this->command->info('- 20 Documents');
        $this->command->newLine();
        $this->command->warn('Default password for all users: Password123!');
        $this->command->info('Login with: admin / Password123!');
        $this->command->newLine();
    }
}

