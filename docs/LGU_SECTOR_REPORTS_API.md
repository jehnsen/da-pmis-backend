# LGU Sector Reports API Documentation

## Overview

This API provides comprehensive reporting and analytics for the 4 LGU sectors mandated by **RA 7160 (Local Government Code of 1991)**:

- **SS (Social Services)**: Health, Education, Social Welfare, Community Development
- **ES (Economic Services)**: Agriculture, Fisheries, Tourism, Trade & Industry
- **IEM (Infrastructure & Environmental Management)**: Public Works, Utilities, DRRM
- **GPS (General Public Services)**: Planning, Legal, Budget, Administration

All endpoints are **PUBLIC** for transparent governance and stakeholder oversight.

---

## Endpoints

### 1. Get All LGU Sector Reports

**Endpoint:** `GET /api/lgu-sectors` or `GET /api/lgu-sectors/reports`

**Description:** Retrieves aggregated reports for all 4 LGU sectors with comprehensive statistics.

**Query Parameters:**
- `fiscal_year` (optional): Filter by fiscal year (default: current year)
- `quarter` (optional): Filter by quarter (1-4)
- `include_stats` (optional): Include statistics (default: true)

**Request Example:**
```bash
GET /api/lgu-sectors/reports?fiscal_year=2026&include_stats=true
```

**Response Example:**
```json
{
  "success": true,
  "data": {
    "fiscal_year": "2026",
    "quarter": null,
    "sectors": [
      {
        "id": 1,
        "name": "Social Services",
        "code": "SS",
        "description": "Health, Education, Social Welfare, and Community Development programs",
        "icon": "users",
        "color_code": "#3B82F6",
        "statistics": {
          "total_projects": 15,
          "approved_projects": 12,
          "total_budget": 45000000.00,
          "total_disbursed": 32500000.00,
          "utilization_rate": 72.22,
          "approval_rate": 80.00,
          "status_breakdown": {
            "approved": 12,
            "pending_governor": 2,
            "draft": 1
          }
        }
      },
      {
        "id": 2,
        "name": "Economic Services",
        "code": "ES",
        "description": "Agriculture, Fisheries, Tourism, Trade & Industry development",
        "icon": "trending-up",
        "color_code": "#10B981",
        "statistics": {
          "total_projects": 28,
          "approved_projects": 22,
          "total_budget": 125000000.00,
          "total_disbursed": 98750000.28,
          "utilization_rate": 79.00,
          "approval_rate": 78.57,
          "status_breakdown": {
            "approved": 22,
            "pending_provincial": 4,
            "pending_municipal": 2
          }
        }
      }
    ],
    "summary": {
      "total_sectors": 4,
      "total_budget_all_sectors": 210000000.00,
      "total_disbursed_all_sectors": 165000000.28,
      "total_projects_all_sectors": 65
    }
  },
  "message": "LGU sector reports retrieved successfully"
}
```

---

### 2. Get Budget Utilization by Sector

**Endpoint:** `GET /api/lgu-sectors/budget-utilization`

**Description:** Shows budget allocation, disbursements, and utilization rates for all sectors.

**Query Parameters:**
- `fiscal_year` (optional): Filter by fiscal year (default: current year)

**Request Example:**
```bash
GET /api/lgu-sectors/budget-utilization?fiscal_year=2026
```

**Response Example:**
```json
{
  "success": true,
  "data": {
    "fiscal_year": "2026",
    "sectors": [
      {
        "sector_id": 1,
        "sector_name": "Social Services",
        "sector_code": "SS",
        "color_code": "#3B82F6",
        "total_budget": 45000000.00,
        "total_disbursed": 32500000.00,
        "remaining_budget": 12500000.00,
        "utilization_rate": 72.22,
        "projects_count": 15
      },
      {
        "sector_id": 2,
        "sector_name": "Economic Services",
        "sector_code": "ES",
        "color_code": "#10B981",
        "total_budget": 125000000.00,
        "total_disbursed": 98750000.28,
        "remaining_budget": 26249999.72,
        "utilization_rate": 79.00,
        "projects_count": 28
      }
    ],
    "total_budget": 210000000.00,
    "total_disbursed": 165000000.28,
    "average_utilization_rate": 75.61
  },
  "message": "Budget utilization data retrieved successfully"
}
```

