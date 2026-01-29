# Deploy Provincial LGU Governance Platform - Quick Guide

## Prerequisites

- PHP 8.2+
- MySQL 8.0+
- Composer installed
- Laravel 11 compatible environment

---

## Deployment Commands (Production)

### Step 1: Backup Current System

```bash
# Backup database
mysqldump -u root -p iterable_db > backup_$(date +%Y%m%d_%H%M%S).sql

# Backup .env file
cp .env .env.backup
```

### Step 2: Pull Latest Code

```bash
# If using Git
git pull origin main

# Install/update dependencies
composer install --no-dev --optimize-autoloader
```

### Step 3: Run Migrations (CRITICAL ORDER)

```bash
# Run in this exact sequence:

# 1. Create LGU sectors table (includes seeding)
php artisan migrate --path=database/migrations/2026_01_29_060001_create_lgu_sectors_table.php

# 2. Refactor approval levels (updates enums + migrates data)
php artisan migrate --path=database/migrations/2026_01_29_060000_refactor_approval_levels_for_lgu_governance.php

# 3. Add sector and location fields to projects
php artisan migrate --path=database/migrations/2026_01_29_060002_add_sector_and_location_to_projects_table.php

# Or run all together (if migrations are new):
# php artisan migrate
```

### Step 4: Update Roles and Permissions

```bash
# Re-seed roles with LGU structure
php artisan db:seed --class=RoleSeeder

# Re-seed permissions
php artisan db:seed --class=PermissionSeeder
```

### Step 5: Migrate User Roles (SQL)

```sql
-- Connect to database
mysql -u root -p iterable_db

-- Convert old roles to new LGU roles

-- Regional Director → Provincial Governor
UPDATE users
SET role_id = (SELECT id FROM roles WHERE name = 'Provincial Governor')
WHERE role_id = (SELECT id FROM roles WHERE name = 'Regional Director');

-- Field Officer → Barangay Development Council Officer
UPDATE users
SET role_id = (SELECT id FROM roles WHERE name = 'Barangay Development Council Officer')
WHERE role_id IN (SELECT id FROM roles WHERE name = 'Field Officer');

-- Department Head → Sector Head
UPDATE users
SET role_id = (SELECT id FROM roles WHERE name = 'Sector Head')
WHERE role_id IN (SELECT id FROM roles WHERE name = 'Department Head');

-- Agricultural Technician → Technical Officer
UPDATE users
SET role_id = (SELECT id FROM roles WHERE name = 'Technical Officer')
WHERE role_id IN (SELECT id FROM roles WHERE name = 'Agricultural Technician');
```

### Step 6: Clear Cache

```bash
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Step 7: Verify Installation

```bash
# Check LGU sectors were created (should return 4)
php artisan tinker
>>> App\Models\LguSector::count();
>>> exit

# Check approval statuses migrated correctly
mysql -u root -p iterable_db -e "SELECT approval_status, COUNT(*) as count FROM projects GROUP BY approval_status;"

# Expected output: pending_governor (not pending_regional)
```

---

## Post-Deployment Verification

### 1. Database Checks

```sql
-- Verify LGU sectors exist
SELECT * FROM lgu_sectors ORDER BY display_order;
-- Expected: 4 rows (SS, ES, IEM, GPS)

-- Verify no old approval statuses
SELECT COUNT(*) FROM projects WHERE approval_status = 'pending_regional';
-- Expected: 0

-- Verify new approval statuses
SELECT approval_status, COUNT(*) FROM projects GROUP BY approval_status;
-- Should include: pending_barangay, pending_governor

-- Verify project approvals migrated
SELECT level, COUNT(*) FROM project_approvals GROUP BY level;
-- Should include: barangay, governor (NOT field or regional)
```

### 2. API Tests

```bash
# Test projects endpoint
curl http://localhost:8000/api/projects | jq '.data[0] | {sector_id, municipality_id, approval_status}'

# Test approval statistics
curl http://localhost:8000/api/projects/approval-statistics | jq '.by_status'

# Expected to see: pending_barangay, pending_governor
```

### 3. Approval Workflow Test

```bash
# 1. Create test project (as authenticated user)
POST /api/projects
{
  "title": "Test LGU Project",
  "sector_id": 2,  // Economic Services
  "municipality_id": 10,
  "province_id": 1,
  "barangay": "Test Barangay",
  "budget": 1000000,
  "start_date": "2026-02-01",
  "end_date": "2026-12-31"
}
# Expected: approval_status = "draft"

# 2. Submit for approval
POST /api/projects/{id}/submit-for-approval
# Expected: approval_status = "pending_barangay"

# 3. Approve (as Barangay officer)
POST /api/projects/{id}/approve
# Expected: approval_status = "pending_municipal"
```

---

## Rollback Procedure (If Issues Occur)

```bash
# 1. Stop application
php artisan down

# 2. Rollback migrations
php artisan migrate:rollback --step=3

# 3. Restore database backup
mysql -u root -p iterable_db < backup_YYYYMMDD_HHMMSS.sql

