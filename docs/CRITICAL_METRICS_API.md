# Critical Government Compliance Metrics API

This document describes the 5 critically important dashboard metrics required by COA (Commission on Audit), DBM (Department of Budget and Management), and NEDA (National Economic and Development Authority) for government project monitoring and compliance.

## Overview

These endpoints provide essential performance indicators that demonstrate:
- **Financial Accountability** (COA requirement)
- **Budget Utilization Efficiency** (DBM requirement)
- **Timeline Compliance** (NEDA requirement)
- **Cost Effectiveness** (DBM requirement)
- **Performance Management** (All agencies)

All endpoints are **public** (no authentication required) to support transparent governance.

---

## 1. Physical vs Financial Progress Variance

**Endpoint:** `GET /api/dashboard/physical-financial-variance`

**Purpose:** COA's favorite metric showing if money spent matches actual work completed. Critical for detecting overspending and project mismanagement.

### Query Parameters

| Parameter | Type | Description | Example |
|-----------|------|-------------|---------|
| `fiscal_year` | integer | Filter by fiscal year | `2025` |
| `department_id` | integer | Filter by department | `1` |
| `report_period` | string | Filter by period (Q1, Q2, etc.) | `Q1 2025` |
| `show_all` | boolean | Show all reports vs latest only | `true` |
| `limit` | integer | Limit results (default: 50) | `100` |

### Response Structure

```json
{
  "success": true,
  "data": {
    "projects": [
      {
        "project_id": 1,
        "project_title": "Rice Production Enhancement Program",
        "department": "Crop Production",
        "financial_progress": 80,
        "physical_progress": 65,
        "variance": -15,
        "status": "overspending",
        "alert": "Review contractor invoices",
        "budget": 5000000.00,
        "projected_overrun": 750000.00,
        "report_period": "Q1 2025",
        "reporting_date": "2025-01-15"
      }
    ],
    "summary": {
      "total_projects": 45,
      "average_variance": -5.2,
      "overspending_count": 12,
      "on_track_count": 28,
      "underspending_count": 5,
      "overspending_percentage": 26.7
    }
  }
}
```

### Variance Interpretation

| Variance | Status | Meaning | Action Required |
|----------|--------|---------|-----------------|
| < -10% | 🔴 Overspending | Spending ahead of work | Review invoices, investigate |
| -10% to 10% | 🟢 On Track | Balanced spending | Continue monitoring |
| > 10% | 🟡 Underspending | Work ahead of spending | Accelerate disbursements |

### Usage Example

```bash
curl "http://localhost:8000/api/dashboard/physical-financial-variance?fiscal_year=2025&limit=20"
```

---

## 2. Budget Variance Heatmap

**Endpoint:** `GET /api/dashboard/budget-variance-heatmap`

**Purpose:** Shows departmental over/under spending. DBM requires 85-95% utilization as the "sweet spot" for efficient budget use.

### Query Parameters

| Parameter | Type | Description | Example |
|-----------|------|-------------|---------|
| `fiscal_year` | integer | Filter by fiscal year | `2025` |

### Response Structure

```json
{
  "success": true,
  "data": {
    "departments": [
      {
        "department": "Crop Production",
        "total_budget": 2000000000.00,
        "disbursed": 1840000000.00,
        "remaining": 160000000.00,
        "utilization_rate": 92.0,
        "status": "good",
        "color": "green",
        "project_count": 45
      },
      {
        "department": "Fisheries",
        "total_budget": 1200000000.00,
        "disbursed": 1248000000.00,
        "remaining": -48000000.00,
        "utilization_rate": 104.0,
        "status": "critical_overspending",
        "color": "red",
        "project_count": 32
      }
    ],
    "summary": {
      "total_budget": 6000000000.00,
      "total_disbursed": 5100000000.00,
      "overall_utilization": 85.0,
      "average_utilization": 88.5,
      "departments_on_track": 8,
      "departments_at_risk": 2
    }
  }
}
```

### Status Color Guide

| Utilization | Status | Color | Meaning |
|-------------|--------|-------|---------|
| 85-100% | 🟢 Good | green | Optimal spending |
| 70-84% | 🟡 Warning | yellow | Under-utilizing |
| > 100% | 🔴 Critical | red | Over budget |
| < 70% | 🔴 Critical | red | Significant under-utilization |

### Usage Example

```bash
curl "http://localhost:8000/api/dashboard/budget-variance-heatmap?fiscal_year=2025"
```

---

## 3. Milestone Completion Tracker

**Endpoint:** `GET /api/dashboard/milestone-completion-tracker`

**Purpose:** Shows if projects are meeting deadlines. NEDA requires timeline compliance reporting.

### Query Parameters