---

### 3. Compare Sectors

**Endpoint:** `GET /api/lgu-sectors/compare`

**Description:** Comparative analysis of all sectors with ranking and insights.

**Query Parameters:**
- `fiscal_year` (optional): Filter by fiscal year (default: current year)
- `metric` (optional): Sort by metric - `budget`, `projects`, or `utilization` (default: `budget`)

**Request Example:**
```bash
GET /api/lgu-sectors/compare?fiscal_year=2026&metric=utilization
```

**Response Example:**
```json
{
  "success": true,
  "data": {
    "fiscal_year": "2026",
    "metric": "utilization",
    "sectors": [
      {
        "sector_id": 3,
        "sector_name": "Infrastructure & Environmental Management",
        "sector_code": "IEM",
        "color_code": "#F59E0B",
        "total_projects": 18,
        "approved_projects": 15,
        "total_budget": 85000000.00,
        "total_disbursed": 72250000.00,
        "utilization_rate": 85.00
      },
      {
        "sector_id": 2,
        "sector_name": "Economic Services",
        "sector_code": "ES",
        "color_code": "#10B981",
        "total_projects": 28,
        "approved_projects": 22,
        "total_budget": 125000000.00,
        "total_disbursed": 98750000.28,
        "utilization_rate": 79.00
      }
    ],
    "insights": {
      "highest_budget": {
        "sector_id": 2,
        "sector_name": "Economic Services",
        "total_budget": 125000000.00
      },
      "highest_utilization": {
        "sector_id": 3,
        "sector_name": "Infrastructure & Environmental Management",
        "utilization_rate": 85.00
      },
      "most_projects": {
        "sector_id": 2,
        "sector_name": "Economic Services",
        "total_projects": 28
      }
    }
  },
  "message": "Sector comparison data retrieved successfully"
}
```

---

### 4. Get Monthly Progress for Specific Sector

**Endpoint:** `GET /api/lgu-sectors/{sector_id}/monthly-progress`

**Description:** Month-by-month breakdown of projects, budget, and disbursements for a specific sector.

**Query Parameters:**
- `year` (optional): Filter by year (default: current year)

**Request Example:**
```bash
GET /api/lgu-sectors/2/monthly-progress?year=2026
```

**Response Example:**
```json
{
  "success": true,
  "data": {
    "sector": {
      "id": 2,
      "name": "Economic Services",
      "code": "ES"
    },
    "year": 2026,
    "monthly_data": [
      {
        "month": 1,
        "month_name": "January",
        "projects_started": 3,
        "budget_allocated": 15000000.00,
        "disbursements": 2500000.00,
        "projects_approved": 2
      },
      {
        "month": 2,
        "month_name": "February",
        "projects_started": 5,
        "budget_allocated": 22000000.00,
        "disbursements": 8750000.00,
        "projects_approved": 4
      }
    ]
  },
  "message": "Monthly progress data retrieved successfully"
}
```

---

### 5. Get Performance Summary for Specific Sector

**Endpoint:** `GET /api/lgu-sectors/{sector_id}/performance-summary`

**Description:** Comprehensive performance metrics for a specific sector including approval workflow breakdown.

**Query Parameters:**
- `fiscal_year` (optional): Filter by fiscal year

**Request Example:**
```bash
GET /api/lgu-sectors/1/performance-summary?fiscal_year=2026
```

