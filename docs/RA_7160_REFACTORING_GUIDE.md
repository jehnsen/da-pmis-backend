# RA 7160 LGU Governance Refactoring Guide

## Overview

This system has been refactored from a **Regional DA (Department of Agriculture)** structure to a **Provincial LGU (Local Government Unit) Governance Intelligence Platform** fully aligned with **RA 7160 (Local Government Code of 1991)**.

## System Pivot Summary

### FROM: Regional DA System
- **Focus**: Agricultural projects only
- **Hierarchy**: Field → Municipal → Provincial → Regional Director
- **Sectors**: Agriculture departments only
- **Entry Point**: Field officer submission

### TO: Provincial LGU Governance Platform
- **Focus**: Multi-sector provincial governance (RA 7160)
- **Hierarchy**: Barangay → Municipal (MPDO) → Provincial (PPDO) → Governor
- **Sectors**: 4 LGU sectors (SS, ES, IEM, GPS)
- **Entry Point**: Barangay Development Council

---

## RA 7160 Compliance Features

### 1. Four LGU Sectors (Mandatory for Provincial Governance)

| Sector Code | Name | Description | Examples |
|-------------|------|-------------|----------|
| **SS** | Social Services | Health, Education, Social Welfare, Community Development | Health centers, scholarship programs, DSWD projects |
| **ES** | Economic Services | Agriculture, Fisheries, Tourism, Trade & Industry | Farm-to-market roads, agri-tech programs, tourism promotion |
| **IEM** | Infrastructure & Environmental Management | Public Works, Utilities, Environment, Disaster Risk Reduction | Road construction, water systems, DRRM programs |
| **GPS** | General Public Services | Administration, Planning, Legal, Budget, Finance | PPDO initiatives, governance reforms, capacity building |

### 2. Approval Workflow (RA 7160 Chain of Command)

```
┌─────────────────────────────────────────────────────────────────┐
│                    RA 7160 APPROVAL HIERARCHY                   │
└─────────────────────────────────────────────────────────────────┘

1. DRAFT
   ↓ (Submit for Approval)
2. PENDING BARANGAY
   └─ Barangay Development Council (BDC)
      ↓ (Approve)
3. PENDING MUNICIPAL
   └─ Municipal Planning & Development Office (MPDO)
      ↓ (Validate & Approve)
4. PENDING PROVINCIAL
   └─ Provincial Planning & Development Office (PPDO)
      ↓ (Technical Review & Approve)
5. PENDING GOVERNOR
   └─ Office of the Provincial Governor
      ↓ (Final Approval)
6. APPROVED
```

### 3. Key Terminology Changes

| Old (DA System) | New (LGU RA 7160) | Description |
|-----------------|-------------------|-------------|
| Field Officer | Barangay Development Council Officer | Entry point for community-based projects |
| Municipal Officer | Municipal Planning Officer (MPDO) | Validates Barangay proposals |
| Provincial Officer | Provincial Planning Officer (PPDO) | Technical review at provincial level |
| Regional Director | Provincial Governor | Final approval authority |
| Department | LGU Sector | Broader governance scope |
| Regional | Provincial | Geographic scope adjustment |

---

## Database Schema Changes

### New Tables

#### 1. `lgu_sectors`
```sql
CREATE TABLE lgu_sectors (
    id BIGINT PRIMARY KEY,
    name VARCHAR(255),          -- e.g., "Economic Services"
    code VARCHAR(10) UNIQUE,    -- e.g., "ES"
    description TEXT,
    icon VARCHAR(255),          -- UI icon
    color_code VARCHAR(7),      -- Hex color for dashboards
    is_active BOOLEAN,
    display_order INT,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

**Pre-seeded Data:**
- Social Services (SS)
- Economic Services (ES)
- Infrastructure & Environmental Management (IEM)
- General Public Services (GPS)

### Modified Tables

#### 2. `projects` Table Additions
```sql
ALTER TABLE projects ADD COLUMN (
    sector_id BIGINT,              -- References lgu_sectors (replaces department_id)
    municipality_id BIGINT,        -- References municipalities
    province_id BIGINT,            -- References provinces
    barangay VARCHAR(255),         -- Barangay name (free text)

    -- Approval status enum updated:
    approval_status ENUM(
        'draft',
        'pending_barangay',        -- NEW
        'pending_municipal',
        'pending_provincial',
        'pending_governor',        -- CHANGED from pending_regional
        'approved',
        'rejected'
    )
);
```

#### 3. `project_approvals` Table Updates
```sql
ALTER TABLE project_approvals MODIFY COLUMN
    level ENUM(
        'barangay',               -- NEW (replaces 'field')
        'municipal',
        'provincial',
        'governor'                -- CHANGED from 'regional'
    );
