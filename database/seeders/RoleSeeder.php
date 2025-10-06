<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Support\Facades\DB;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Creates roles for CARAGA DA-PMIS and assigns appropriate permissions
     */
    public function run(): void
    {
        DB::transaction(function () {
            // Define roles
            $roles = [
                [
                    'name' => 'System Administrator',
                    'description' => 'Full system access with all permissions. Manages system configuration, users, and overall system administration.',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'name' => 'Regional Director',
                    'description' => 'CARAGA Regional Director with oversight of all agricultural projects and programs. Full access to view and manage regional data.',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'name' => 'Department Head',
                    'description' => 'Head of agricultural department divisions with authority over specific programs and projects within their domain.',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'name' => 'Project Manager',
                    'description' => 'Manages specific agricultural projects including team coordination, progress tracking, and reporting.',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'name' => 'Agricultural Technician',
                    'description' => 'Field staff responsible for project implementation, data collection, and farmer coordination.',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'name' => 'Data Encoder',
                    'description' => 'Staff responsible for data entry, document upload, and information management.',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'name' => 'Public Viewer',
                    'description' => 'Public access role with view-only permissions for published information and news.',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ];

            // Create roles
            foreach ($roles as $roleData) {
                $role = Role::create($roleData);

                // Assign permissions based on role
                $this->assignPermissions($role);
            }
        });

        $this->command->info('Roles and permissions seeded successfully!');
    }

    /**
     * Assign permissions to roles
     */
    private function assignPermissions(Role $role): void
    {
        $allPermissions = Permission::all();

        switch ($role->name) {
            case 'System Administrator':
                // Full access to everything
                $role->permissions()->attach($allPermissions);
                break;

            case 'Regional Director':
                // All permissions except system administration
                $permissions = $allPermissions->filter(function ($perm) {
                    return $perm->module !== 'System';
                });
                $role->permissions()->attach($permissions);
                break;

            case 'Department Head':
                // Manage projects, view reports, manage team
                $permissions = $allPermissions->filter(function ($perm) {
                    return in_array($perm->slug, [
                        'projects.view', 'projects.create', 'projects.edit', 'projects.manage_team',
                        'documents.view', 'documents.upload', 'documents.edit',
                        'reports.view', 'reports.generate', 'reports.export',
                        'news.view', 'news.create', 'news.edit',
                        'departments.view',
                        'dashboard.view', 'analytics.view',
                    ]);
                });
                $role->permissions()->attach($permissions);
                break;

            case 'Project Manager':
                // Manage assigned projects and teams
                $permissions = $allPermissions->filter(function ($perm) {
                    return in_array($perm->slug, [
                        'projects.view', 'projects.edit', 'projects.manage_team',
                        'documents.view', 'documents.upload',
                        'reports.view', 'reports.generate',
                        'news.view',
                        'dashboard.view',
                    ]);
                });
                $role->permissions()->attach($permissions);
                break;

            case 'Agricultural Technician':
                // Field data collection and reporting
                $permissions = $allPermissions->filter(function ($perm) {
                    return in_array($perm->slug, [
                        'projects.view',
                        'documents.view', 'documents.upload',
                        'reports.view',
                        'news.view',
                        'dashboard.view',
                    ]);
                });
                $role->permissions()->attach($permissions);
                break;

            case 'Data Encoder':
                // Data entry and document management
                $permissions = $allPermissions->filter(function ($perm) {
                    return in_array($perm->slug, [
                        'projects.view',
                        'documents.view', 'documents.upload', 'documents.edit',
                        'reports.view',
                        'news.view', 'news.create',
                        'dashboard.view',
                    ]);
                });
                $role->permissions()->attach($permissions);
                break;

            case 'Public Viewer':
                // View only public information
                $permissions = $allPermissions->filter(function ($perm) {
                    return in_array($perm->slug, [
                        'projects.view',
                        'documents.view',
                        'news.view',
                    ]);
                });
                $role->permissions()->attach($permissions);
                break;
        }
    }
}