**Response Example:**
```json
{
  "success": true,
  "data": {
    "sector": {
      "id": 1,
      "name": "Social Services",
      "code": "SS",
      "description": "Health, Education, Social Welfare, and Community Development programs"
    },
    "fiscal_year": 2026,
    "performance": {
      "total_projects": 15,
      "approved_projects": 12,
      "ongoing_projects": 10,
      "completed_projects": 5,
      "completion_rate": 33.33,
      "approval_rate": 80.00
    },
    "budget": {
      "total_budget": 45000000.00,
      "total_disbursed": 32500000.00,
      "remaining_budget": 12500000.00,
      "utilization_rate": 72.22
    },
    "approval_breakdown": {
      "approved": {
        "count": 12,
        "percentage": 80.00
      },
      "pending_governor": {
        "count": 2,
        "percentage": 13.33
      },
      "draft": {
        "count": 1,
        "percentage": 6.67
      }
    }
  },
  "message": "Performance summary retrieved successfully"
}
```

---

### 6. Get Departments for Specific Sector

**Endpoint:** `GET /api/lgu-sectors/{sector_id}/departments`

**Description:** Retrieves all departments under a specific LGU sector with optional statistics.

**Query Parameters:**
- `include_stats` (optional): Include department statistics (default: true)

**Request Example:**
```bash
GET /api/lgu-sectors/2/departments?include_stats=true
```

**Response Example:**
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
        "description": "Manages field implementation of agricultural programs, farmer assistance, and extension services across CARAGA provinces.",
        "statistics": {
          "total_projects": 12,
          "active_projects": 10,
          "total_budget": 45000000.00,
          "total_disbursed": 35000000.00,
          "utilization_rate": 77.78,
          "beneficiaries": 2500
        }
      },
      {
        "id": 3,
        "name": "Agribusiness and Marketing Assistance Division",
        "description": "Facilitates market linkages, agribusiness development, and value chain enhancement for CARAGA agricultural products.",
        "statistics": {
          "total_projects": 8,
          "active_projects": 7,
          "total_budget": 28000000.00,
          "total_disbursed": 22400000.00,
          "utilization_rate": 80.00,
          "beneficiaries": 1800
        }
      }
    ],
    "total_departments": 9
  },
  "message": "Departments retrieved successfully"
}
```

---

### 7. Get Sector Overview with Department Breakdown

**Endpoint:** `GET /api/lgu-sectors/{sector_id}/departments-overview`

**Description:** Comprehensive sector overview with detailed department-level breakdown including budget allocation, utilization, and performance metrics.

**Query Parameters:**
- `fiscal_year` (optional): Filter by fiscal year (default: current year)

**Request Example:**
```bash
GET /api/lgu-sectors/3/departments-overview?fiscal_year=2026
```

**Response Example:**
```json
{
  "success": true,
  "data": {
    "sector": {
      "id": 3,
      "name": "Infrastructure & Environmental Management",
      "code": "IEM",
      "description": "Public Works, Water Systems, Flood Control, DRRM Programs, and Environmental Management",
      "icon": "hammer",
      "color_code": "#F59E0B"
    },
    "fiscal_year": "2026",
    "overview": {
      "total_projects": 18,
      "active_projects": 15,
      "total_budget": 85000000.00,
      "total_disbursed": 72250000.00,
      "remaining_budget": 12750000.00,
      "utilization_rate": 85.00,
      "total_beneficiaries": 45000,
      "average_compliance": 88.5
    },
    "departments": [
      {
        "id": 8,
        "name": "Agricultural Engineering Division",
        "description": "Plans and implements farm-to-market roads, irrigation systems, and post-harvest facilities infrastructure.",
        "projects_count": 10,
        "total_budget": 50000000.00,
        "total_disbursed": 42500000.00,
        "remaining_budget": 7500000.00,
        "utilization_rate": 85.00,
        "beneficiaries": 28000,
        "compliance_average": 90.0
      },
      {
        "id": 9,
        "name": "Regional Agricultural and Biosystems Engineering Division",
        "description": "Provides farm mechanization, agricultural machinery services, and engineering support for CARAGA farmers.",
        "projects_count": 8,
        "total_budget": 35000000.00,
        "total_disbursed": 29750000.00,
        "remaining_budget": 5250000.00,
        "utilization_rate": 85.00,
        "beneficiaries": 17000,
        "compliance_average": 87.0
      }
    ],
    "department_count": 2
  },
  "message": "Sector overview with departments retrieved successfully"
}
```

---

## Use Cases

### For Provincial Governors & PPDO
- Monitor budget utilization across all 4 sectors
- Compare sector performance to identify underperforming areas
- Track monthly progress and seasonal trends
- Make data-driven decisions on budget reallocation

### For DILG & COA Auditors
- Verify RA 7160 compliance in sector categorization
- Audit budget utilization rates per sector
- Review approval workflow distribution
- Assess transparency and governance metrics

### For NEDA & DBM
- Analyze sector-based development priorities
- Track infrastructure vs social services investment balance
- Monitor cost efficiency per sector
- Evaluate fiscal year performance trends

### For Public Transparency
- Citizens can view how government funds are allocated across sectors
- Civil society organizations can monitor sector priorities
- Media can report on sector-specific developments
- Researchers can analyze LGU governance patterns

---

## Integration with Dashboard

These endpoints complement the existing dashboard metrics:

```javascript
// Example: Fetch sector comparison for dashboard widget
fetch('/api/lgu-sectors/compare?fiscal_year=2026&metric=utilization')
  .then(response => response.json())
  .then(data => {
    renderSectorComparisonChart(data.sectors);
    displayTopPerformingSector(data.insights.highest_utilization);
  });

