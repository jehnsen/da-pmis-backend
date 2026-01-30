# Database Migration Instructions - RA 7160 Update

## For Existing Databases

If you already have a database with the old approval structure, follow these steps:

### Step 1: Backup Your Database

```bash
mysqldump -u root -p iterable_db > backup_before_ra7160_$(date +%Y%m%d_%H%M%S).sql
```

### Step 2: Run the Update Migration

```bash
php artisan migrate
```

This will run the migration:
`2026_01_30_000000_update_approval_status_enum_to_lgu_structure.php`

**What it does:**
1. Converts existing data:
   - `pending_regional` → `pending_governor`
   - `level='field'` → `level='barangay'`
2. Updates enum definitions to include new RA 7160 values
3. Removes old values from enums

### Step 3: Re-seed Roles and Permissions

```bash
# Truncate old roles (optional, if you want fresh roles)
php artisan db:seed --class=RoleSeeder

# Update permissions
php artisan db:seed --class=PermissionSeeder
```

### Step 4: Verify the Migration

```bash
# Check approval_status enum
mysql -u root -p iterable_db -e "SHOW COLUMNS FROM projects WHERE Field = 'approval_status';"

# Expected Type:
# enum('draft','pending_barangay','pending_municipal','pending_provincial','pending_governor','approved','rejected')

# Check level enum
mysql -u root -p iterable_db -e "SHOW COLUMNS FROM project_approvals WHERE Field = 'level';"

# Expected Type:
# enum('barangay','municipal','provincial','governor')

# Check data was migrated
mysql -u root -p iterable_db -e "SELECT approval_status, COUNT(*) as count FROM projects GROUP BY approval_status;"

# Should NOT show 'pending_regional', should show 'pending_governor'
```

### Step 5: Update User Roles (Manual)

Since role names changed, update existing users:

```sql
-- Connect to database
mysql -u root -p iterable_db

-- Update role assignments (if roles already exist)
UPDATE users u
JOIN roles r_old ON u.role_id = r_old.id
JOIN roles r_new ON r_new.name = CASE
    WHEN r_old.name = 'Regional Director' THEN 'Provincial Governor'
    WHEN r_old.name = 'Field Officer' THEN 'Barangay Development Council Officer'
    WHEN r_old.name = 'Department Head' THEN 'Sector Head'
    WHEN r_old.name = 'Agricultural Technician' THEN 'Technical Officer'
    ELSE r_old.name
END
SET u.role_id = r_new.id;
```

---

## For Fresh Installations

If you're setting up a new database:

```bash
# 1. Create database
mysql -u root -p -e "CREATE DATABASE iterable_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# 2. Run all migrations (RA 7160 structure is built-in!)
php artisan migrate

# 3. Seed all data
php artisan db:seed

# ✅ Done! System is ready with RA 7160 compliance
```

---

## Rollback (If Issues Occur)

```bash
# 1. Rollback the last migration
php artisan migrate:rollback --step=1

# 2. Or restore from backup
mysql -u root -p iterable_db < backup_before_ra7160_YYYYMMDD_HHMMSS.sql

# 3. Clear cache
php artisan optimize:clear
```

---

## Migration Files Summary

### Base Migrations (Updated)
- `2024_01_01_000035_add_approval_status_to_projects_table.php`
  - Now creates enum with RA 7160 values from the start
- `2024_01_01_000036_create_project_approvals_table.php`
  - Now creates level enum with RA 7160 values from the start

### Update Migration (For Existing DBs)
- `2026_01_30_000000_update_approval_status_enum_to_lgu_structure.php`
  - Converts old data to new structure
  - Updates enum definitions

### LGU Structure Migrations
- `2026_01_29_060001_create_lgu_sectors_table.php`
  - Creates 4 LGU sectors (SS, ES, IEM, GPS)
- `2026_01_29_060002_add_sector_and_location_to_projects_table.php`
  - Adds sector_id, municipality_id, province_id, barangay fields

---

## Verification Checklist

After migration, verify:

- [ ] `projects.approval_status` includes `pending_barangay` and `pending_governor`
- [ ] `projects.approval_status` does NOT include `pending_regional`
- [ ] `project_approvals.level` includes `barangay` and `governor`
- [ ] `project_approvals.level` does NOT include `field` or `regional`
- [ ] `lgu_sectors` table exists with 4 entries
- [ ] `projects` table has `sector_id`, `municipality_id`, `province_id`, `barangay` columns
- [ ] Roles include: Provincial Governor, PPDO, MPDO, Barangay Development Council Officer
- [ ] No database errors when creating new projects

---

## Testing the New Structure

### Create a Test Project

```bash
php artisan tinker

# Create project with new structure
$project = App\Models\Project::create([
    'title' => 'Test RA 7160 Project',
    'description' => 'Testing new approval workflow',
    'sector_id' => 2, // Economic Services
    'municipality_id' => 1,
    'province_id' => 1,
    'barangay' => 'Test Barangay',
    'project_type_id' => 1,
    'project_status_id' => 1,
    'budget' => 1000000,
    'start_date' => '2026-02-01',
    'end_date' => '2026-12-31',
    'is_public' => true
]);

# Check approval status
$project->approval_status; // Should be 'draft'

exit
```

### Test Approval Workflow

```bash
# Via API (if server is running)
# 1. Submit for approval
POST /api/projects/{id}/submit-for-approval
# Expected: approval_status changes to 'pending_barangay'

# 2. Check pending level
GET /api/projects/{id}
# Expected: Shows "Pending Barangay Development Council Review"
```

---

## Common Issues & Solutions

### Issue: "Unknown column 'sector_id'"
**Solution:** Run the sector migrations
```bash
php artisan migrate --path=database/migrations/2026_01_29_060001_create_lgu_sectors_table.php
php artisan migrate --path=database/migrations/2026_01_29_060002_add_sector_and_location_to_projects_table.php
```

### Issue: "Data truncated for column 'level'"
**Solution:** This means you're trying to insert old values. Re-run the update migration:
```bash
php artisan migrate:refresh --path=database/migrations/2026_01_30_000000_update_approval_status_enum_to_lgu_structure.php
```

### Issue: "Projects have NULL sector_id"
**Solution:** Assign default sector to all projects
```sql
UPDATE projects SET sector_id = 2 WHERE sector_id IS NULL; -- Economic Services
```

### Issue: "Role not found" errors
**Solution:** Re-seed roles with new LGU structure
```bash
php artisan db:seed --class=RoleSeeder
```

---

## Production Deployment Checklist

- [ ] Schedule maintenance window
- [ ] Notify users of system downtime
- [ ] Backup database
- [ ] Test migration on staging environment first
- [ ] Run migrations on production
- [ ] Update user roles
- [ ] Verify all systems operational
- [ ] Monitor logs for errors
- [ ] Update API documentation
- [ ] Train staff on new RA 7160 terminology

---

**Migration Time:** ~5 minutes
**Downtime Required:** Yes (~2-3 minutes)
**Rollback Available:** Yes (see Rollback section)
**Data Loss Risk:** None (data is converted, not deleted)

**Last Updated:** 2026-01-30
