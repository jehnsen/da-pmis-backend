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
     * Creates comprehensive permissions for PMIS system modules
     */
    public function run(): void
    {
        DB::transaction(function () {
            $permissions = [
                // Project Management Permissions
                ['name' => 'View Projects', 'slug' => 'projects.view', 'description' => 'View project listings and details', 'module' => 'Projects'],
                ['name' => 'Create Projects', 'slug' => 'projects.create', 'description' => 'Create new agricultural projects', 'module' => 'Projects'],
                ['name' => 'Edit Projects', 'slug' => 'projects.edit', 'description' => 'Edit existing project information', 'module' => 'Projects'],
                ['name' => 'Delete Projects', 'slug' => 'projects.delete', 'description' => 'Delete projects from the system', 'module' => 'Projects'],
                ['name' => 'Manage Project Team', 'slug' => 'projects.manage_team', 'description' => 'Add/remove project team members', 'module' => 'Projects'],

                // Document Management Permissions
                ['name' => 'View Documents', 'slug' => 'documents.view', 'description' => 'View document library', 'module' => 'Documents'],
                ['name' => 'Upload Documents', 'slug' => 'documents.upload', 'description' => 'Upload new documents', 'module' => 'Documents'],
                ['name' => 'Edit Documents', 'slug' => 'documents.edit', 'description' => 'Edit document metadata', 'module' => 'Documents'],
                ['name' => 'Delete Documents', 'slug' => 'documents.delete', 'description' => 'Delete documents', 'module' => 'Documents'],

                // Reports & Statistics Permissions
                ['name' => 'View Reports', 'slug' => 'reports.view', 'description' => 'View statistical reports and analytics', 'module' => 'Reports'],
                ['name' => 'Generate Reports', 'slug' => 'reports.generate', 'description' => 'Generate custom reports', 'module' => 'Reports'],
                ['name' => 'Export Data', 'slug' => 'reports.export', 'description' => 'Export data and reports', 'module' => 'Reports'],

                // News & Updates Permissions
                ['name' => 'View News', 'slug' => 'news.view', 'description' => 'View news updates', 'module' => 'News'],
                ['name' => 'Create News', 'slug' => 'news.create', 'description' => 'Create news articles', 'module' => 'News'],
                ['name' => 'Edit News', 'slug' => 'news.edit', 'description' => 'Edit news articles', 'module' => 'News'],
                ['name' => 'Delete News', 'slug' => 'news.delete', 'description' => 'Delete news articles', 'module' => 'News'],
                ['name' => 'Publish News', 'slug' => 'news.publish', 'description' => 'Publish/unpublish news articles', 'module' => 'News'],

                // User Management Permissions
                ['name' => 'View Users', 'slug' => 'users.view', 'description' => 'View user listings', 'module' => 'Users'],
                ['name' => 'Create Users', 'slug' => 'users.create', 'description' => 'Create new user accounts', 'module' => 'Users'],
                ['name' => 'Edit Users', 'slug' => 'users.edit', 'description' => 'Edit user information', 'module' => 'Users'],
                ['name' => 'Delete Users', 'slug' => 'users.delete', 'description' => 'Delete user accounts', 'module' => 'Users'],
                ['name' => 'Manage Roles', 'slug' => 'users.manage_roles', 'description' => 'Assign/modify user roles', 'module' => 'Users'],

                // Department Management Permissions
                ['name' => 'View Departments', 'slug' => 'departments.view', 'description' => 'View department information', 'module' => 'Departments'],
                ['name' => 'Manage Departments', 'slug' => 'departments.manage', 'description' => 'Create, edit, delete departments', 'module' => 'Departments'],

                // Dashboard & Analytics Permissions
                ['name' => 'View Dashboard', 'slug' => 'dashboard.view', 'description' => 'Access main dashboard', 'module' => 'Dashboard'],
                ['name' => 'View Analytics', 'slug' => 'analytics.view', 'description' => 'View detailed analytics and insights', 'module' => 'Dashboard'],

                // System Administration Permissions
                ['name' => 'System Settings', 'slug' => 'system.settings', 'description' => 'Manage system settings and configuration', 'module' => 'System'],
                ['name' => 'Audit Logs', 'slug' => 'system.audit_logs', 'description' => 'View system audit logs', 'module' => 'System'],
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

