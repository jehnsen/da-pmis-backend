<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permission;
use Illuminate\Support\Facades\DB;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Creates comprehensive permissions for Provincial LGU Governance Platform (RA 7160)
     */
    public function run(): void
    {
        DB::transaction(function () {
            $permissions = [
                // Project Management Permissions
                ['name' => 'View Projects', 'slug' => 'projects.view', 'description' => 'View project listings and details across all LGU sectors', 'module' => 'Projects'],
                ['name' => 'Create Projects', 'slug' => 'projects.create', 'description' => 'Create new projects in LGU sectors (SS/ES/IEM/GPS)', 'module' => 'Projects'],
                ['name' => 'Edit Projects', 'slug' => 'projects.edit', 'description' => 'Edit existing project information', 'module' => 'Projects'],
                ['name' => 'Delete Projects', 'slug' => 'projects.delete', 'description' => 'Delete projects from the system', 'module' => 'Projects'],
                ['name' => 'Manage Project Team', 'slug' => 'projects.manage_team', 'description' => 'Add/remove project team members', 'module' => 'Projects'],

                // Project Approval Workflow Permissions (RA 7160)
                ['name' => 'Approve Projects (Barangay)', 'slug' => 'projects.approve', 'description' => 'Approve projects at Barangay Development Council level', 'module' => 'Projects'],
                ['name' => 'Approve Projects (Municipal)', 'slug' => 'projects.approve.municipal', 'description' => 'Approve projects at MPDO (Municipal Planning) level', 'module' => 'Projects'],
                ['name' => 'Approve Projects (Provincial)', 'slug' => 'projects.approve.provincial', 'description' => 'Approve projects at PPDO (Provincial Planning) level', 'module' => 'Projects'],
                ['name' => 'Approve Projects (Governor)', 'slug' => 'projects.approve.governor', 'description' => 'Final approval by Provincial Governor', 'module' => 'Projects'],
                ['name' => 'Reject Projects', 'slug' => 'projects.reject', 'description' => 'Reject projects with reasons at any approval level', 'module' => 'Projects'],
                ['name' => 'Request Changes', 'slug' => 'projects.request_changes', 'description' => 'Request changes to projects in approval workflow', 'module' => 'Projects'],
                ['name' => 'Submit for Approval', 'slug' => 'projects.submit', 'description' => 'Submit projects to Barangay Development Council for approval', 'module' => 'Projects'],
                ['name' => 'View Approval History', 'slug' => 'projects.view_approval_history', 'description' => 'View project approval history and audit trail', 'module' => 'Projects'],

                // LGU Sector Management Permissions (NEW)
                ['name' => 'View LGU Sectors', 'slug' => 'sectors.view', 'description' => 'View LGU sectors (Social Services, Economic Services, Infrastructure, General)', 'module' => 'Sectors'],
                ['name' => 'Manage LGU Sectors', 'slug' => 'sectors.manage', 'description' => 'Create, edit, delete LGU sectors', 'module' => 'Sectors'],
                ['name' => 'View Sector Analytics', 'slug' => 'sectors.analytics', 'description' => 'View sector-wise performance analytics', 'module' => 'Sectors'],

                // Document Management Permissions
                ['name' => 'View Documents', 'slug' => 'documents.view', 'description' => 'View document library', 'module' => 'Documents'],
                ['name' => 'Upload Documents', 'slug' => 'documents.upload', 'description' => 'Upload project documents, BDP, AIP, LDIP', 'module' => 'Documents'],
                ['name' => 'Edit Documents', 'slug' => 'documents.edit', 'description' => 'Edit document metadata', 'module' => 'Documents'],
                ['name' => 'Delete Documents', 'slug' => 'documents.delete', 'description' => 'Delete documents', 'module' => 'Documents'],

                // Reports & Statistics Permissions
                ['name' => 'View Reports', 'slug' => 'reports.view', 'description' => 'View statistical reports and governance metrics', 'module' => 'Reports'],
                ['name' => 'Generate Reports', 'slug' => 'reports.generate', 'description' => 'Generate custom reports for COA/DBM/NEDA compliance', 'module' => 'Reports'],
                ['name' => 'Export Data', 'slug' => 'reports.export', 'description' => 'Export data and reports for transparency', 'module' => 'Reports'],
                ['name' => 'View COA Metrics', 'slug' => 'reports.coa_metrics', 'description' => 'View COA compliance metrics', 'module' => 'Reports'],
                ['name' => 'View DBM Metrics', 'slug' => 'reports.dbm_metrics', 'description' => 'View DBM budget utilization metrics', 'module' => 'Reports'],
                ['name' => 'View NEDA Metrics', 'slug' => 'reports.neda_metrics', 'description' => 'View NEDA development plan compliance', 'module' => 'Reports'],

                // News & Updates Permissions
                ['name' => 'View News', 'slug' => 'news.view', 'description' => 'View news updates and public announcements', 'module' => 'News'],
                ['name' => 'Create News', 'slug' => 'news.create', 'description' => 'Create news articles for public transparency', 'module' => 'News'],
                ['name' => 'Edit News', 'slug' => 'news.edit', 'description' => 'Edit news articles', 'module' => 'News'],
                ['name' => 'Delete News', 'slug' => 'news.delete', 'description' => 'Delete news articles', 'module' => 'News'],
                ['name' => 'Publish News', 'slug' => 'news.publish', 'description' => 'Publish/unpublish news articles', 'module' => 'News'],

                // User Management Permissions
                ['name' => 'View Users', 'slug' => 'users.view', 'description' => 'View user listings across all LGU offices', 'module' => 'Users'],
                ['name' => 'Create Users', 'slug' => 'users.create', 'description' => 'Create new user accounts for LGU staff', 'module' => 'Users'],
                ['name' => 'Edit Users', 'slug' => 'users.edit', 'description' => 'Edit user information', 'module' => 'Users'],
                ['name' => 'Delete Users', 'slug' => 'users.delete', 'description' => 'Delete user accounts', 'module' => 'Users'],
                ['name' => 'Manage Roles', 'slug' => 'users.manage_roles', 'description' => 'Assign/modify user roles (Barangay/MPDO/PPDO/Governor)', 'module' => 'Users'],

                // Department Management Permissions (Legacy - kept for backward compatibility)
                ['name' => 'View Departments', 'slug' => 'departments.view', 'description' => 'View department information (legacy)', 'module' => 'Departments'],
                ['name' => 'Manage Departments', 'slug' => 'departments.manage', 'description' => 'Create, edit, delete departments (legacy)', 'module' => 'Departments'],

                // Dashboard & Analytics Permissions
                ['name' => 'View Dashboard', 'slug' => 'dashboard.view', 'description' => 'Access main LGU governance dashboard', 'module' => 'Dashboard'],
                ['name' => 'View Analytics', 'slug' => 'analytics.view', 'description' => 'View detailed analytics and insights across sectors', 'module' => 'Dashboard'],
                ['name' => 'View Provincial Analytics', 'slug' => 'analytics.provincial', 'description' => 'View province-wide performance metrics', 'module' => 'Dashboard'],
                ['name' => 'View Municipal Analytics', 'slug' => 'analytics.municipal', 'description' => 'View municipality-level analytics', 'module' => 'Dashboard'],
                ['name' => 'View Barangay Analytics', 'slug' => 'analytics.barangay', 'description' => 'View barangay-level metrics', 'module' => 'Dashboard'],

                // System Administration Permissions
                ['name' => 'System Settings', 'slug' => 'system.settings', 'description' => 'Manage system settings and configuration', 'module' => 'System'],
                ['name' => 'Audit Logs', 'slug' => 'system.audit_logs', 'description' => 'View system audit logs for COA compliance', 'module' => 'System'],
                ['name' => 'Backup & Restore', 'slug' => 'system.backup', 'description' => 'Perform system backup and restore', 'module' => 'System'],
            ];

            foreach ($permissions as $permission) {
                Permission::create(array_merge($permission, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ]));
            }
        });

        $this->command->info('Permissions seeded successfully!');
    }
}

