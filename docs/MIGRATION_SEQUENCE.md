# PMIS Migration Sequence Guide

**Last Updated:** 2026-01-28
**Total Migrations:** 39 (2 Laravel built-in + 37 custom including Sanctum)
**Status:** ✅ Properly sequenced and tested
**Authentication:** Migrated from Laravel Passport to Laravel Sanctum (2026-01-28)

---

## ⚠️ Authentication Update

**Date:** January 28, 2026
**Action:** Migrated from Laravel Passport (OAuth 2.0) to Laravel Sanctum (Token-based)

**Removed Migrations:**
- ~~2024_01_01_000008_create_oauth_auth_codes_table.php~~
- ~~2024_01_01_000009_create_oauth_access_tokens_table.php~~
- ~~2024_01_01_000010_create_oauth_refresh_tokens_table.php~~
- ~~2024_01_01_000011_create_oauth_clients_table.php~~
- ~~2024_01_01_000012_create_oauth_personal_access_clients_table.php~~

**Added Migrations:**
- 2026_01_28_010231_create_personal_access_tokens_table.php (Sanctum)
- 2026_01_28_010431_drop_passport_oauth_tables.php (Cleanup)

**Reason:** Sanctum is simpler, faster, and better suited for first-party SPAs and mobile apps. Passport's OAuth 2.0 complexity was unnecessary for this project's use case.

---

## Migration Order (Dependency-Based)

All migrations are properly sequenced to avoid foreign key constraint errors and follow logical dependency chains.

### Phase 1: Laravel Foundation (2 migrations)
Built-in Laravel tables for caching and job queues:
```
0001_01_01_000001_create_cache_table.php
0001_01_01_000002_create_jobs_table.php
```

### Phase 2: Base Lookup Tables (3 migrations)
Independent lookup tables with no foreign key dependencies:
```
2024_01_01_000001_create_roles_table.php
2024_01_01_000002_create_departments_table.php
2024_01_01_000003_create_regions_table.php
```

### Phase 3: Geographic Hierarchy (2 migrations)
Location tables following hierarchical order (regions → provinces → municipalities):
```
2024_01_01_000004_create_provinces_table.php          (depends on: regions)
2024_01_01_000005_create_municipalities_table.php     (depends on: provinces)
```

### Phase 4: Authentication & Users (1 migration)
User table that depends on roles and departments:
```
2024_01_01_000006_create_users_table.php              (depends on: roles, departments)
```

### Phase 5: Session & API Authentication (1 migration)
Session management for web authentication:
```
2024_01_01_000007_create_sessions_table.php           (depends on: users) ✨ FK added
```

**Note:** API authentication is now handled by Laravel Sanctum with `personal_access_tokens` table (added later in Phase 11).
**✨ New:** Added foreign key constraints to prevent orphaned records

### Phase 6: PMIS Lookup Tables (4 migrations)
Independent PMIS configuration tables:
```
2024_01_01_000013_create_permissions_table.php
2024_01_01_000014_create_project_types_table.php
2024_01_01_000015_create_project_statuses_table.php
2024_01_01_000016_create_document_categories_table.php
```

### Phase 7: PMIS Main Entity Tables (11 migrations)
Core business entities with their dependencies:
```
2024_01_01_000017_create_projects_table.php           (depends on: departments, project_types, project_statuses)
2024_01_01_000018_create_department_kpis_table.php    (depends on: departments)
2024_01_01_000019_create_progress_reports_table.php   (depends on: departments, users)
2024_01_01_000020_create_crop_productions_table.php   (depends on: regions)
2024_01_01_000021_create_livestock_statistics_table.php (depends on: regions)
2024_01_01_000022_create_funding_distributions_table.php (depends on: departments, projects)
2024_01_01_000023_create_news_updates_table.php       (depends on: users)
2024_01_01_000024_create_documents_table.php          (depends on: users)
2024_01_01_000025_create_contact_inquiries_table.php  (no dependencies)
2024_01_01_000026_create_newsletter_subscriptions_table.php (no dependencies)
2024_01_01_000027_create_audit_logs_table.php         (depends on: users)
```

