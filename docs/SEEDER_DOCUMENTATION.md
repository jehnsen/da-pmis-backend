# CARAGA PMIS Database Seeder Documentation

## Overview
This document provides comprehensive information about the database seeders for the **Department of Agriculture - CARAGA Region Performance Management Information System (PMIS)**.

All seeders contain realistic data specifically tailored for the CARAGA Region (Region XIII) in the Philippines, covering the provinces of Agusan del Norte, Agusan del Sur, Surigao del Norte, Surigao del Sur, and Dinagat Islands.

---

## Running the Seeders

### Fresh Migration with Seed
To reset the database and seed all data:
```bash
php artisan migrate:fresh --seed
```

### Seed Only (without migration)
To run seeders without resetting the database:
```bash
php artisan db:seed
```

### Run Specific Seeder
To run a specific seeder:
```bash
php artisan db:seed --class=ProjectSeeder
php artisan db:seed --class=UserSeeder
```

---

## Seeder Execution Order

The `DatabaseSeeder` orchestrates all seeders in the correct dependency order:

### 1. **Base Configuration Data** (No Dependencies)
- `ProjectTypeSeeder` - 10 project types
- `ProjectStatusSeeder` - 7 project statuses with color codes
- `DocumentCategorySeeder` - 10 document categories
- `RegionSeeder` - CARAGA region + 5 provinces with coordinates

### 2. **User Management** (No Dependencies)
- `PermissionSeeder` - 29 system permissions
- `RoleSeeder` - 7 roles with assigned permissions
- `DepartmentSeeder` - 15 DA-CARAGA departments

### 3. **Users** (Depends on Roles & Departments)
- `UserSeeder` - 15 users across all roles

### 4. **Main Content** (Depends on Types, Statuses, Departments, Regions)
- `ProjectSeeder` - 20 agricultural projects
- `CropProductionSeeder` - 300+ crop production records (2023-2025)
- `LivestockStatisticSeeder` - 400+ livestock statistics (2023-2025)

### 5. **Content with User Dependencies**
- `NewsUpdateSeeder` - 15 news updates
- `DocumentSeeder` - 20 documents with category associations

---

## Detailed Seeder Information

### 1. ProjectTypeSeeder
**Records:** 10 project types

**Data:**
- Crop Development Program
- Livestock Development Program
- Fisheries and Aquaculture
- Agricultural Infrastructure
- High-Value Crops Development
- Irrigation and Water Management
- Agricultural Mechanization
- Post-Harvest Facilities
- Organic Agriculture Program
- Climate-Resilient Agriculture

---

### 2. ProjectStatusSeeder
**Records:** 7 statuses with color codes

**Data:**
| Status | Color Code | Description |
|--------|-----------|-------------|
| Planning | #6c757d | Project in planning phase |
| On Track | #28a745 | Project progressing as planned |
| At Risk | #ffc107 | Project facing challenges |
| Delayed | #fd7e14 | Project behind schedule |
| Completed | #007bff | Project successfully completed |
| On Hold | #6c757d | Project temporarily paused |
| Cancelled | #dc3545 | Project cancelled |

---

### 3. DocumentCategorySeeder
**Records:** 10 categories

**Data:**
- Annual Reports
- Policy Guidelines
- Technical Papers
- Budget Documents
- Project Proposals
- Monitoring Reports
- Statistical Reports
- Training Materials
- Legal Documents
- Extension Services

---

### 4. RegionSeeder
**Records:** 6 regions (1 main region + 5 provinces)

**Data:**
| Code | Name | Type | Coordinates |
|------|------|------|-------------|
| XIII | CARAGA | region | 8.9475, 125.5283 |
| AGN | Agusan del Norte | province | 8.9475, 125.5283 |
| AGS | Agusan del Sur | province | 8.5567, 125.9800 |
| SUN | Surigao del Norte | province | 9.7833, 125.4833 |
| SUS | Surigao del Sur | province | 8.6500, 126.1667 |
| DIN | Dinagat Islands | province | 10.1283, 125.6050 |

---

### 5. PermissionSeeder
**Records:** 29 permissions

