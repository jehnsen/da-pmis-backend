# PMIS Migration Sequence Guide

## Migration Order (Dependency-Based)

The migrations are properly sequenced to avoid dependency errors.

### Phase 1: Base Tables (Already Exist)
These were created in the base project setup:
- 0001_01_01_000001_create_cache_table
- 0001_01_01_000002_create_jobs_table
- 2025_08_24_122909_create_roles_table
- 2025_08_24_122927_create_departments_table
- 2025_08_24_124004_create_regions_table
- 2025_08_24_124005_create_divisions_table
- 2025_08_24_124007_create_schools_table
- 2025_08_24_124008_create_users_table
- 2025_08_24_133056_create_sessions_table

### Phase 2: PMIS Lookup Tables
These tables have no foreign key dependencies:
1. 2025_10_06_100001_create_project_types_table
2. 2025_10_06_100002_create_project_statuses_table
3. 2025_10_06_100003_create_document_categories_table
4. 2025_10_06_100004_create_permissions_table

### Phase 3: PMIS Main Tables
These depend on base tables and lookup tables:
5. 2025_10_06_100010_create_projects_table (depends on: departments, project_types, project_statuses)
6. 2025_10_06_100011_create_department_kpis_table (depends on: departments)
7. 2025_10_06_100012_create_progress_reports_table (depends on: departments, users)
8. 2025_10_06_100013_create_crop_productions_table (depends on: regions)
9. 2025_10_06_100014_create_livestock_statistics_table (depends on: regions)
10. 2025_10_06_100015_create_funding_distributions_table (depends on: departments, projects)
11. 2025_10_06_100016_create_news_updates_table (depends on: users)
12. 2025_10_06_100017_create_documents_table (depends on: users)
13. 2025_10_06_100018_create_contact_inquiries_table (no dependencies)
14. 2025_10_06_100019_create_newsletter_subscriptions_table (no dependencies)
15. 2025_10_06_100020_create_audit_logs_table (depends on: users)

### Phase 4: PMIS Pivot/Relationship Tables
These depend on main tables:
16. 2025_10_06_100021_create_project_team_members_table (depends on: projects, users)
17. 2025_10_06_100022_create_project_milestones_table (depends on: projects)
18. 2025_10_06_100023_create_report_metrics_table (depends on: progress_reports)
19. 2025_10_06_100024_create_document_category_pivot_table (depends on: documents, document_categories)
20. 2025_10_06_100025_create_role_permission_table (depends on: roles, permissions)

## Running Migrations

### Fresh Migration (Development Only)
```bash
php artisan migrate:fresh
```
⚠️ WARNING: This will DROP all tables and recreate them!

### Normal Migration (Recommended)
```bash
php artisan migrate
```
This will run pending migrations in order.

### Check Migration Status
```bash
php artisan migrate:status
```

### Rollback Last Batch
```bash
php artisan migrate:rollback
```

### Rollback All Migrations
```bash
php artisan migrate:reset
```

## Verifying Migrations

After running migrations, verify tables exist:
```bash
php artisan tinker
>>> DB::select('SHOW TABLES');
```

Or check specific table:
```bash
php artisan tinker
>>> Schema::hasTable('projects');
```

## Troubleshooting

### Error: Foreign key constraint fails
**Cause:** Parent table doesn't exist yet
**Solution:** Check migration timestamps - parent tables must run before child tables

### Error: Table already exists
**Cause:** Migration was already run
**Solution:** 
```bash
# Check status
php artisan migrate:status

# If needed, rollback and re-run
php artisan migrate:rollback
php artisan migrate
```

### Error: Syntax error in migration
**Cause:** PHP or SQL syntax error in migration file
**Solution:** Check the specific migration file mentioned in error

## Migration Dependencies Map

```
roles (existing)
  └─> role_permission
        └─> permissions

departments (existing)
  ├─> projects
  │     ├─> project_team_members
  │     ├─> project_milestones
  │     └─> funding_distributions
  ├─> department_kpis
  └─> progress_reports
        └─> report_metrics

users (existing)
  ├─> projects (created_by)
  ├─> progress_reports (created_by)
  ├─> news_updates (created_by)
  ├─> documents (created_by)
  ├─> audit_logs (user_id)
  └─> project_team_members

regions (existing)
  ├─> crop_productions
  └─> livestock_statistics

project_types (lookup)
  └─> projects

project_statuses (lookup)
  └─> projects

document_categories (lookup)
  └─> document_category_pivot
        └─> documents

No Dependencies:
  - contact_inquiries
  - newsletter_subscriptions
```

## Best Practices

1. **Always backup database before migrating in production**
   ```bash
   mysqldump -u username -p database_name > backup.sql
   ```

2. **Test migrations in development first**

3. **Review migration files before running**

4. **Keep migrations immutable** - don't modify once run in production

5. **Use transactions when possible** (Laravel does this by default)

## Post-Migration Setup

After successful migration, seed lookup tables:
```bash
# Create seeders
php artisan make:seeder ProjectTypesSeeder
php artisan make:seeder ProjectStatusesSeeder
php artisan make:seeder DocumentCategoriesSeeder
php artisan make:seeder PermissionsSeeder

# Run seeders
php artisan db:seed
```

---

**Total Tables:** 20 new PMIS tables + existing base tables
**Migration Files:** 20 PMIS migrations
**Status:** ✅ Properly sequenced and ready to run