### Phase 8: Junction/Pivot Tables (5 migrations)
Many-to-many relationship tables:
```
2024_01_01_000028_create_project_team_members_table.php (depends on: projects, users)
2024_01_01_000029_create_project_milestones_table.php   (depends on: projects)
2024_01_01_000030_create_report_metrics_table.php       (depends on: progress_reports)
2024_01_01_000031_create_document_category_pivot_table.php (depends on: documents, document_categories)
2024_01_01_000032_create_role_permission_table.php      (depends on: roles, permissions)
```

### Phase 9: Table Alterations (3 migrations)
Modifications to existing tables:
```
2024_01_01_000033_add_fields_to_documents_table.php   (alters: documents)
2024_01_01_000034_add_last_login_to_users_table.php   (alters: users)
2024_01_01_000035_add_approval_status_to_projects_table.php (alters: projects)
```

### Phase 10: Workflow & Advanced Features (4 migrations)
Advanced features that depend on altered tables:
```
2024_01_01_000036_create_project_approvals_table.php  (depends on: projects, users)
2024_01_01_000037_create_notifications_table.php      (no dependencies)
2024_01_01_000038_create_project_disbursements_table.php (depends on: projects, users)
2024_01_01_000039_enhance_progress_reports_table.php  (alters: progress_reports, adds: projects FK)
```

### Phase 11: Additional Location Fields (1 migration)
```
2024_01_01_000040_add_location_fields_to_regions_table.php (alters: regions)
```

### Phase 12: Authentication Migration (2 migrations)
Sanctum API token authentication and Passport cleanup:
```
2026_01_28_010231_create_personal_access_tokens_table.php (Sanctum token storage)
2026_01_28_010431_drop_passport_oauth_tables.php          (Cleanup: drops 5 OAuth tables)
```

---

## Migration Dependency Map

```
Laravel Built-in
├── cache
└── jobs

Base Tables
├── roles
│   └─> users
│       ├─> sessions ✨
│       ├─> personal_access_tokens ✨ (Sanctum)
│       ├─> project_team_members
│       ├─> news_updates
│       ├─> documents
│       ├─> progress_reports
│       ├─> project_approvals
│       ├─> project_disbursements
│       └─> audit_logs
├── departments
│   ├─> users
│   ├─> projects
│   ├─> department_kpis
│   ├─> progress_reports
│   └─> funding_distributions
└── regions
    ├─> provinces
    │   └─> municipalities
    ├─> crop_productions
    └─> livestock_statistics

Lookup Tables
├── permissions
│   └─> role_permission
├── project_types
│   └─> projects
├── project_statuses
│   └─> projects
└── document_categories
    └─> document_category_pivot

Main Entities
├── projects
│   ├─> project_team_members
│   ├─> project_milestones
│   ├─> funding_distributions
│   ├─> project_approvals
│   ├─> project_disbursements
│   └─> progress_reports (enhanced)
├── progress_reports
│   └─> report_metrics
└── documents
    └─> document_category_pivot

No Dependencies
├── contact_inquiries
└── newsletter_subscriptions
```

**✨ = New foreign key constraints added for data integrity**

---

## Running Migrations

### Fresh Migration (Development Only)
```bash
php artisan migrate:fresh
```
⚠️ **WARNING:** This will DROP all tables and recreate them! Use only in development.

### Fresh Migration with Seeders
```bash
php artisan migrate:fresh --seed
```
This will reset the database and populate it with sample data.

### Normal Migration (Recommended for Production)
```bash
php artisan migrate
```
This will run only pending migrations in order.

### Check Migration Status
```bash
php artisan migrate:status
```
View which migrations have been run and which are pending.

### Rollback Last Batch
```bash
php artisan migrate:rollback
```
Rollback the most recent migration batch.

### Rollback All Migrations
```bash
php artisan migrate:reset
```
Rollback all migrations.

### Rollback and Re-migrate
```bash
php artisan migrate:refresh
```
Rollback all migrations and re-run them.

---

## Verification Commands

### Verify Tables Exist
```bash
php artisan tinker
>>> DB::select('SHOW TABLES');
```

### Check Specific Table
```bash
php artisan tinker
>>> Schema::hasTable('projects');
```