**Categories:**
- **Projects:** view, create, update, delete
- **Progress Reports:** view, create, update, delete
- **Crop Production:** view, create, update, delete
- **Livestock Statistics:** view, create, update, delete
- **News Updates:** view, create, update, delete, publish
- **Documents:** view, upload, update, delete, approve
- **Users:** view, create, update, delete
- **Settings:** view, update

---

### 6. RoleSeeder
**Records:** 7 roles with assigned permissions

**Data:**
| Role | Permissions Count | Description |
|------|-------------------|-------------|
| System Administrator | All (29) | Full system access |
| Regional Director | 26 | Management and oversight |
| Department Head | 22 | Department management |
| Project Manager | 18 | Project operations |
| Agricultural Technician | 12 | Field operations and data entry |
| Data Encoder | 8 | Data entry only |
| Public Viewer | 5 | Read-only public access |

---

### 7. DepartmentSeeder
**Records:** 15 DA-CARAGA departments

**Data:**
- Office of the Regional Executive Director
- Planning, Monitoring and Evaluation Division
- Rice Program
- High-Value Crops Development Program
- Livestock Development Division
- Fisheries and Aquatic Resources Division
- Agricultural Extension Services
- Field Operations Division
- Agricultural Engineering Division
- Organic Agriculture Program
- Research and Development
- Regulatory Division
- Marketing Assistance Division
- Agribusiness and Marketing Assistance Division
- Administrative Division

---

### 8. UserSeeder
**Records:** 15 users across all roles

**Default Password:** `Password123!` (for all users)

**Key Users:**

| Full Name | Username | Email | Role | Department |
|-----------|----------|-------|------|------------|
| Juan Dela Cruz | admin | admin@da-caraga.gov.ph | System Administrator | Office of the Regional Executive Director |
| Maria Santos-Rodriguez | mrodriguez | director@da-caraga.gov.ph | Regional Director | Office of the Regional Executive Director |
| Roberto Villanueva | rvillanueva | rvillanueva@da-caraga.gov.ph | Department Head | Rice Program |
| Carmen Reyes-Lopez | clopez | clopez@da-caraga.gov.ph | Department Head | High-Value Crops Development Program |
| Antonio Mendoza | amendoza | amendoza@da-caraga.gov.ph | Department Head | Livestock Development Division |
| Elena Garcia-Cruz | ecruz | ecruz@da-caraga.gov.ph | Project Manager | Field Operations Division |
| Ferdinand Aquino | faquino | faquino@da-caraga.gov.ph | Project Manager | Agricultural Engineering Division |
| Rosalinda Fernandez | rfernandez | rfernandez@da-caraga.gov.ph | Project Manager | Fisheries Division |
| Jose Ramos | jramos | jramos@da-caraga.gov.ph | Agricultural Technician | Agricultural Extension Services |
| Public User | public | public@example.com | Public Viewer | - |

**Login Credentials for Testing:**
```
Username: admin
Password: Password123!
```

---

### 9. ProjectSeeder
**Records:** 20 agricultural projects

**Budget Range:** ₱18,000,000 - ₱125,000,000

**Sample Projects:**
- Hybrid Rice Production Enhancement in Agusan del Norte (₱45M)
- High-Value Crops Development in Surigao del Sur (₱38M)
- Agusan Valley Irrigation System Rehabilitation (₱125M)
- CARAGA Livestock Development and Breeding Program (₱52M)
- Farm-to-Market Roads Construction Phase 2 (₱85M)
- Organic Agriculture Certification Support Program (₱28M)
- Climate-Resilient Agriculture Initiative (₱67M)

**Coverage:** All 5 CARAGA provinces with actual geographic coordinates

---

### 10. CropProductionSeeder
**Records:** 300+ crop production entries

**Years Covered:** 2023, 2024, 2025

**Crops:**
- **Rice:** 150,000 - 220,000 metric tons per province
- **Corn:** 68,000 - 125,000 metric tons per province
- **Coconut:** 45,000 - 92,000 metric tons per province
- **Banana:** 18,000 - 58,000 metric tons per province
- **Cacao:** 4,000 - 9,500 metric tons (select provinces)
- **Coffee:** 3,000 - 7,500 metric tons (select provinces)
- **Abaca:** 7,000 - 14,500 metric tons (coastal provinces)

