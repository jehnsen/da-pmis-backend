# Additional Critical Metrics API - Risk Management & Proactive Monitoring

This document describes 5 additional critical metrics that complement the core compliance metrics, focusing on proactive management, risk identification, and outcome tracking.

## Overview

These endpoints provide advanced monitoring capabilities for:
- **Proactive Risk Management** - Identify problems before they become disasters
- **Outcome Tracking** - Show results, not just outputs
- **Audit Readiness** - COA compliance scorecard
- **Performance Trends** - Year-over-year improvement tracking
- **Early Warning System** - Catch problems 2-3 months early

All endpoints are **public** (no authentication required) for transparent governance.

---

## 6. Risk Dashboard

**Endpoint:** `GET /api/dashboard/risk-dashboard`

**Purpose:** Shows proactive risk management. COA loves seeing that you're identifying and tracking risks before they become problems.

### Query Parameters

| Parameter | Type | Description | Example |
|-----------|------|-------------|---------|
| `fiscal_year` | integer | Filter by fiscal year | `2025` |
| `department_id` | integer | Filter by department | `1` |

### Response Structure

```json
{
  "success": true,
  "data": {
    "high_risk_projects": [
      {
        "project_id": 1,
        "project_title": "Irrigation System Upgrade",
        "department": "Infrastructure",
        "risk_score": 65,
        "risk_level": "high",
        "risk_factors": [
          "Cost overrun risk",
          "4 overdue milestones",
          "Project status: delayed"
        ],
        "budget": 5000000.00,
        "status": "Delayed"
      }
    ],
    "medium_risk_projects": [...],
    "low_risk_projects": [...],
    "summary": {
      "total_projects": 127,
      "high_risk_count": 2,
      "medium_risk_count": 5,
      "low_risk_count": 120,
      "high_risk_percentage": 1.6,
      "medium_risk_percentage": 3.9,
      "low_risk_percentage": 94.5
    }
  }
}
```

### Risk Scoring Algorithm

**Risk Score Components:**
- Budget variance risk: 15-30 points (based on financial vs physical progress)
- Timeline risk: 20-35 points (based on overdue milestones)
- Project status: 15-25 points (if delayed or critical)
- Issues/risks reported: 10 points (if active issues exist)

**Risk Levels:**
- 🔴 **High Risk (50+ points):** Requires immediate intervention
- 🟡 **Medium Risk (25-49 points):** Requires close monitoring
- 🟢 **Low Risk (<25 points):** Normal monitoring

### Usage Example

```bash
curl "http://localhost:8000/api/dashboard/risk-dashboard?fiscal_year=2025"
```

---

## 7. Beneficiary Impact Metrics

**Endpoint:** `GET /api/dashboard/beneficiary-impact-metrics`

**Purpose:** Shows OUTCOME not just OUTPUT. Answers the critical question: "We spent ₱2.4B - did farmers actually benefit?" Justifies continued funding.

### Query Parameters

| Parameter | Type | Description | Example |
|-----------|------|-------------|---------|
| `fiscal_year` | integer | Filter by fiscal year | `2025` |

### Response Structure

```json
{
  "success": true,
  "data": {
    "income_impact": {
      "avg_income_before": 8500,
      "avg_income_after": 12300,
      "income_increase": 3800,
      "income_increase_percentage": 44.7,
      "status": "excellent"
    },
    "beneficiary_reach": {
      "total_beneficiaries": 52500,
      "direct_beneficiaries": 36750,
      "indirect_beneficiaries": 15750
    },
    "economic_impact": {
      "aggregate_impact_annual": 199500000.00,
      "project_cost": 85000000.00,
      "social_roi_percentage": 235,
      "status": "excellent"
    },
    "production_impact": {
      "current_year_production": 22500.00,
      "previous_year_production": 19800.00,
      "production_increase_percentage": 13.6,
      "status": "excellent"
    },
    "additional_impact": {
      "jobs_created": 1250,
      "hectares_covered": 2500,
      "community_infrastructure_value": 12750000.00
    },
    "fiscal_year": 2025
  }
}
```

### Impact Interpretation

