# Sector-Department Hierarchy Implementation

## Overview

This document describes the implementation of the **LGU Sector → Department** hierarchy in the PLGU-GIP system, enabling proper organizational structure compliance with **RA 7160 (Local Government Code of 1991)**.

## Implementation Summary

### Date Completed
**2026-01-31**

### What Was Added

#### 1. Database Schema Changes
- **Migration**: `2026_01_31_034052_add_sector_id_to_departments_table.php`
- Added `sector_id` foreign key to `departments` table
- Relationship: `departments.sector_id` → `lgu_sectors.id`
- Constraint: `ON DELETE SET NULL` (preserves department if sector is deleted)

#### 2. Model Relationships

**Department Model** ([Department.php:22-28](app/Models/Department.php#L22-L28)):
```php
public function sector(): BelongsTo
{
    return $this->belongsTo(LguSector::class, 'sector_id');
}
```

**LguSector Model** ([LguSector.php:45-51](app/Models/LguSector.php#L45-L51)):
```php
public function departments(): HasMany
{
    return $this->hasMany(Department::class, 'sector_id');
}
```

#### 3. New API Endpoints

Two new public endpoints added to `/api/lgu-sectors` route group:

| Endpoint | Description |
|----------|-------------|
| `GET /api/lgu-sectors/{id}/departments` | Get all departments under a specific sector with statistics |
| `GET /api/lgu-sectors/{id}/departments-overview` | Comprehensive sector overview with department-level breakdown |

#### 4. Department-Sector Assignments

Departments have been assigned to sectors based on their functional responsibilities:

**General Public Services (GPS)** - 4 Departments:
- Office of the Regional Executive Director
- Regulatory Division
- Planning, Monitoring and Evaluation Division
- Finance and Administrative Division

**Economic Services (ES)** - 9 Departments:
- Field Operations Division
- Agribusiness and Marketing Assistance Division
- Rice Program
- High-Value Crops Development Program
- Livestock Development Division
- Fisheries and Aquatic Resources Division
- Research and Development Division
- Organic Agriculture Program
- Agricultural Extension Services

**Infrastructure & Environmental Management (IEM)** - 2 Departments:
- Agricultural Engineering Division
- Regional Agricultural and Biosystems Engineering Division

**Social Services (SS)** - 0 Departments:
- (Currently no departments assigned - reserved for future health, education, social welfare departments)

---

## API Endpoint Details

### 1. Get Departments by Sector

**Endpoint**: `GET /api/lgu-sectors/{sector_id}/departments`

**Query Parameters**:
- `include_stats` (optional, default: `true`) - Include department statistics

**Response Structure**:
```json
{
  "success": true,
  "data": {
    "sector": {
      "id": 2,
      "name": "Economic Services",
      "code": "ES"
    },
    "departments": [
      {
        "id": 2,
        "name": "Field Operations Division",
        "description": "...",
        "statistics": {
          "total_projects": 12,
          "active_projects": 10,
          "total_budget": 45000000.00,
          "total_disbursed": 35000000.00,
          "utilization_rate": 77.78,
          "beneficiaries": 2500
        }
      }
    ],
    "total_departments": 9
  }
}
```

**Use Cases**:
- Display department list on sector detail pages
- Calculate sector-level statistics from department aggregation
- Filter projects by department within a sector
- Budget allocation visualization by department

---

### 2. Get Sector Overview with Department Breakdown

**Endpoint**: `GET /api/lgu-sectors/{sector_id}/departments-overview`

**Query Parameters**:
- `fiscal_year` (optional, default: current year)

**Response Structure**:
```json
{
  "success": true,
  "data": {
    "sector": {
      "id": 2,
      "name": "Economic Services",
      "code": "ES",
      "description": "...",
      "icon": "trending-up",
      "color_code": "#10B981"
    },
    "fiscal_year": "2026",
    "overview": {
      "total_projects": 15,
      "active_projects": 12,
      "total_budget": 654000000.00,
      "total_disbursed": 0.00,
      "remaining_budget": 654000000.00,
      "utilization_rate": 0.00,
      "total_beneficiaries": 0,
      "average_compliance": 0.00
    },
    "departments": [
      {
        "id": 2,
        "name": "Field Operations Division",
        "description": "...",
        "projects_count": 0,
        "total_budget": 0.00,
        "total_disbursed": 0.00,
        "remaining_budget": 0.00,
        "utilization_rate": 0.00,
        "beneficiaries": 0,
        "compliance_average": 0.00
      }
    ],
    "department_count": 9
  }
}
```

**Use Cases**:
- Sector dashboard pages (matching your UI screenshots)
- Budget allocation charts by department
- Performance comparison within a sector
- Compliance tracking per department
- COA/DBM audit reporting

---

## Hierarchy Structure

```
LGU Sector (4 sectors - RA 7160)
├── Departments (15 total)
│   ├── Projects (multiple per department)
│   │   ├── Team Members
│   │   ├── Milestones
│   │   ├── Disbursements
│   │   ├── Progress Reports
│   │   └── Approval Workflow (Barangay → Municipal → Provincial → Governor)
│   └── KPIs
└── Budget Tracking
```

**Complete Flow**:
```
Sector → Department → Project → Municipality → Barangay
```

---

## Frontend Implementation Guide

### Displaying Sector Pages (Like Your Screenshots)

Your UI shows sector pages with department breakdowns. Here's how to implement:

#### 1. Fetch Sector Overview
```javascript
// Fetch sector overview with departments
const response = await fetch('/api/lgu-sectors/2/departments-overview?fiscal_year=2026');
const data = await response.json();

// Display sector header
const sector = data.data.sector;
// sector.name = "Economic Services"
// sector.color_code = "#10B981"

// Display sector-level statistics
const overview = data.data.overview;
// overview.total_budget
// overview.total_projects
// overview.utilization_rate
// overview.total_beneficiaries

// Display department cards
data.data.departments.forEach(dept => {
  renderDepartmentCard({
    name: dept.name,
    budget: dept.total_budget,
    projects: dept.projects_count,
    utilization: dept.utilization_rate,
    beneficiaries: dept.beneficiaries
  });
});
```

#### 2. Budget Allocation Chart by Department
```javascript
const departments = data.data.departments;
const chartData = departments.map(dept => ({
  name: dept.name,
  allocated: dept.total_budget,
  disbursed: dept.total_disbursed
}));

// Render bar chart or pie chart
renderBudgetAllocationChart(chartData);
```

#### 3. Project Status Distribution
```javascript
// For each department, fetch projects
const response = await fetch('/api/projects?department_id=' + dept.id);
const projects = await response.json();

// Calculate status distribution
const statusCounts = {
  'On Track': projects.filter(p => p.status === 'On Track').length,
  'Delayed': projects.filter(p => p.status === 'Delayed').length,
  'Planning': projects.filter(p => p.status === 'Planning').length,
  'Under Review': projects.filter(p => p.status === 'Under Review').length
};

renderStatusPieChart(statusCounts);
```

---

## Database Queries

### Get All Departments in a Sector
```php
$sector = LguSector::find(2); // Economic Services
$departments = $sector->departments; // Returns collection of departments
```

### Get Department's Sector
```php
$department = Department::find(5);
$sector = $department->sector; // Returns LguSector model
```

### Get Projects in a Sector Through Departments
```php
$sector = LguSector::find(2);
$projects = Project::whereHas('department', function($query) use ($sector) {
    $query->where('sector_id', $sector->id);
})->get();
```

### Aggregate Sector Stats from Departments
```php
$sector = LguSector::find(2);
$totalBudget = $sector->departments()
    ->get()
    ->sum(function($dept) {
        return $dept->projects->sum('budget');
    });
```

---

## Migration Commands

```bash
# Run migration
php artisan migrate

# Update existing departments with sector assignments
php artisan tinker --execute="
\$es = App\Models\LguSector::where('code', 'ES')->first();
App\Models\Department::where('name', 'Field Operations Division')
    ->update(['sector_id' => \$es->id]);
"

# Rollback migration
php artisan migrate:rollback
```

---

## Testing the Endpoints

```bash
# Start server
php artisan serve

# Test departments endpoint
curl http://localhost:8000/api/lgu-sectors/2/departments

# Test departments overview
curl http://localhost:8000/api/lgu-sectors/2/departments-overview?fiscal_year=2026

# Test without stats
curl http://localhost:8000/api/lgu-sectors/2/departments?include_stats=false
```

---

## Files Modified/Created

### Created:
1. `/database/migrations/2026_01_31_034052_add_sector_id_to_departments_table.php`
2. `/docs/SECTOR_DEPARTMENT_HIERARCHY.md` (this file)

### Modified:
1. `/app/Models/Department.php` - Added sector relationship
2. `/app/Models/LguSector.php` - Added departments relationship
3. `/app/Http/Controllers/LguSectorReportController.php` - Added 2 new methods
4. `/routes/api.php` - Added 2 new routes
5. `/database/seeders/DepartmentSeeder.php` - Added sector assignments
6. `/docs/LGU_SECTOR_REPORTS_API.md` - Updated with new endpoints

---

## Future Enhancements

### Potential Improvements:
1. **Add Social Services Departments**: Create departments for health, education, social welfare
2. **Department-Level KPI Dashboard**: Endpoint for department-specific KPIs
3. **Cross-Sector Comparison**: Compare departments across different sectors
4. **Department Head Assignment**: Add user relationships to departments
5. **Budget Allocation Limits**: Set sector-level budget limits per department
6. **Department Performance Ranking**: Rank departments within a sector by metrics
7. **Historical Trends**: Track department performance over multiple fiscal years

---

## Notes

- All endpoints are **public** (no authentication required) for transparency
- Sector assignments follow **RA 7160** functional categorization
- Department `sector_id` can be `NULL` (for unassigned departments)
- Statistics are calculated in real-time (no caching yet)
- Budget values are in Philippine Pesos (PHP)
- Utilization rate = (total_disbursed / total_budget) × 100

---

## Summary

This implementation completes the **Sector → Department** hierarchy, enabling:
- ✅ Department grouping under LGU sectors
- ✅ Sector-level budget tracking from department aggregation
- ✅ Department-level performance reporting
- ✅ Hierarchical navigation (Sector → Department → Project)
- ✅ Compliance with RA 7160 organizational structure
- ✅ Frontend support for sector dashboard pages (matching your UI)

**Total Endpoints**: 7 sector-related endpoints (5 existing + 2 new)
**Total Departments**: 15 departments across 3 sectors (GPS, ES, IEM)

---

**Version**: 3.1 - Sector-Department Hierarchy
**Date**: 2026-01-31
**Status**: PRODUCTION READY
