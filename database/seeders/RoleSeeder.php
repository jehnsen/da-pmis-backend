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
     * Creates roles for Provincial LGU Governance Platform (RA 7160 compliant)
     */
    public function run(): void
    {
        DB::transaction(function () {
            // Define roles (RA 7160 LGU Governance Hierarchy)
            $roles = [
                [
                    'name' => 'System Administrator',
                    'description' => 'Full system access with all permissions. Manages system configuration, users, and overall system administration.',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'name' => 'Provincial Governor',
                    'description' => 'Provincial Governor - Final approval authority for all provincial projects under RA 7160. Oversees all LGU sectors (Social Services, Economic Services, Infrastructure, General Public Services).',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'name' => 'Provincial Planning Officer (PPDO)',
                    'description' => 'Provincial Planning & Development Office - Technical review of projects at provincial level. Validates alignment with Provincial Development Plan and LDIP.',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'name' => 'Provincial Officer',
                    'description' => 'Provincial sector officers managing specific LGU sectors (SS/ES/IEM/GPS). Coordinates sector-wide programs and monitors implementation.',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'name' => 'Municipal Planning Officer (MPDO)',
                    'description' => 'Municipal Planning & Development Office - Validates projects submitted from barangays. Ensures alignment with Municipal Development Plan and Annual Investment Plan (AIP).',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'name' => 'Municipal Officer',
                    'description' => 'Municipal-level officers managing sector programs and coordinating with barangays on project implementation.',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'name' => 'Barangay Development Council Officer',
                    'description' => 'Barangay Development Council (BDC) - Entry point for project proposals under RA 7160. Reviews Barangay Development Plan (BDP) submissions and community-based projects.',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'name' => 'Sector Head',
                    'description' => 'LGU Sector heads managing specific governance sectors: Social Services (SS), Economic Services (ES), Infrastructure & Environmental Management (IEM), or General Public Services (GPS).',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'name' => 'Project Manager',
                    'description' => 'Manages specific projects including team coordination, progress tracking, disbursement monitoring, and outcome reporting.',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'name' => 'Technical Officer',
                    'description' => 'Technical staff responsible for project implementation, data collection, beneficiary coordination, and field monitoring.',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'name' => 'Data Encoder',
                    'description' => 'Staff responsible for data entry, document upload, and information management across all LGU sectors.',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'name' => 'Public Viewer',
                    'description' => 'Public access role with view-only permissions for published information, transparency reports, and governance metrics.',
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

            case 'Provincial Governor':
                // All permissions except system administration (final approval authority)
                $permissions = $allPermissions->filter(function ($perm) {
                    return $perm->module !== 'System';
                });
                $role->permissions()->attach($permissions);
                break;

            case 'Provincial Planning Officer (PPDO)':
                // Provincial technical review and approval authority
                $permissions = $allPermissions->filter(function ($perm) {
                    return in_array($perm->slug, [
                        'projects.view', 'projects.approve', 'projects.reject', 'projects.request_changes',
                        'projects.create', 'projects.edit', 'projects.manage_team',
                        'documents.view', 'documents.upload', 'documents.edit',
                        'reports.view', 'reports.generate', 'reports.export',
                        'news.view', 'news.create', 'news.edit',
                        'departments.view', 'sectors.view',
                        'dashboard.view', 'analytics.view',
                    ]);
                });
                $role->permissions()->attach($permissions);
                break;

            case 'Provincial Officer':
                // Provincial sector management
                $permissions = $allPermissions->filter(function ($perm) {
                    return in_array($perm->slug, [
                        'projects.view', 'projects.create', 'projects.edit', 'projects.manage_team',
                        'documents.view', 'documents.upload', 'documents.edit',
                        'reports.view', 'reports.generate', 'reports.export',
                        'news.view', 'news.create',
                        'departments.view', 'sectors.view',
                        'dashboard.view', 'analytics.view',
                    ]);
                });
                $role->permissions()->attach($permissions);
                break;

            case 'Municipal Planning Officer (MPDO)':
                // Municipal-level validation and approval authority
                $permissions = $allPermissions->filter(function ($perm) {
                    return in_array($perm->slug, [
                        'projects.view', 'projects.approve', 'projects.reject', 'projects.request_changes',
                        'projects.create', 'projects.edit', 'projects.manage_team',
                        'documents.view', 'documents.upload', 'documents.edit',
                        'reports.view', 'reports.generate',
                        'news.view', 'news.create',
                        'departments.view', 'sectors.view',
                        'dashboard.view', 'analytics.view',
                    ]);
                });
                $role->permissions()->attach($permissions);
                break;

            case 'Municipal Officer':
                // Municipal operations
                $permissions = $allPermissions->filter(function ($perm) {
                    return in_array($perm->slug, [
                        'projects.view', 'projects.create', 'projects.edit', 'projects.manage_team',
                        'documents.view', 'documents.upload',
                        'reports.view', 'reports.generate',
                        'news.view', 'news.create',
                        'departments.view', 'sectors.view',
                        'dashboard.view',
                    ]);
                });
                $role->permissions()->attach($permissions);
                break;

            case 'Barangay Development Council Officer':
                // Barangay-level project submission and review
                $permissions = $allPermissions->filter(function ($perm) {
                    return in_array($perm->slug, [
                        'projects.view', 'projects.create', 'projects.edit', 'projects.approve',
                        'documents.view', 'documents.upload',
                        'reports.view', 'reports.generate',
                        'news.view',
                        'dashboard.view',
                    ]);
                });
                $role->permissions()->attach($permissions);
                break;

            case 'Sector Head':
                // LGU sector management
                $permissions = $allPermissions->filter(function ($perm) {
                    return in_array($perm->slug, [
                        'projects.view', 'projects.create', 'projects.edit', 'projects.manage_team',
                        'documents.view', 'documents.upload', 'documents.edit',
                        'reports.view', 'reports.generate', 'reports.export',
                        'news.view', 'news.create', 'news.edit',
                        'departments.view', 'sectors.view',
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

            case 'Technical Officer':
                // Field data collection and technical support
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