| Metric | Excellent | Good | Needs Improvement |
|--------|-----------|------|-------------------|
| Income Increase | ≥ 30% | 15-29% | < 15% |
| Social ROI | ≥ 200% | 100-199% | < 100% |
| Production Growth | ≥ 10% | 5-9% | < 5% |

### Usage Example

```bash
curl "http://localhost:8000/api/dashboard/beneficiary-impact-metrics?fiscal_year=2025"
```

---

## 8. Compliance Scorecard

**Endpoint:** `GET /api/dashboard/compliance-scorecard`

**Purpose:** COA audit readiness check. Shows document completeness and prevents surprise audit findings. Identifies potential disallowances before they happen.

### Query Parameters

| Parameter | Type | Description | Example |
|-----------|------|-------------|---------|
| `fiscal_year` | integer | Filter by fiscal year | `2025` |
| `department_id` | integer | Filter by department | `1` |

### Response Structure

```json
{
  "success": true,
  "data": {
    "overall_compliance": 87,
    "compliance_areas": {
      "documentation": {
        "score": 92,
        "status": "green"
      },
      "budget_tracking": {
        "score": 89,
        "status": "yellow"
      },
      "beneficiary_docs": {
        "score": 78,
        "status": "yellow"
      },
      "procurement": {
        "score": 95,
        "status": "green"
      },
      "liquidation": {
        "score": 71,
        "status": "red"
      }
    },
    "at_risk_projects": [
      {
        "project_id": 15,
        "project_title": "Fish Port Construction",
        "compliance_score": 68.2,
        "risk_amount": 500000.00,
        "weakest_areas": [
          {
            "area": "Liquidation",
            "score": 45.0
          },
          {
            "area": "Beneficiary docs",
            "score": 60.0
          }
        ]
      }
    ],
    "at_risk_count": 8,
    "potential_disallowance": 2500000.00
  }
}
```

### Compliance Scoring

**Status Colors:**
- 🟢 **Green (90-100%):** Compliant, audit-ready
- 🟡 **Yellow (75-89%):** Needs minor improvements
- 🔴 **Red (<75%):** At risk of audit findings

**Compliance Areas:**
1. **Documentation:** Project has all required supporting documents
2. **Budget Tracking:** Disbursements are properly recorded
3. **Beneficiary Documentation:** Beneficiary lists and signed documents
4. **Procurement:** Proper approval and procurement processes followed
5. **Liquidation:** Disbursements properly liquidated with receipts

### Usage Example

```bash
curl "http://localhost:8000/api/dashboard/compliance-scorecard?fiscal_year=2025"
```

---

## 9. Year-over-Year Trends

**Endpoint:** `GET /api/dashboard/year-over-year-trends`

**Purpose:** Shows improvement over time. Proves programs are working and performance is improving. Essential for annual reports and budget justification.

### Query Parameters

| Parameter | Type | Description | Example |
|-----------|------|-------------|---------|
| `current_year` | integer | Current year (defaults to current) | `2025` |

### Response Structure

```json
{
  "success": true,
  "data": {
    "trends": [
      {
        "year": 2023,
        "projects_completed": 65,
        "budget_utilization": 68,
        "beneficiaries_reached": 65000,
        "success_rate": 72,
        "total_budget": 180000000.00,
        "total_disbursed": 122400000.00
      },
      {
        "year": 2024,
        "projects_completed": 78,
        "budget_utilization": 85,
        "beneficiaries_reached": 78000,
        "success_rate": 85,
        "total_budget": 220000000.00,
        "total_disbursed": 187000000.00
      },
      {
        "year": 2025,
        "projects_completed": 89,
        "budget_utilization": 89,
        "beneficiaries_reached": 89000,
        "success_rate": 91,
        "total_budget": 250000000.00,
        "total_disbursed": 222500000.00
      }
    ],
    "year_over_year_changes": [
      {
        "period": "2023-2024",
        "projects_change": 13,
        "projects_change_percentage": 20.0,
        "utilization_change": 17,
        "beneficiaries_change": 13000,
        "beneficiaries_change_percentage": 20.0
      },
      {
        "period": "2024-2025",
        "projects_change": 11,
        "projects_change_percentage": 14.1,
        "utilization_change": 4,
        "beneficiaries_change": 11000,
        "beneficiaries_change_percentage": 14.1
      }
    ],
    "summary": {
      "trend_direction": "improving",
      "three_year_growth": {
        "projects_completed": 24,
        "utilization_improvement": 21,
        "beneficiaries_growth": 24000
      }
    }
  }
}
```