| Parameter | Type | Description | Example |
|-----------|------|-------------|---------|
| `fiscal_year` | integer | Filter by fiscal year | `2025` |
| `department_id` | integer | Filter by department | `1` |
| `quarter` | integer | Filter by quarter (1-4) | `1` |

### Response Structure

```json
{
  "success": true,
  "data": {
    "summary": {
      "total_milestones": 156,
      "on_time": 89,
      "on_time_percentage": 57.0,
      "delayed_1_to_7_days": 45,
      "delayed_1_to_7_percentage": 29.0,
      "delayed_8_plus_days": 22,
      "delayed_8_plus_percentage": 14.0,
      "average_delay_days": 5.2
    },
    "worst_performers": [
      {
        "milestone": "Irrigation System Installation",
        "project": "Farm Infrastructure Upgrade",
        "delay_days": 18
      },
      {
        "milestone": "Equipment Procurement",
        "project": "Modernization Program",
        "delay_days": 15
      }
    ]
  }
}
```

### Performance Interpretation

| Metric | Target | Acceptable | Needs Attention |
|--------|--------|------------|-----------------|
| On-time % | > 70% | 60-70% | < 60% |
| Avg Delay | < 5 days | 5-10 days | > 10 days |
| Delayed 8+ days | < 10% | 10-15% | > 15% |

### Usage Example

```bash
curl "http://localhost:8000/api/dashboard/milestone-completion-tracker?fiscal_year=2025&quarter=1"
```

---

## 4. Target vs Achievement KPI Table

**Endpoint:** `GET /api/dashboard/target-achievement-kpi`

**Purpose:** Core of Performance Management. Shows if departments are hitting their Key Performance Indicators.

### Query Parameters

| Parameter | Type | Description | Example |
|-----------|------|-------------|---------|
| `fiscal_year` | integer | Filter by fiscal year (defaults to current) | `2025` |
| `department_id` | integer | Filter by department | `1` |

### Response Structure

```json
{
  "success": true,
  "data": {
    "kpis": [
      {
        "department": "Crop Production",
        "indicator": "Rice Production (MT)",
        "target": 22000.00,
        "actual": 20500.00,
        "unit": "MT",
        "achievement_rate": 93.2,
        "status": "yellow",
        "performance": "Good",
        "fiscal_year": 2025
      },
      {
        "department": "Outreach",
        "indicator": "Beneficiaries Reached",
        "target": 100000.00,
        "actual": 125000.00,
        "unit": "persons",
        "achievement_rate": 125.0,
        "status": "green",
        "performance": "Excellent",
        "fiscal_year": 2025
      }
    ],
    "summary": {
      "total_indicators": 24,
      "excellent_count": 18,
      "good_count": 4,
      "needs_improvement_count": 2,
      "overall_achievement_rate": 98.5
    }
  }
}
```

### Performance Rating

| Achievement Rate | Status | Performance | Color |
|-----------------|--------|-------------|-------|
| ≥ 100% | 🟢 green | Excellent | Target exceeded |
| 90-99% | 🟡 yellow | Good | Close to target |
| < 90% | 🔴 red | Needs Improvement | Below target |

### Usage Example

```bash
curl "http://localhost:8000/api/dashboard/target-achievement-kpi?fiscal_year=2025"
```

---

## 5. Cost Efficiency Metrics

**Endpoint:** `GET /api/dashboard/cost-efficiency-metrics`

**Purpose:** Shows value for money. DBM uses this for budget approval and benchmarking against other regions.

### Query Parameters

| Parameter | Type | Description | Example |
|-----------|------|-------------|---------|
| `fiscal_year` | integer | Filter by fiscal year (defaults to current) | `2025` |

### Response Structure

```json
{
  "success": true,
  "data": {
    "cost_per_beneficiary": 2100.00,
    "cost_per_beneficiary_target": 2500.00,
    "cost_per_beneficiary_status": "good",
    "cost_per_beneficiary_vs_regional": 2800.00,
    "efficiency_vs_regional": 25.0,

    "cost_per_hectare": 4500.00,
    "cost_per_hectare_target": 5000.00,
    "cost_per_hectare_status": "good",
    "cost_per_hectare_vs_regional": 5625.00,

    "cost_per_mt_production": 3200.00,
    "cost_per_mt_target": 3500.00,
    "cost_per_mt_status": "good",

    "admin_cost_percentage": 8.0,
    "admin_cost_target": 10.0,
    "admin_cost_status": "good",

    "total_beneficiaries": 125000,
    "total_hectares": 50000,
    "total_production_mt": 82000.00,
    "total_budget": 300000000.00,
    "total_disbursed": 262500000.00,
    "fiscal_year": 2025
  }
}
```

### Efficiency Benchmarks