**Features:**
- Quarterly harvest data with seasonal patterns
- Province-specific crop specialization
- Annual growth trends (3% average)
- Realistic seasonal variations

---

### 11. LivestockStatisticSeeder
**Records:** 400+ livestock statistics

**Years Covered:** 2023, 2024, 2025

**Livestock Types:**
- **Cattle:** 80,000 - 115,000 heads per province
- **Carabao:** 48,000 - 78,000 heads per province
- **Swine:** 185,000 - 285,000 heads per province
- **Goat:** 35,000 - 58,000 heads per province
- **Poultry:** 950,000 - 1,450,000 heads per province
- **Native Chicken:** 180,000 - 250,000 heads
- **Duck:** 45,000 - 75,000 heads
- **Dairy Cattle:** 3,500 - 5,500 heads (Agusan provinces)
- **Sheep:** 2,500 - 4,500 heads (upland areas)

**Features:**
- Quarterly census data
- 3% annual growth trend
- 5% growth for dairy (specialty livestock)
- Province-specific livestock profiles

---

### 12. NewsUpdateSeeder
**Records:** 15 news articles

**Sample Headlines:**
- "DA-CARAGA Distributes 50,000 Bags of Certified Rice Seeds to Agusan Farmers"
- "CARAGA Cacao Industry Shows Promising Growth with 800 New Farmer Beneficiaries"
- "Farm-to-Market Road Project Completed in Agusan del Sur"
- "Agricultural Mechanization Program Delivers 70 Units of Farm Equipment"
- "DA-CARAGA Launches Digital Agriculture Platform for Farmers"

**Features:**
- Published dates ranging from 96 days ago to 5 days ago
- Featured articles (5 featured, 10 regular)
- Image URLs for media display
- Realistic content about CARAGA agricultural programs

---

### 13. DocumentSeeder
**Records:** 20 documents across all categories

**Sample Documents:**

**Annual Reports:**
- CARAGA Agricultural Accomplishment Report 2024
- CARAGA Regional Development Plan 2023-2028

**Policy Guidelines:**
- Guidelines on Rice Production Enhancement Program Implementation
- Organic Agriculture Certification Standards and Procedures

**Technical Papers:**
- Climate-Resilient Rice Varieties for CARAGA Region
- Cacao Production Technology Manual for Surigao del Sur

**Budget Documents:**
- CARAGA Agricultural Budget Utilization Report Q1 2025
- Program Budget Allocation 2025

**Project Proposals:**
- Regional Rice Processing Complex Project Proposal
- Cold Storage Facilities Project Concept Paper

**Monitoring Reports:**
- CARAGA Agricultural Projects Monitoring Report September 2024
- Farm-to-Market Roads Project Progress Report

**Statistical Reports:**
- CARAGA Crop Production Statistics 2023
- CARAGA Livestock and Poultry Census Report 2024

**Training Materials:**
- Integrated Pest Management Training Module
- Climate-Smart Agriculture Practices for CARAGA Farmers

**Legal Documents:**
- Memorandum of Agreement - DA-CARAGA and Local Government Units

**Extension Services:**
- Fall Armyworm Management Advisory
- Seasonal Crop Calendar for CARAGA Region

**Features:**
- Multiple category associations (pivot table)
- Realistic file paths
- Published dates throughout 2023-2025
- Comprehensive descriptions

---

## Data Summary

### Total Records Created:
- **10** Project Types
- **7** Project Statuses
- **10** Document Categories
- **6** Regions (CARAGA + 5 provinces)
- **29** Permissions
- **7** Roles
- **15** Departments
- **15** Users
- **20** Projects
- **300+** Crop Production Records
- **400+** Livestock Statistics
- **15** News Updates
- **20** Documents

### **Grand Total: ~950+ database records**

---

## Testing the Seeded Data

### 1. Login as Administrator
```
URL: /api/login
Method: POST
Body: {
  "username": "admin",
  "password": "Password123!"
}
```