```

---

## Migration Sequence

**IMPORTANT**: Run migrations in this exact order:

```bash
# 1. Create LGU sectors table
php artisan migrate --path=database/migrations/2026_01_29_060001_create_lgu_sectors_table.php

# 2. Refactor approval levels (updates enums and migrates data)
php artisan migrate --path=database/migrations/2026_01_29_060000_refactor_approval_levels_for_lgu_governance.php

# 3. Add sector and location fields to projects
php artisan migrate --path=database/migrations/2026_01_29_060002_add_sector_and_location_to_projects_table.php

# Or run all together:
php artisan migrate
```

### Data Migration Notes

The migration automatically handles:
- `pending_regional` → `pending_governor`
- `level='regional'` → `level='governor'`
- `level='field'` → `level='barangay'`
- Auto-mapping existing departments to LGU sectors based on name patterns

---

## Code Refactoring Summary

### 1. Models Updated

#### `Project.php`
**New Relationships:**
```php
public function sector(): BelongsTo              // LGU sector (SS/ES/IEM/GPS)
public function municipality(): BelongsTo        // Geographic routing
public function province(): BelongsTo            // Provincial scope
```

**Updated Methods:**
```php
getCurrentPendingLevel()           // Now returns: barangay|municipal|provincial|governor
getApprovalStatusDisplayAttribute() // RA 7160 terminology (e.g., "Pending Governor Approval")
isPendingApproval()                // Updated for 4-level chain
```

#### `ProjectApproval.php`
**Updated Display:**
```php
getLevelDisplayAttribute() // Returns:
// - 'Barangay Development Council'
// - 'Municipal Planning & Development Office (MPDO)'
// - 'Provincial Planning & Development Office (PPDO)'
// - 'Office of the Provincial Governor'
```

#### New: `LguSector.php`
```php
// Relationships
public function projects(): HasMany

// Computed Attributes
getTotalBudgetAttribute()        // Sum of project budgets
getTotalDisbursedAttribute()     // Sum of disbursements
getUtilizationRateAttribute()    // (disbursed / budget) * 100
```

### 2. Repository Updates

#### `ProjectApprovalRepository.php`

**Updated Flow Maps:**
```php
private array $approvalFlow = [
    'draft' => 'pending_barangay',              // START at Barangay
    'pending_barangay' => 'pending_municipal',
    'pending_municipal' => 'pending_provincial',
    'pending_provincial' => 'pending_governor',
    'pending_governor' => 'approved',           // FINAL
];

