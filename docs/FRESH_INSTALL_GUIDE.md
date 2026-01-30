# Provincial LGU Governance Platform - Fresh Installation Guide

## Quick Setup (New Database)

This guide is for setting up the Provincial LGU Governance Platform from scratch with RA 7160-compliant approval workflows.

---

## Prerequisites

- PHP 8.2+
- MySQL 8.0+
- Composer
- Laravel 11

---

## Step-by-Step Installation

### 1. Clone & Configure

```bash
# Clone the repository (if not already done)
cd "/Users/jehnsenenrique/Projects/Freelance Projects/da-pmis-backend"

# Install dependencies
composer install

# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate
```

### 2. Configure Database

Edit `.env` file:

```env
APP_NAME="Provincial LGU Governance Platform"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=iterable_db
DB_USERNAME=root
DB_PASSWORD=your_password

# LGU Configuration
LGU_PROVINCE_NAME="Agusan del Norte"
LGU_REGION_CODE="XIII"
LGU_REGION_NAME="CARAGA"
```

### 3. Create Database

```bash
# Create the database
mysql -u root -p -e "CREATE DATABASE IF NOT EXISTS iterable_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

### 4. Run Migrations (RA 7160 Structure Included)

```bash
# Run all migrations
php artisan migrate

# This will create:
# - All base tables
# - Projects table with RA 7160 approval statuses (pending_barangay, pending_municipal, pending_provincial, pending_governor)
# - Project approvals with LGU levels (barangay, municipal, provincial, governor)
# - LGU sectors table (Social Services, Economic Services, Infrastructure, General Public Services)
# - Location tables (regions, provinces, municipalities)
```

### 5. Seed Database

```bash
# Seed in this order:
php artisan db:seed --class=PermissionSeeder
php artisan db:seed --class=RoleSeeder
php artisan db:seed --class=RegionSeeder
php artisan db:seed --class=ProvinceSeeder
php artisan db:seed --class=MunicipalitySeeder
php artisan db:seed --class=UserSeeder

# Or seed everything at once:
php artisan db:seed
```

### 6. Verify Installation

```bash
# Check LGU Sectors (should return 4)
php artisan tinker
>>> App\Models\LguSector::count();
4

>>> App\Models\LguSector::pluck('name', 'code');
// Expected output:
// {
//   "SS": "Social Services",
//   "ES": "Economic Services",
//   "IEM": "Infrastructure & Environmental Management",
//   "GPS": "General Public Services"
// }

>>> exit
```

### 7. Verify Approval Workflow

```bash
# Check approval statuses
mysql -u root -p iterable_db -e "SHOW COLUMNS FROM projects WHERE Field = 'approval_status';"

# Expected Type:
# enum('draft','pending_barangay','pending_municipal','pending_provincial','pending_governor','approved','rejected')

# Check approval levels
mysql -u root -p iterable_db -e "SHOW COLUMNS FROM project_approvals WHERE Field = 'level';"

# Expected Type:
# enum('barangay','municipal','provincial','governor')
```

### 8. Start Development Server

```bash
php artisan serve

# Access at: http://localhost:8000
```

---

## Default Login Credentials

After seeding, you can login with:

**Username:** `admin`
**Password:** `Password123!`

**⚠️ IMPORTANT:** Change this password immediately in production!

---

## Verify LGU Roles

```bash
# Check roles created
mysql -u root -p iterable_db -e "SELECT name FROM roles ORDER BY id;"
```

**Expected Roles (RA 7160 Structure):**
1. System Administrator
2. Provincial Governor
3. Provincial Planning Officer (PPDO)
4. Provincial Officer
5. Municipal Planning Officer (MPDO)
6. Municipal Officer
7. Barangay Development Council Officer
8. Sector Head
9. Project Manager
10. Technical Officer
11. Data Encoder
12. Public Viewer

---

## Test the Approval Workflow

### Create Test Project via API

```bash
# 1. Login to get token
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{"username":"admin","password":"Password123!"}'

# Save the token from response

# 2. Create a test project
curl -X POST http://localhost:8000/api/projects \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -d '{
    "title": "Test LGU Project - Community Health Center",
    "description": "Construction of barangay health center",
    "sector_id": 1,
    "municipality_id": 1,
    "province_id": 1,
    "barangay": "Poblacion",
    "project_type_id": 1,
    "project_status_id": 1,
    "budget": 5000000,
    "start_date": "2026-02-01",
    "end_date": "2026-12-31",
    "is_public": true
  }'

# Expected response: approval_status = "draft"

# 3. Submit for approval
curl -X POST http://localhost:8000/api/projects/1/submit-for-approval \
  -H "Authorization: Bearer YOUR_TOKEN"

# Expected response: approval_status = "pending_barangay"
```

### Complete Approval Chain

```bash
# 4. Approve at Barangay level (as Barangay officer)
curl -X POST http://localhost:8000/api/projects/1/approve \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"comments": "Approved by BDC"}'
# Expected: approval_status = "pending_municipal"

# 5. Approve at Municipal level (as MPDO)
curl -X POST http://localhost:8000/api/projects/1/approve \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"comments": "Validated by MPDO"}'
# Expected: approval_status = "pending_provincial"