### Trend Interpretation

**Trend Direction:**
- ↗ **Improving:** Positive growth in key metrics
- → **Stable:** Minimal change year-over-year
- ↘ **Declining:** Negative trends requiring attention

**Key Metrics to Watch:**
- **Projects Completed:** Growing completion rate = improving efficiency
- **Budget Utilization:** Moving toward 85-95% sweet spot
- **Beneficiaries Reached:** Expanding program reach

### Usage Example

```bash
curl "http://localhost:8000/api/dashboard/year-over-year-trends?current_year=2025"
```

---

## 10. Early Warning Alerts

**Endpoint:** `GET /api/dashboard/early-warning-alerts`

**Purpose:** Proactive vs reactive management. Catches problems 2-3 months early before they become disasters. Shows management sophistication to COA/DBM.

### Query Parameters

| Parameter | Type | Description | Example |
|-----------|------|-------------|---------|
| `fiscal_year` | integer | Filter by fiscal year | `2025` |

### Response Structure

```json
{
  "success": true,
  "data": {
    "critical_alerts": [
      {
        "type": "budget_overrun_risk",
        "severity": "critical",
        "project_id": 12,
        "project_title": "Irrigation System Phase 2",
        "message": "Budget overrun risk detected",
        "details": "Projected overrun: ₱1,200,000.00",
        "action_required": "Review contractor invoices and project scope",
        "action_due_days": 7
      },
      {
        "type": "critical_delay",
        "severity": "critical",
        "project_id": 8,
        "project_title": "FMR Road Construction",
        "message": "Critical delay: 30 days behind schedule",
        "details": "3 overdue milestone(s)",
        "action_required": "Immediate intervention required",
        "action_due_days": 3
      },
      {
        "type": "budget_underutilization",
        "severity": "critical",
        "project_id": 45,
        "project_title": "Fisheries Development",
        "message": "Budget underutilization: Only 45% at Month 9",
        "details": "Risk: ₱80,000,000.00 reversion",
        "action_required": "Accelerate implementation and disbursements",
        "action_due_days": 14
      }
    ],
    "warning_alerts": [
      {
        "type": "budget_variance",
        "severity": "warning",
        "project_id": 23,
        "project_title": "Corn Production Program",
        "message": "Budget variance detected (-16%)",
        "action_required": "Monitor closely",
        "action_due_days": 14
      }
    ],
    "info_alerts": [
      {
        "type": "low_utilization",
        "severity": "info",
        "project_id": 67,
        "project_title": "Training Program",
        "message": "Low budget utilization: 38%",
        "action_required": "Monitor utilization rate",
        "action_due_days": 30
      }
    ],
    "summary": {
      "total_alerts": 12,
      "critical_count": 3,
      "warning_count": 5,
      "info_count": 4,
      "requires_immediate_action": 2
    }
  }
}
```

### Alert Types & Triggers

**Critical Alerts (Immediate Action Required):**
1. **Budget Overrun Risk:** Financial progress >20% ahead of physical progress
2. **Critical Delay:** Milestone(s) overdue by 30+ days
3. **Budget Underutilization:** <50% utilization in Q3 or later (reversion risk)

**Warning Alerts (Monitor Closely):**
1. **Budget Variance:** Financial progress 15-20% ahead of physical
2. **Schedule Delay:** Milestone(s) overdue by 14-30 days

**Info Alerts (Awareness):**
1. **Low Utilization:** <40% utilization in Q2 or later

### Action Due Days

| Days | Urgency | Action Level |
|------|---------|--------------|
| ≤ 7 | 🔴 Urgent | Immediate intervention |
| 8-14 | 🟠 High | Priority action within 2 weeks |
| 15-30 | 🟡 Medium | Schedule review/meeting |
| 30+ | 🟢 Low | Routine monitoring |