private array $roleToLevelMap = [
    'barangay_development_council' => 'barangay',
    'municipal_planning_officer' => 'municipal',
    'provincial_planning_officer' => 'provincial',
    'governor' => 'governor',
];
```

**Updated Submission:**
- First pending status: `pending_barangay` (was `pending_municipal`)
- Initial level: `barangay` (was `field`)
- Notifies Barangay Development Council officers

### 3. Seeders Updated

#### `RoleSeeder.php`
**New Roles:**
- Provincial Governor (final approval)
- Provincial Planning Officer (PPDO)
- Municipal Planning Officer (MPDO)
- Barangay Development Council Officer
- Sector Head (SS/ES/IEM/GPS)

**Removed Roles:**
- Regional Director
- Field Officer
- Department Head
- Agricultural Technician

---

## API Endpoints Impact

### Approval Status Values (Breaking Change)

**Old Values:**
```json
{
  "approval_status": "pending_regional"
}
```

**New Values:**
```json
{
  "approval_status": "pending_governor"
}
```

### New Approval Statistics Response

```json
{
  "total_projects": 100,
  "by_status": {
    "draft": 10,
    "pending_barangay": 5,      // NEW
    "pending_municipal": 8,
    "pending_provincial": 6,
    "pending_governor": 4,       // CHANGED
    "approved": 65,
    "rejected": 2
  }
}
```

### Project Response Updates

**New Fields:**
```json
{
  "id": 1,
  "title": "Farm-to-Market Road Construction",
  "sector_id": 2,                           // NEW
  "sector": {                               // NEW
    "id": 2,
    "name": "Economic Services",
    "code": "ES"
  },
  "municipality_id": 15,                    // NEW
  "municipality": {                         // NEW
    "id": 15,
    "name": "Butuan City"
  },
  "province_id": 1,                         // NEW
  "province": {                             // NEW
    "id": 1,
    "name": "Agusan del Norte"
  },
  "barangay": "Poblacion",                  // NEW
  "approval_status": "pending_governor",    // UPDATED
  "approval_status_display": "Pending Governor Approval"
}
```

---

## Dashboard Metrics (RA 7160 Alignment)

### Sector-Based Analytics

All 10 governance metrics now support filtering by:
- **Sector** (SS/ES/IEM/GPS)
- **Province** (instead of Region)
- **Municipality**
- **Barangay**

### Compliance Metrics Alignment

| Metric | RA 7160 Application | COA/DBM/NEDA Use Case |
|--------|---------------------|------------------------|
| Physical vs Financial Variance | Track slippage across LGU sectors | COA audit compliance |
| Budget Variance Heatmap | Monitor utilization by sector (SS/ES/IEM/GPS) | DBM performance evaluation |
| Milestone Completion Tracker | Ensure LDIP/AIP timeline adherence | NEDA development plan monitoring |
| Target Achievement KPI | Measure LGU service delivery effectiveness | Performance management system |
| Cost Efficiency Metrics | Cost per beneficiary across sectors | DBM budget justification |

---

## User Roles & Permissions

### Approval Authority Matrix

| Role | Approval Level | Can Approve | Can Request Changes | Can Reject |
|------|----------------|-------------|---------------------|------------|
| Barangay Development Council Officer | Barangay | ✅ pending_barangay | ✅ | ✅ |
| Municipal Planning Officer (MPDO) | Municipal | ✅ pending_municipal | ✅ | ✅ |
| Provincial Planning Officer (PPDO) | Provincial | ✅ pending_provincial | ✅ | ✅ |
| Provincial Governor | Governor | ✅ pending_governor | ✅ | ✅ |
| System Administrator | Governor | ✅ pending_governor | ✅ | ✅ |

### Role Descriptions (RA 7160 Context)

**Barangay Development Council Officer:**
- Entry point for community-based projects
- Reviews Barangay Development Plan (BDP) submissions
- Validates beneficiary lists and community participation
- **RA 7160 Provision**: Section 106 (Barangay Development Council)

**Municipal Planning Officer (MPDO):**
- Validates projects from barangays
- Ensures alignment with Municipal Development Plan
- Consolidates projects for Annual Investment Plan (AIP)
- **RA 7160 Provision**: Section 476 (Local Development Council)

**Provincial Planning Officer (PPDO):**
- Technical review of provincial-level projects
- Validates LDIP (Local Development Investment Program) alignment
- Coordinates with NEDA on development planning
- **RA 7160 Provision**: Section 477 (Provincial Development Council)

**Provincial Governor:**
- Final approval authority for all provincial projects
- Oversight of all LGU sectors (SS, ES, IEM, GPS)
- Budget allocation and disbursement authority
- **RA 7160 Provision**: Section 455 (Chief Executive: Powers, Duties, Functions)

---

## Testing the New Workflow

### Test Case 1: Barangay-to-Governor Approval Chain

```bash
# 1. Create project with sector and location
POST /api/projects
{
  "title": "Community Health Center Construction",
  "sector_id": 1,              # Social Services
  "municipality_id": 10,
  "province_id": 1,
  "barangay": "San Vicente",
  "budget": 5000000,
  ...
}
# Expected: approval_status = "draft"

# 2. Submit for approval (as Barangay officer)
POST /api/projects/{id}/submit-for-approval
# Expected: approval_status = "pending_barangay"