// Example: Fetch budget utilization for pie chart
fetch('/api/lgu-sectors/budget-utilization?fiscal_year=2026')
  .then(response => response.json())
  .then(data => {
    renderBudgetPieChart(data.sectors);
  });
```

---

## Sector Codes Reference

| Code | Sector Name | Primary Focus | Examples |
|------|-------------|---------------|----------|
| **SS** | Social Services | People & Community Welfare | Health centers, scholarship programs, DSWD projects, barangay health workers |
| **ES** | Economic Services | Economic Development | Farm-to-market roads, agri-tech programs, tourism promotion, livelihood projects |
| **IEM** | Infrastructure & Environmental Management | Physical Infrastructure | Road construction, water systems, flood control, DRRM programs |
| **GPS** | General Public Services | Governance & Administration | PPDO initiatives, legal services, budget planning, capacity building |

---

## Error Responses

### 404 - Sector Not Found
```json
{
  "message": "LGU Sector not found"
}
```

### 500 - Server Error
```json
{
  "message": "Failed to retrieve LGU sector reports",
  "error": "Detailed error message"
}
```

---

## Notes

- All amounts are in Philippine Pesos (PHP)
- Dates follow `YYYY-MM-DD` format
- Utilization rates are percentages with 2 decimal precision
- All endpoints support CORS for public access
- No authentication required (public transparency)
- Rate limiting: 60 requests per minute per IP

---

## Endpoint Summary

| Endpoint | Method | Description |
|----------|--------|-------------|
| `/api/lgu-sectors` | GET | List all sectors with statistics |
| `/api/lgu-sectors/budget-utilization` | GET | Budget utilization by sector |
| `/api/lgu-sectors/compare` | GET | Compare sectors by metrics |
| `/api/lgu-sectors/{id}/monthly-progress` | GET | Monthly progress for sector |
| `/api/lgu-sectors/{id}/performance-summary` | GET | Performance summary for sector |
| `/api/lgu-sectors/{id}/departments` | GET | Departments under a sector |
| `/api/lgu-sectors/{id}/departments-overview` | GET | Sector overview with department breakdown |

## Related Endpoints

- `/api/dashboard/budget-allocation-by-sector` - Dashboard sector allocation
- `/api/projects?sector_id={id}` - Projects filtered by sector
- `/api/departments/reports` - Department-level reports
- `/api/departments/{id}` - Specific department details

---

**Version:** 3.0 - Provincial LGU Governance Platform (RA 7160 Compliant)
**Last Updated:** 2026-01-30
**Status:** PRODUCTION READY