# 6. Approve at Provincial level (as PPDO)
curl -X POST http://localhost:8000/api/projects/1/approve \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"comments": "Technical review passed - PPDO"}'
# Expected: approval_status = "pending_governor"

# 7. Final approval (as Governor)
curl -X POST http://localhost:8000/api/projects/1/approve \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"comments": "Approved by Provincial Governor"}'
# Expected: approval_status = "approved"
```

---

## Common Commands

```bash
# Clear all caches
php artisan optimize:clear

# Cache configuration for production
php artisan config:cache
php artisan route:cache
php artisan view:cache

# View routes
php artisan route:list

# Rollback last migration batch
php artisan migrate:rollback

# Fresh database with seeders
php artisan migrate:fresh --seed
```

---

## Database Schema Overview

### Key Tables

| Table | Purpose |
|-------|---------|
| `lgu_sectors` | 4 LGU governance sectors (SS, ES, IEM, GPS) |
| `projects` | Main project records with sector and location |
| `project_approvals` | Approval workflow history |
| `regions` | Geographic: Regions |
| `provinces` | Geographic: Provinces |
| `municipalities` | Geographic: Cities/Municipalities |
| `roles` | LGU role definitions |
| `users` | System users |
| `permissions` | Granular permissions |

### Projects Table Key Fields

```sql
sector_id           -- Links to lgu_sectors (SS/ES/IEM/GPS)
municipality_id     -- Where project is implemented
province_id         -- Province (auto-filled from municipality)
barangay            -- Barangay name
approval_status     -- draft|pending_barangay|pending_municipal|pending_provincial|pending_governor|approved|rejected
submitted_by        -- User who submitted
submitted_at        -- Submission timestamp
```

---

## RA 7160 Compliance Verification

### 1. Check Approval Workflow

```sql
-- View approval flow configuration
SELECT DISTINCT approval_status FROM projects;

-- Should show:
-- draft
-- pending_barangay (NEW - Entry point per RA 7160)
-- pending_municipal (MPDO validation)
-- pending_provincial (PPDO technical review)
-- pending_governor (Final approval per RA 7160 Sec. 455)
-- approved
-- rejected
```

### 2. Check LGU Sectors

```sql
SELECT id, code, name FROM lgu_sectors ORDER BY display_order;

-- Expected output:
-- 1  SS   Social Services
-- 2  ES   Economic Services
-- 3  IEM  Infrastructure & Environmental Management
-- 4  GPS  General Public Services
```

### 3. Check Approval Levels

```sql
SELECT DISTINCT level FROM project_approvals;

-- Expected:
-- barangay (Barangay Development Council)
-- municipal (MPDO)
-- provincial (PPDO)
-- governor (Provincial Governor)
```

---

## Troubleshooting

### Issue: "sector_id cannot be null"
```bash
# All projects need a sector. Assign default:
php artisan tinker
>>> App\Models\Project::whereNull('sector_id')->update(['sector_id' => 2]); // Economic Services
```

### Issue: "municipality_id cannot be null"
```bash
# Ensure municipalities are seeded first:
php artisan db:seed --class=RegionSeeder
php artisan db:seed --class=ProvinceSeeder
php artisan db:seed --class=MunicipalitySeeder
```

### Issue: "Unknown enum value"
```bash
# If you see old values like 'pending_regional':
# Drop and recreate database:
php artisan migrate:fresh --seed
```

---

## Production Deployment

```bash
# 1. Set environment to production
APP_ENV=production
APP_DEBUG=false

# 2. Optimize for production
composer install --no-dev --optimize-autoloader
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 3. Set proper permissions
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

# 4. Secure .env file
chmod 600 .env

# 5. Enable HTTPS
# Configure your web server (nginx/apache) for SSL
```

---

## API Endpoints

### Public Endpoints (No Auth)
- `POST /api/register`
- `POST /api/login`
- `GET /api/projects` (public projects only)
- `GET /api/dashboard/*` (all governance metrics)

### Protected Endpoints (Require Auth)
- `POST /api/projects/{id}/submit-for-approval`
- `POST /api/projects/{id}/approve`
- `POST /api/projects/{id}/reject`
- `POST /api/projects/{id}/request-changes`
- `GET /api/projects/pending-approval`
- `GET /api/projects/approval-statistics`

---

## Next Steps

After successful installation:

1. **Configure Users**: Create users for each LGU level (Barangay, MPDO, PPDO, Governor)
2. **Test Workflow**: Run through complete approval chain
3. **Import Data**: Import existing projects if migrating from old system
4. **Configure Dashboard**: Customize governance metrics for your province
5. **Train Staff**: Orient users on RA 7160 workflow
6. **Go Live**: Deploy to production server

---

## Support

For issues or questions:
- Check logs: `storage/logs/laravel.log`
- Run diagnostics: `php artisan about`
- Database check: `php artisan migrate:status`

---

**Installation Time:** ~10 minutes
**System Version:** 2.0 (Provincial LGU Governance Platform)
**RA 7160 Compliant:** ✅
**Last Updated:** 2026-01-29