# 4. Restore .env
cp .env.backup .env

# 5. Clear cache
php artisan optimize:clear

# 6. Restart application
php artisan up
```

---

## Common Issues & Fixes

### Issue 1: "sector_id cannot be null"
```sql
-- Fix: Assign default sector to projects missing sector_id
UPDATE projects
SET sector_id = (SELECT id FROM lgu_sectors WHERE code = 'ES')
WHERE sector_id IS NULL;
```

### Issue 2: "Unknown approval level"
```sql
-- Check if migrations ran correctly
SELECT DISTINCT approval_status FROM projects;
-- Should NOT contain 'pending_regional'

-- If still showing old values, re-run migration:
php artisan migrate:refresh --path=database/migrations/2026_01_29_060000_refactor_approval_levels_for_lgu_governance.php
```

### Issue 3: "User cannot approve projects"
```sql
-- Check user's role
SELECT users.username, roles.name
FROM users
JOIN roles ON users.role_id = roles.id
WHERE users.id = [USER_ID];

-- Update to appropriate LGU role:
UPDATE users
SET role_id = (SELECT id FROM roles WHERE name = 'Provincial Planning Officer (PPDO)')
WHERE id = [USER_ID];
```

### Issue 4: "municipalities table doesn't exist"
```bash
# Run location seeders first
php artisan db:seed --class=RegionSeeder
php artisan db:seed --class=ProvinceSeeder
php artisan db:seed --class=MunicipalitySeeder
```

---

## Environment Variables

Ensure `.env` has these configured:

```env
APP_NAME="Provincial LGU Governance Platform"
APP_ENV=production
APP_DEBUG=false

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=iterable_db
DB_USERNAME=root
DB_PASSWORD=your_password

# LGU-specific settings
LGU_PROVINCE_NAME="Agusan del Norte"
LGU_REGION_CODE="XIII"
LGU_REGION_NAME="CARAGA"
```

---

## Performance Optimization

```bash
# Production optimizations
composer install --no-dev --optimize-autoloader
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# Database optimizations
php artisan optimize

# Queue workers (if using)
php artisan queue:restart
```

---

## Monitoring Commands

```bash
# Check system health
php artisan tinker
>>> App\Models\Project::count();
>>> App\Models\LguSector::count();  // Should be 4
>>> App\Models\Role::count();       // Should be 12 (new LGU roles)
>>> exit

# Check recent approvals
SELECT p.title, pa.level, pa.action, pa.action_taken_at
FROM project_approvals pa
JOIN projects p ON pa.project_id = p.id
ORDER BY pa.action_taken_at DESC
LIMIT 10;

# Check sector distribution
SELECT ls.name, COUNT(p.id) as project_count
FROM lgu_sectors ls
LEFT JOIN projects p ON p.sector_id = ls.id
GROUP BY ls.id, ls.name;
```

---

## Security Checklist

- [ ] `.env` file has secure `APP_KEY`
- [ ] Database password is strong
- [ ] `APP_DEBUG=false` in production
- [ ] CORS settings configured properly
- [ ] API rate limiting enabled
- [ ] All migrations ran successfully
- [ ] User roles properly migrated
- [ ] Audit logging functional
- [ ] No sensitive data in Git repository

---

## Success Criteria

✅ **System is ready when:**

1. LGU sectors table has 4 entries
2. No projects have `approval_status = 'pending_regional'`
3. All project_approvals have valid levels (barangay/municipal/provincial/governor)
4. User roles include Governor, PPDO, MPDO, Barangay Officer
5. API returns new sector and location fields in project responses
6. Approval workflow works: Draft → Barangay → Municipal → Provincial → Governor → Approved
7. All 10 governance metrics endpoints return data

---

## Support Contacts

- **Technical Issues:** Development Team
- **RA 7160 Compliance:** Legal/Governance Team
- **Data Migration:** Database Administrator

---

**Quick Deploy Checklist:**

```bash
# Copy-paste deployment script:

# 1. Backup
mysqldump -u root -p iterable_db > backup_$(date +%Y%m%d_%H%M%S).sql

# 2. Migrate
php artisan migrate --path=database/migrations/2026_01_29_060001_create_lgu_sectors_table.php
php artisan migrate --path=database/migrations/2026_01_29_060000_refactor_approval_levels_for_lgu_governance.php
php artisan migrate --path=database/migrations/2026_01_29_060002_add_sector_and_location_to_projects_table.php

# 3. Seed
php artisan db:seed --class=RoleSeeder
php artisan db:seed --class=PermissionSeeder

# 4. Optimize
php artisan optimize:clear
php artisan config:cache
php artisan route:cache

# 5. Verify
php artisan tinker -c "echo App\Models\LguSector::count();"
# Should output: 4

echo "✅ Provincial LGU Governance Platform deployed successfully!"
```

---

**Last Updated:** 2026-01-29
**Version:** 2.0 LGU Platform
**Deployment Time:** ~15 minutes