### Usage Example

```bash
curl "http://localhost:8000/api/dashboard/early-warning-alerts?fiscal_year=2025"
```

---

## Integration with Core Metrics

These 5 additional metrics complement the core 5 compliance metrics:

### Core Compliance Metrics (See CRITICAL_METRICS_API.md)
1. Physical vs Financial Progress Variance - **COA compliance**
2. Budget Variance Heatmap - **DBM utilization tracking**
3. Milestone Completion Tracker - **NEDA timeline compliance**
4. Target vs Achievement KPI - **Performance management**
5. Cost Efficiency Metrics - **DBM cost-effectiveness**

### Additional Risk & Impact Metrics (This Document)
6. Risk Dashboard - **Proactive risk management**
7. Beneficiary Impact Metrics - **Outcome tracking**
8. Compliance Scorecard - **Audit readiness**
9. Year-over-Year Trends - **Performance trends**
10. Early Warning Alerts - **Proactive monitoring**

---

## Complete Dashboard Query Pattern

### Executive Dashboard (All Metrics at Once)

```bash
# Core Compliance Metrics
curl "http://localhost:8000/api/dashboard/physical-financial-variance?fiscal_year=2025&limit=10"
curl "http://localhost:8000/api/dashboard/budget-variance-heatmap?fiscal_year=2025"
curl "http://localhost:8000/api/dashboard/milestone-completion-tracker?fiscal_year=2025"
curl "http://localhost:8000/api/dashboard/target-achievement-kpi?fiscal_year=2025"
curl "http://localhost:8000/api/dashboard/cost-efficiency-metrics?fiscal_year=2025"

# Additional Risk & Impact Metrics
curl "http://localhost:8000/api/dashboard/risk-dashboard?fiscal_year=2025"
curl "http://localhost:8000/api/dashboard/beneficiary-impact-metrics?fiscal_year=2025"
curl "http://localhost:8000/api/dashboard/compliance-scorecard?fiscal_year=2025"
curl "http://localhost:8000/api/dashboard/year-over-year-trends?current_year=2025"
curl "http://localhost:8000/api/dashboard/early-warning-alerts?fiscal_year=2025"
```

### Department-Specific Analysis

```bash
# Risk analysis for specific department
curl "http://localhost:8000/api/dashboard/risk-dashboard?department_id=1&fiscal_year=2025"
curl "http://localhost:8000/api/dashboard/compliance-scorecard?department_id=1&fiscal_year=2025"
curl "http://localhost:8000/api/dashboard/physical-financial-variance?department_id=1&fiscal_year=2025"
```

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

## Performance Considerations

- Risk dashboard analyzes all projects - use department filters for faster queries
- Beneficiary impact metrics aggregate large datasets - consider caching results
- Compliance scorecard performs multiple document checks - optimize for production
- Year-over-year trends query 3 years of data - results are cacheable
- Early warning alerts scan all active projects - use fiscal year filters

---

## Compliance & Governance Benefits

✅ **Proactive Risk Management** - Identify and mitigate risks before they escalate
✅ **Outcome Measurement** - Demonstrate real impact on beneficiaries
✅ **Audit Readiness** - Continuous compliance monitoring
✅ **Performance Improvement** - Track year-over-year growth
✅ **Early Problem Detection** - Catch issues 2-3 months before deadline
✅ **Transparent Governance** - Public endpoints for stakeholder oversight
✅ **Data-Driven Decisions** - Comprehensive metrics for strategic planning

---

## Support & Documentation

- **Core Compliance Metrics:** `docs/CRITICAL_METRICS_API.md`
- **Main API Documentation:** `docs/POSTMAN_GUIDE.md`
- **Project Overview:** `CLAUDE.md`

For technical support or questions about these metrics, contact the DA-PMIS development team.

---

**Version:** 1.0
**Last Updated:** 2026-01-29
**Status:** ✅ Production Ready
**Total Dashboard Endpoints:** 16 (6 basic + 10 critical metrics)
