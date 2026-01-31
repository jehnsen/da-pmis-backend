# LGU Sector-Department Mapping

## Quick Reference Guide

This document provides a quick reference for which departments belong to which LGU sectors in the DA-PMIS system.

---

## Sector Overview

| Sector Code | Sector Name | Departments Count | Color |
|-------------|-------------|-------------------|-------|
| **GPS** | General Public Services | 4 | Purple |
| **ES** | Economic Services | 9 | Green |
| **IEM** | Infrastructure & Environmental Management | 2 | Orange |
| **SS** | Social Services | 0 | Blue |

---

## Complete Mapping

### 🟣 General Public Services (GPS)

**Sector ID**: 4
**Code**: GPS
**Total Departments**: 4

| ID | Department Name | Description |
|----|----------------|-------------|
| 1 | Office of the Regional Executive Director | Overall leadership and policy implementation |
| 11 | Regulatory Division | Agricultural regulations and compliance monitoring |
| 12 | Planning, Monitoring and Evaluation Division | Strategic planning and performance evaluation |
| 13 | Finance and Administrative Division | Budget, procurement, and administrative services |

**Functional Areas**: Administration, Planning, Legal, Budget, Governance

---

### 🟢 Economic Services (ES)

**Sector ID**: 2
**Code**: ES
**Total Departments**: 9

| ID | Department Name | Description |
|----|----------------|-------------|
| 2 | Field Operations Division | Field implementation and farmer assistance |
| 3 | Agribusiness and Marketing Assistance Division | Market linkages and value chain development |
| 4 | Rice Program | Rice production and irrigation development |
| 5 | High-Value Crops Development Program | Cacao, coffee, abaca, banana development |
| 6 | Livestock Development Division | Livestock production and animal health |
| 7 | Fisheries and Aquatic Resources Division | Fisheries and aquaculture development |
| 10 | Research and Development Division | Agricultural research and innovation |
| 14 | Organic Agriculture Program | Organic farming and certification |
| 15 | Agricultural Extension Services | Farmer training and technology transfer |

**Functional Areas**: Agriculture, Fisheries, Tourism, Trade & Industry, Economic Development

---

### 🟠 Infrastructure & Environmental Management (IEM)

**Sector ID**: 3
**Code**: IEM
**Total Departments**: 2

| ID | Department Name | Description |
|----|----------------|-------------|
| 8 | Agricultural Engineering Division | Farm-to-market roads, irrigation, post-harvest facilities |
| 9 | Regional Agricultural and Biosystems Engineering Division | Farm mechanization and engineering support |

**Functional Areas**: Public Works, Utilities, Infrastructure, Environmental Management, DRRM

---

### 🔵 Social Services (SS)

**Sector ID**: 1
**Code**: SS
**Total Departments**: 0 (Reserved for future)

**Potential Future Departments**:
- Health Services Division
- Education and Training Division
- Social Welfare Division
- Community Development Division

**Functional Areas**: Health, Education, Social Welfare, Community Development

---

## API Usage Examples

### Get All Economic Services Departments
```bash
GET /api/lgu-sectors/2/departments
```

### Get Infrastructure Sector Overview with Departments
```bash
GET /api/lgu-sectors/3/departments-overview?fiscal_year=2026
```

### Get GPS Department List without Statistics
```bash
GET /api/lgu-sectors/4/departments?include_stats=false
```

---

## Statistics by Sector

### Current Project Distribution (as of 2026-01-31)

| Sector | Departments | Total Projects | Total Budget |
|--------|-------------|----------------|--------------|
| GPS | 4 | TBD | TBD |
| ES | 9 | 15 | ₱654,000,000 |
| IEM | 2 | 0 | ₱0 |
| SS | 0 | 0 | ₱0 |

---

## Database Query Examples

### Get All Departments in Economic Services
```sql
SELECT d.*
FROM departments d
JOIN lgu_sectors s ON d.sector_id = s.id
WHERE s.code = 'ES';
```

### Count Departments per Sector
```sql
SELECT
    s.name as sector_name,
    s.code,
    COUNT(d.id) as department_count
FROM lgu_sectors s
LEFT JOIN departments d ON s.id = d.sector_id
GROUP BY s.id, s.name, s.code
ORDER BY s.display_order;
```

### Get Projects by Sector Through Departments
```sql
SELECT
    s.name as sector,
    d.name as department,
    COUNT(p.id) as projects_count,
    SUM(p.budget) as total_budget
FROM lgu_sectors s
LEFT JOIN departments d ON s.id = d.sector_id
LEFT JOIN projects p ON d.id = p.department_id
GROUP BY s.id, d.id;
```

---

## Notes

- All departments are currently active
- Sector assignments follow RA 7160 functional categorization
- Departments can be reassigned to different sectors if needed
- New departments should be assigned to appropriate sectors upon creation
- Social Services (SS) sector is reserved for future health/education/welfare departments

---

**Last Updated**: 2026-01-31
**Version**: 3.1
**Related Documentation**:
- `docs/SECTOR_DEPARTMENT_HIERARCHY.md` - Full implementation details
- `docs/LGU_SECTOR_REPORTS_API.md` - API documentation