### 2. Test Public Endpoints (No Auth Required)
```bash
# Get all projects (public view)
GET /api/projects

# Get specific project
GET /api/projects/{id}

# Get news updates
GET /api/news

# Get agricultural data
GET /api/crop-production
GET /api/livestock-statistics

# Get documents
GET /api/documents
```

### 3. Test Protected Endpoints (Require Auth)
```bash
# Create new project (requires auth)
POST /api/projects
Headers: Authorization: Bearer {token}

# Update project
PUT /api/projects/{id}

# Delete project
DELETE /api/projects/{id}
```

### 4. Test RBAC (Role-Based Access Control)
- Login as different users to test permission levels
- Public users see limited project details (no budget, no team members)
- Authenticated users see full project details including financials

---

## Verification Queries

### Check seeded data with SQL:
```sql
-- Count all projects
SELECT COUNT(*) FROM projects;

-- Count users by role
SELECT r.name as role, COUNT(u.id) as user_count
FROM users u
JOIN roles r ON u.role_id = r.id
GROUP BY r.name;

-- Check crop production data
SELECT crop_name, COUNT(*) as records, SUM(production_volume) as total_volume
FROM crop_productions
GROUP BY crop_name;

-- Check livestock statistics
SELECT livestock_type, COUNT(*) as records, AVG(population) as avg_population
FROM livestock_statistics
GROUP BY livestock_type;

-- Check news updates
SELECT title, published_at, is_featured
FROM news_updates
ORDER BY published_at DESC;

-- Check documents with categories
SELECT d.title, GROUP_CONCAT(dc.name) as categories
FROM documents d
LEFT JOIN document_category_pivot dcp ON d.id = dcp.document_id
LEFT JOIN document_categories dc ON dcp.document_category_id = dc.id
GROUP BY d.id, d.title;
```

---

## Customization

### Adding More Data

To add more seeded data, you can:

1. **Modify existing seeders:**
   - Edit the array of data in any seeder file
   - Add new records following the existing pattern

2. **Create new seeders:**
   ```bash
   php artisan make:seeder YourNewSeeder
   ```

3. **Register new seeder in DatabaseSeeder:**
   ```php
   $this->call([
       // ... existing seeders
       YourNewSeeder::class,
   ]);
   ```

### Resetting Specific Data

To reset only specific data:
```bash
# Drop all tables and re-migrate
php artisan migrate:fresh

# Run specific seeder
php artisan db:seed --class=ProjectSeeder
```

---

## Important Notes

1. **Password Security:** All users have the same default password (`Password123!`) for testing purposes. **Change this in production!**

2. **Foreign Keys:** Seeders must run in the correct order due to foreign key constraints. The `DatabaseSeeder` handles this automatically.

3. **File Paths:** Document file paths (`/storage/documents/...`) are placeholders. Actual files would need to be uploaded separately.

4. **Image URLs:** News update image URLs (`/storage/news/...`) are placeholders. Actual images would need to be uploaded.

5. **Geographic Coordinates:** All latitude/longitude coordinates are actual CARAGA locations for mapping accuracy.

6. **Data Realism:** All production volumes, populations, and budgets are based on realistic CARAGA agricultural data patterns.

---

## Troubleshooting

### Issue: Foreign Key Constraint Errors
**Solution:** Ensure migrations are run before seeding:
```bash
php artisan migrate:fresh --seed
```

### Issue: Duplicate Entry Errors
**Solution:** Reset the database before re-seeding:
```bash
php artisan migrate:fresh
php artisan db:seed
```

### Issue: "Class not found" Errors
**Solution:** Clear and regenerate autoload files:
```bash
composer dump-autoload
php artisan db:seed
```

### Issue: Permission Denied
**Solution:** Ensure database user has proper permissions:
```sql
GRANT ALL PRIVILEGES ON database_name.* TO 'user'@'localhost';
FLUSH PRIVILEGES;
```

---

## Support

For questions or issues with the seeders:
1. Check the seeder class files in `database/seeders/`
2. Review the migration files in `database/migrations/`
3. Verify database connections in `.env`
4. Check Laravel logs in `storage/logs/laravel.log`

---

**Last Updated:** 2025-10-06
**CARAGA PMIS Version:** 1.0
**Laravel Version:** 11.x

## Default acccount
Username: admin
Password: Password123!