| Metric | Target | Your Region | Regional Avg | Status |
|--------|--------|-------------|--------------|--------|
| Cost/Beneficiary | ≤ ₱2,500 | ₱2,100 | ₱2,800 | 🟢 25% more efficient |
| Cost/Hectare | ≤ ₱5,000 | ₱4,500 | ₱5,625 | 🟢 20% more efficient |
| Cost/MT Production | ≤ ₱3,500 | ₱3,200 | N/A | 🟢 On target |
| Admin Cost % | ≤ 10% | 8% | 10% | 🟢 2% below target |

### Usage Example

```bash
curl "http://localhost:8000/api/dashboard/cost-efficiency-metrics?fiscal_year=2025"
```

---

## Common Query Patterns

### Get All Metrics for Current Year (Dashboard Overview)

```bash
# Physical-Financial Variance
curl "http://localhost:8000/api/dashboard/physical-financial-variance?limit=10"

# Budget Heatmap
curl "http://localhost:8000/api/dashboard/budget-variance-heatmap"

# Milestone Tracker
curl "http://localhost:8000/api/dashboard/milestone-completion-tracker"

# KPI Achievement
curl "http://localhost:8000/api/dashboard/target-achievement-kpi"

# Cost Efficiency
curl "http://localhost:8000/api/dashboard/cost-efficiency-metrics"
```

### Department-Specific Analysis

```bash
# All metrics for Crop Production department (ID: 1)
curl "http://localhost:8000/api/dashboard/physical-financial-variance?department_id=1"
curl "http://localhost:8000/api/dashboard/milestone-completion-tracker?department_id=1"
curl "http://localhost:8000/api/dashboard/target-achievement-kpi?department_id=1"
```

### Quarterly Reporting

```bash
# Q1 2025 Performance Review
curl "http://localhost:8000/api/dashboard/physical-financial-variance?fiscal_year=2025&report_period=Q1%202025"
curl "http://localhost:8000/api/dashboard/milestone-completion-tracker?fiscal_year=2025&quarter=1"
```

---

## Integration with Existing Dashboard

These 5 new endpoints complement the existing dashboard endpoints:

### Existing Endpoints (Still Available)
1. `/api/dashboard/overview` - High-level project statistics
2. `/api/dashboard/budget-allocation` - Budget by region
3. `/api/dashboard/project-status-distribution` - Project status counts
4. `/api/dashboard/national-performance` - Agricultural production data
5. `/api/dashboard/recent-updates` - Recent project updates
6. `/api/dashboard/monthly-progress` - Monthly progress by department

### New Critical Metrics (Just Added)
7. `/api/dashboard/physical-financial-variance` - **COA compliance**
8. `/api/dashboard/budget-variance-heatmap` - **DBM utilization tracking**
9. `/api/dashboard/milestone-completion-tracker` - **NEDA timeline compliance**
10. `/api/dashboard/target-achievement-kpi` - **Performance management**
11. `/api/dashboard/cost-efficiency-metrics` - **DBM cost-effectiveness**

---

## Response Format

All endpoints follow the standard API response format:

```json
{
  "success": true,
  "data": {
    // Metric-specific data
  },
  "message": "Data retrieved successfully"
}
```

Error responses:

```json
{
  "success": false,
  "message": "Failed to retrieve data",
  "error": "Error details"
}
```

---

## Data Sources

| Metric | Primary Data Source | Secondary Data |
|--------|---------------------|----------------|
| Physical-Financial Variance | `progress_reports` table | `projects`, `departments` |
| Budget Heatmap | `projects`, `project_disbursements` | `departments` |
| Milestone Tracker | `project_milestones` | `projects` |
| KPI Achievement | `department_kpis` | `departments` |
| Cost Efficiency | `projects`, `project_disbursements` | `crop_productions` |

---

## Performance Considerations

- All queries are optimized with appropriate indexes
- Results are limited by default (configurable via `limit` parameter)
- Use fiscal year filters to reduce data processing
- Department filters significantly improve query performance
- Consider caching results for high-traffic dashboards

---

## Compliance & Audit Trail

All these metrics support:

✅ **COA Audit Requirements** - Financial accountability and variance tracking
✅ **DBM Budget Efficiency** - Utilization rates and cost-effectiveness
✅ **NEDA Timeline Compliance** - Milestone tracking and project delays
✅ **Transparent Governance** - Public endpoints for citizen oversight
✅ **Performance Management** - Target vs achievement tracking

---

## Support & Documentation

- **Main API Documentation:** `docs/POSTMAN_GUIDE.md`
- **Project Overview:** `CLAUDE.md`
- **Database Schema:** `docs/MIGRATION_SEQUENCE.md`

For technical support or questions about these metrics, contact the DA-PMIS development team.

---

**Version:** 1.0
**Last Updated:** 2026-01-29
**Status:** ✅ Production Ready
