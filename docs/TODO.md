### Dashboard Analytics (13 endpoints):
GET /api/dashboard/overview?sector_id=1
GET /api/dashboard/budget-allocation
GET /api/dashboard/budget-allocation-by-sector
GET /api/dashboard/project-status-distribution?sector_id=1
GET /api/dashboard/monthly-progress?sector_id=1
GET /api/dashboard/physical-financial-variance?sector_id=1
GET /api/dashboard/budget-variance-heatmap?sector_id=1
GET /api/dashboard/milestone-completion-tracker?sector_id=1
GET /api/dashboard/target-achievement-kpi?sector_id=1
GET /api/dashboard/cost-efficiency-metrics?sector_id=1
GET /api/dashboard/risk-dashboard?sector_id=1
GET /api/dashboard/beneficiary-impact-metrics?sector_id=1
GET /api/dashboard/compliance-scorecard?sector_id=1
GET /api/dashboard/early-warning-alerts?sector_id=1

### Todos
1. fix logout logic, it still logged in automatically after logging out
2. ✅ Implemented Sector-Department Hierarchy (2026-01-31)
   - Added sector_id to departments table
   - Created relationships: LguSector ↔ Department
   - Added 2 new endpoints:
     - GET /api/lgu-sectors/{id}/departments
     - GET /api/lgu-sectors/{id}/departments-overview
   - Assigned all 15 departments to appropriate sectors (GPS, ES, IEM)
   - Documentation: docs/SECTOR_DEPARTMENT_HIERARCHY.md
3. ✅ Updated ProjectSeeder with 50 Realistic Projects (2026-01-31)
   - 32 Economic Services (ES) projects - ₱992M total budget
   - 18 Infrastructure & Environmental Management (IEM) projects - ₱1.245B total budget
   - Covers rice, high-value crops, livestock, fisheries, agribusiness, irrigation, roads, facilities
   - Realistic budgets, timelines, locations, and technical details
   - Documentation: docs/PROJECT_SEEDER_UPDATE.md 

###
{
    "success": true,
    "data": {
        "total_projects": 18,
        "total_investment": 929000000,
        "success_rate": 0,
        "on_track_projects": 15,
        "active_projects": 18,
        "beneficiaries": 18000,
        "municipalities_covered": 0,
        "funds_spent_percentage": 0
    },
    "message": "Dashboard overview retrieved successfully"
}