### Verify Foreign Keys
```bash
php artisan tinker
>>> DB::select("SELECT * FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = 'iterable_db' AND CONSTRAINT_NAME LIKE 'fk_%'");
```

### Count Total Tables
```bash
php artisan tinker
>>> count(DB::select('SHOW TABLES'));
# Should return 28
```

---

## Troubleshooting

### Error: Foreign key constraint fails
**Cause:** Parent table doesn't exist yet
**Solution:**
- Check migration timestamps - parent tables must run before child tables
- Verify the dependency map above
- Check if migrations are in correct order

### Error: Table already exists
**Cause:** Migration was already run
**Solution:**
```bash
# Check migration status
php artisan migrate:status

# If needed, rollback and re-run
php artisan migrate:rollback
php artisan migrate
```

### Error: Syntax error in migration
**Cause:** PHP or SQL syntax error in migration file
**Solution:**
- Check the specific migration file mentioned in error
- Review Laravel migration documentation
- Verify column types and method names

### Error: Access denied for user
**Cause:** Database credentials incorrect or user lacks permissions
**Solution:**
- Check `.env` file for correct DB credentials
- Verify MySQL user has CREATE, ALTER, DROP permissions
- Test database connection: `php artisan tinker` → `DB::connection()->getPdo()`

---

## Best Practices

### 1. Always Backup Before Migrating
```bash
# MySQL backup
mysqldump -u username -p iterable_db > backup_$(date +%Y%m%d_%H%M%S).sql

# Or using Laravel
php artisan backup:run
```

### 2. Test Migrations in Development First
- Never run migrations directly on production without testing
- Use staging environment that mirrors production
- Verify data integrity after migration

### 3. Review Migration Files Before Running
- Check foreign key constraints
- Verify column types and defaults
- Ensure proper indexes

### 4. Keep Migrations Immutable
- Don't modify migrations once run in production
- Create new migrations for changes
- Use `down()` method for reversibility

### 5. Use Database Transactions
Laravel migrations run in transactions by default, but you can disable:
```php
// In migration file
public $withinTransaction = false; // Only if necessary
```

---

## Post-Migration Setup

### 1. Run Seeders
```bash
php artisan db:seed
```

### 2. Verify Seeded Data
```bash
php artisan tinker
>>> \App\Models\User::count();       # Should return 15
>>> \App\Models\Project::count();    # Should return 20
>>> \App\Models\Role::count();       # Should return 7
```

### 3. Test Authentication
```bash
# Test login endpoint
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{"username": "admin", "password": "Password123!"}'
```

### 4. Verify Foreign Keys
```bash
php artisan tinker
>>> $user = \App\Models\User::first();
>>> $user->delete(); # Should cascade/null related records properly
```

---

## Migration Statistics

| Metric | Count |
|--------|-------|
| Total Migrations | 39 |
| Laravel Built-in | 2 |
| Custom Migrations | 37 |
| Tables Created | 24 (removed 5 OAuth tables, added 1 Sanctum table) |
| Foreign Key Constraints | 30+ (removed OAuth FKs) |
| Indexes | 35+ |
| Soft Deletes Enabled | 4 (projects, news_updates, documents, progress_reports) |

---

## Recent Changes (2026-01-28)

### ✅ Cleanup Completed
- Deleted 1 migration (`create_program_tags_table`)
- Added 4 foreign key constraints (sessions, OAuth tables)
- Renumbered 40 migrations for proper sequencing
- Moved provinces/municipalities earlier in sequence

### 📝 See Also
- [MIGRATION_CLEANUP_LOG.md](./MIGRATION_CLEANUP_LOG.md) - Detailed changelog
- [PROJECT_SUMMARY.md](./PROJECT_SUMMARY.md) - Complete project overview
- [SEEDER_DOCUMENTATION.md](./SEEDER_DOCUMENTATION.md) - Seeder guide

---

**Status:** ✅ Tested and verified
**Last Migration Test:** 2026-01-28 (SUCCESS)
**Environment:** Development (XAMPP, MySQL)
**Database:** iterable_db

---

*This document is automatically updated when migrations change.*