# 3. Approve at Barangay level
POST /api/projects/{id}/approve
# Expected: approval_status = "pending_municipal"

# 4. Approve at Municipal (MPDO) level
POST /api/projects/{id}/approve
# Expected: approval_status = "pending_provincial"

# 5. Approve at Provincial (PPDO) level
POST /api/projects/{id}/approve
# Expected: approval_status = "pending_governor"

# 6. Final approval by Governor
POST /api/projects/{id}/approve
# Expected: approval_status = "approved"
```

### Test Case 2: Sector-Based Dashboard Filtering

```bash
# Get budget variance by LGU sector
GET /api/dashboard/budget-variance-heatmap?sector=ES
# Expected: Returns Economic Services projects only

# Get projects pending at barangay level
GET /api/projects/pending-approval?level=barangay
# Expected: Returns only pending_barangay projects
```

---

## Backward Compatibility Notes

### Breaking Changes

1. **Approval Status Enum:**
   - `pending_regional` no longer exists → use `pending_governor`
   - `pending_barangay` is now the first pending state

2. **Role Names:**
   - `Regional Director` removed → use `Provincial Governor`
   - `Field Officer` removed → use `Barangay Development Council Officer`

3. **Department vs Sector:**
   - `department_id` is now legacy (still exists for backward compatibility)
   - Use `sector_id` for all new projects

### Migration Path for Existing Clients

**Option 1: Auto-Migration (Recommended)**
- Run migrations to auto-convert data
- Update client code to use new enum values
- Add sector filtering to existing queries

**Option 2: Dual Support (Temporary)**
- Keep `department_id` populated alongside `sector_id`
- Map old role names to new ones in authentication layer
- Provide API aliases for old endpoints

---

## RA 7160 Terminology Reference

| Term | Definition | Legal Basis |
|------|------------|-------------|
| **BDP** | Barangay Development Plan | RA 7160, Sec. 106 |
| **AIP** | Annual Investment Plan | RA 7160, Sec. 476 |
| **LDIP** | Local Development Investment Program | RA 7160, Sec. 477 |
| **MPDO** | Municipal Planning & Development Office | RA 7160, Sec. 476 |
| **PPDO** | Provincial Planning & Development Office | RA 7160, Sec. 477 |
| **IRA** | Internal Revenue Allotment | RA 7160, Sec. 284 |
| **NTA** | National Tax Allotment (now IRA) | Mandanas-Garcia Ruling |
| **20% Development Fund** | Minimum allocation for development projects | RA 7160, Sec. 287 |

---

## Deployment Checklist

- [ ] Backup database before migration
- [ ] Run migrations in correct sequence
- [ ] Verify LGU sectors are seeded (4 sectors: SS, ES, IEM, GPS)
- [ ] Update user roles (remove Regional Director, add Governor)
- [ ] Test approval workflow from barangay to governor
- [ ] Update API documentation with new enum values
- [ ] Update frontend to use new sector-based filtering
- [ ] Verify all 10 dashboard metrics work with sector filtering
- [ ] Test geographic routing (municipality → province)
- [ ] Update client applications to handle new approval statuses
- [ ] Train users on RA 7160 terminology
- [ ] Update deployment scripts to reference Provincial LGU platform

---

## Next Steps

### Phase 1: Core Refactoring (Completed)
- ✅ Database schema migration
- ✅ Model and repository updates
- ✅ Approval workflow refactoring
- ✅ Role and permission updates

### Phase 2: Dashboard Integration (In Progress)
- [ ] Update all 10 dashboard metrics for sector-based filtering
- [ ] Add provincial vs regional comparison views
- [ ] Implement Barangay-level KPI tracking
- [ ] Create LGU sector performance dashboard

### Phase 3: Advanced Features (Planned)
- [ ] Integration with DILG reporting
- [ ] NEDA LDIP export functionality
- [ ] COA audit trail report generation
- [ ] DBM budget compliance validation
- [ ] Public transparency portal (citizen engagement)

---

**Document Version:** 1.0
**Last Updated:** 2026-01-29
**Maintained By:** DA-PMIS Development Team
**Legal Compliance:** RA 7160 (Local Government Code of 1991)
