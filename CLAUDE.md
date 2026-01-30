## Project Overview

**PLGU-GIP** - The Provincial LGU Governance Intelligence Platform for the CARAGA Region (Region XIII), Philippines focused on infrastructure, health and agricultural projects, the system now implements **RA 7160 (Local Government Code of 1991)** compliance with multi-sector provincial governance covering Social Services, Economic Services, Infrastructure & Environmental Management, and General Public Services. It manages projects, crop/livestock statistics, progress reports, dashboard analytics, project approvals, financial disbursements, and public engagement.

## Tech Stack

- **Framework**: Laravel 11 with PHP 8.2+
- **Database**: MySQL
- **Authentication**: Laravel Sanctum (Token-based API Authentication)
- **Architecture**: Service-Repository-Interface Pattern

## Project Structure

```
app/
├── Classes/           # Utility classes (ApiResponseClass)
├── Enums/             # Enumerations (IncidentSeverity, IncidentStatus)
├── Http/
│   ├── Controllers/   # 22 API controllers
│   ├── Middleware/    # ForceJsonResponse middleware
│   ├── Requests/      # Form request validation (Store/Update pairs)
│   └── Resources/     # API resource transformers
├── Interfaces/        # 16 Repository interfaces
├── Models/            # 30 Eloquent models (includes LguSector)
├── Providers/         # Service providers (interface bindings)
├── Repositories/      # 16 Repository implementations
├── Services/          # 16 Business logic services
└── Traits/            # Auditable trait for change tracking
```

## Architecture Pattern

Request → Controller → Service → Repository → Model → Database

Each module follows this pattern:
1. **Interface** defines the contract (`app/Interfaces/`)
2. **Repository** implements data access (`app/Repositories/`)
3. **Service** contains business logic (`app/Services/`)
4. **Provider** binds interface to implementation (`app/Providers/`)
5. **Controller** orchestrates the flow (`app/Http/Controllers/`)

## Key Controllers

| Controller | Description |
|------------|-------------|
| `AuthController` | User authentication (login, register, logout) |
| `DashboardController` | Dashboard analytics endpoints |
| `ProjectController` | Project CRUD operations |
| `ProjectApprovalController` | Project approval workflow |
| `ProjectDisbursementController` | Financial disbursement tracking |
| `ProgressReportController` | Project progress reporting |
| `DepartmentController` | Department CRUD operations |
| `DepartmentReportController` | Department reports & KPI tracking |
| `LocationController` | Geographic location hierarchy |
| `CropProductionController` | Agricultural crop data |
| `LivestockStatisticController` | Livestock data management |
| `NewsUpdateController` | News/announcements management |
| `DocumentController` | Document management with download tracking |
| `UserManagementController` | Admin user management |
| `NotificationController` | User notification system |
| `ContactInquiryController` | Public contact form management |
| `NewsletterSubscriptionController` | Newsletter subscription management |
| `ProjectTeamMemberController` | Project team assignments |
| `ProjectMilestoneController` | Project milestone tracking |
| `ProjectImageController` | Project image uploads |
| `ProgressReportImageController` | Progress report image uploads |

## Key Models

- **Project** - Main entity with budget, timeline, location, team members, milestones, sector, municipality, barangay
- **ProjectApproval** - RA 7160 approval workflow (Barangay → Municipal → Provincial → Governor)
- **ProjectDisbursement** - Financial disbursements per project
- **LguSector** - Four LGU sectors (SS, ES, IEM, GPS) with budget tracking
- **Department** - Organizational units with KPIs
- **ProgressReport** - Periodic reports with metrics and images
- **CropProduction** / **LivestockStatistic** - Agricultural data
- **Region** / **Province** / **Municipality** - Location hierarchy
- **NewsUpdate** / **Document** - Content management
- **ProjectTeamMember** - Team assignments with roles
- **ProjectMilestone** - Timeline deliverables
- **User** - Authentication with roles and permissions
- **AuditLog** - Change tracking via Auditable trait

## Common Commands

```bash
# Development server
php artisan serve

# Database
php artisan migrate                    # Run migrations
php artisan migrate:fresh --seed       # Fresh DB with seeders
php artisan db:seed                    # Run all seeders
php artisan db:seed --class=ProjectSeeder  # Specific seeder

# Cache management
php artisan optimize:clear             # Clear all caches
php artisan config:cache               # Cache configuration
php artisan route:cache                # Cache routes

# Routes
php artisan route:list                 # List all routes
php artisan route:list --path=api      # API routes only

# Testing
./vendor/bin/phpunit                   # Run all tests
./vendor/bin/phpunit tests/Feature/    # Feature tests only
```

## API Routes (111+ Endpoints)

### Public Endpoints (no auth required)

**Authentication:**
- `POST /api/register`, `POST /api/login`

**Dashboard Analytics:**
- `GET /api/dashboard/overview` - Overview statistics
- `GET /api/dashboard/budget-allocation` - Budget by region
- `GET /api/dashboard/project-status-distribution` - Project status counts
- `GET /api/dashboard/national-performance` - Production metrics
- `GET /api/dashboard/recent-updates` - Recent project updates
- `GET /api/dashboard/monthly-progress` - Monthly progress by department

**Critical Government Compliance Metrics (COA/DBM/NEDA):**
- `GET /api/dashboard/physical-financial-variance` - Physical vs financial progress variance (COA)
- `GET /api/dashboard/budget-variance-heatmap` - Budget utilization by department (DBM)
- `GET /api/dashboard/milestone-completion-tracker` - Timeline compliance tracking (NEDA)
- `GET /api/dashboard/target-achievement-kpi` - Target vs achievement KPIs (Performance Management)
- `GET /api/dashboard/cost-efficiency-metrics` - Cost per beneficiary/hectare/MT (DBM)

**Additional Risk Management & Impact Metrics:**
- `GET /api/dashboard/risk-dashboard` - Project risk assessment (COA proactive management)
- `GET /api/dashboard/beneficiary-impact-metrics` - Outcome vs output tracking (Justifies funding)
- `GET /api/dashboard/compliance-scorecard` - COA audit readiness check
- `GET /api/dashboard/year-over-year-trends` - 3-year performance trends
- `GET /api/dashboard/early-warning-alerts` - Proactive problem detection (2-3 months early)

**Location Management:**
- `GET /api/locations/regions`, `/provinces`, `/municipalities`
- `GET /api/locations/hierarchy` - Full location tree
- `GET /api/locations/search` - Search locations
- `GET /api/locations/statistics` - Location counts

**Content:**
- `GET /api/projects`, `GET /api/projects/{id}`
- `GET /api/news-updates`, `GET /api/documents`
- `GET /api/documents/featured` - Featured documents
- `POST /api/documents/{id}/download` - Download with tracking
- `GET /api/crop-productions`, `GET /api/livestock-statistics`

**User Engagement:**
- `POST /api/contact-inquiries`, `POST /api/newsletter-subscriptions`

### Protected Endpoints (require `auth:api` middleware)

**Project Management:**
- Full CRUD for projects, departments, progress-reports
- `POST /api/projects/{id}/submit-for-approval`
- `POST /api/projects/{id}/approve`, `/reject`, `/request-changes`
- `GET /api/projects/{id}/approval-history`
- `GET /api/projects/pending-approval`, `/approval-statistics`

**Project Disbursements:**
- `GET /api/projects/{id}/disbursements` - List disbursements
- `POST /api/projects/{id}/disbursements` - Create disbursement
- `POST /api/projects/{id}/disbursements/{id}/approve`, `/cancel`
- `GET /api/projects/{id}/financial-summary`
- `GET /api/projects/{id}/monthly-spending`

**Department Reports:**
- `GET /api/departments/reports` - All department reports
- `GET /api/departments/budget-utilization`
- `GET /api/departments/{id}/monthly-progress`
- `GET /api/departments/{id}/kpi-summary`

**User Management:**
- `GET /api/users`, `POST /api/users`
- `GET /api/users/statistics`
- `PATCH /api/users/{id}/toggle-status`

**Notifications:**
- `GET /api/notifications`, `/unread-count`
- `POST /api/notifications/{id}/mark-read`, `/mark-all-read`
- `DELETE /api/notifications/clear-all`

**Progress Reports:**
- `GET /api/progress-reports/with-issues`
- `GET /api/progress-reports/statistics`
- `GET /api/projects/{id}/progress-timeline`

**Project Team Members:**
- `GET /api/projects/{id}/team-members` - List team members
- `POST /api/projects/{id}/team-members` - Add team member
- `GET /api/projects/{id}/team-members/{memberId}` - View team member
- `PUT /api/projects/{id}/team-members/{memberId}` - Update role
- `DELETE /api/projects/{id}/team-members/{memberId}` - Remove member

**Project Milestones:**
- `GET /api/projects/{id}/milestones` - List milestones with completion rate
- `POST /api/projects/{id}/milestones` - Create milestone
- `GET /api/projects/{id}/milestones/{milestoneId}` - View milestone
- `PUT /api/projects/{id}/milestones/{milestoneId}` - Update milestone
- `DELETE /api/projects/{id}/milestones/{milestoneId}` - Delete milestone
- `POST /api/projects/{id}/milestones/{milestoneId}/complete` - Mark as completed

## Database

- **Connection**: MySQL on localhost:3306
- **Database name**: `iterable_db`
- **Features**: Foreign keys with cascades, soft deletes, timestamps, indexes

## Key Patterns

### ApiResponseClass
Standardized JSON responses:
```php
ApiResponseClass::sendResponse($data, $message, 200);
ApiResponseClass::rollback($exception, "Error message");
```

### Auditable Trait
Automatically logs created/updated/deleted events with old/new values, user ID, IP address.

### Resource Classes
Transform models with conditional visibility (e.g., hide budget from public users).

### Model Scopes
```php
Project::public()      // Only public projects
NewsUpdate::featured() // Only featured news
```

## Testing

- PHPUnit configured with Unit and Feature test suites
- Test environment uses faster bcrypt rounds (4)
- Factories available for User and Resident models

## Environment

Key `.env` variables:
- `DB_*` - Database connection
- `APP_KEY` - Application key (required)
- `APP_ENV` - Environment (local/production)

## Code Style

- Uses Laravel Pint for code formatting
- Form Requests for validation (never validate in controllers)
- Resources for API response transformation
- Repositories for data access (never query in controllers)

---

## Key Features

### 1. Dashboard Analytics
- Overview statistics (total projects, investment, success rate)
- Budget allocation by region with utilization rates
- Project status distribution
- National performance metrics (rice, corn, fish, livestock)
- Recent project updates
- Monthly progress tracking

### 2. Project Approval Workflow (RA 7160 Compliant)
- **4-Level Approval Chain**: Barangay → Municipal (MPDO) → Provincial (PPDO) → Governor
- Submit projects for approval at Barangay Development Council level
- Each level can approve, reject, or request changes
- Complete approval history tracking
- Statistics on approval workflow
- Compliant with Local Government Code of 1991

### 3. Project Disbursements
- Track financial disbursements per project
- Categorize spending (equipment, personnel, supplies, etc.)
- Approve/cancel disbursements
- Financial summary and monthly spending reports

### 4. Location Management
- Hierarchical location data (Region → Province → Municipality)
- Search functionality
- Statistics per location

### 5. User Notifications
- In-app notification system
- Mark as read/unread
- Bulk operations

### 6. Role-Based Access Control (RBAC)
- **Public View:** Limited data (project name, description, status)
- **Internal View:** Full data including budget, team members, timelines
- Implementation in `ProjectResource::shouldShowInternal()`

### 7. Audit Logging
- Automatic logging via Auditable trait
- Tracks created/updated/deleted events
- Records old/new values, user ID, IP address

### 8. LGU Multi-Sector Governance (RA 7160)
- **SS (Social Services)**: Health, Education, Social Welfare
- **ES (Economic Services)**: Agriculture, Tourism, Trade & Industry
- **IEM (Infrastructure & Environmental Management)**: Public Works, Utilities, DRRM
- **GPS (General Public Services)**: Planning, Legal, Budget, Administration
- Sector-based budget tracking and utilization rates
- Geographic routing through Municipality → Province → Barangay

---

## Database Schema Overview

**Projects Module:**
- `projects` (with sector_id, municipality_id, province_id, barangay fields)
- `project_types`, `project_statuses`
- `project_team_members`, `project_milestones`
- `project_approvals` (RA 7160 levels: barangay, municipal, provincial, governor)
- `project_disbursements`

**Location Module:**
- `regions`, `provinces`, `municipalities`

**LGU Governance Module:**
- `lgu_sectors` (4 sectors: SS, ES, IEM, GPS)

**KPIs & Reporting:**
- `department_kpis`, `progress_reports`, `report_metrics`

**Agricultural Data:**
- `crop_productions`, `livestock_statistics`, `funding_distributions`

**Content Management:**
- `news_updates`, `documents`, `document_categories`

**User Engagement:**
- `contact_inquiries`, `newsletter_subscriptions`

**Security & Compliance:**
- `users`, `roles`, `permissions`, `audit_logs`

---

## Quick Setup

```bash
# 1. Environment
cp .env.example .env
# Configure DB settings

# 2. Install
composer install

# 3. Database
php artisan key:generate
php artisan migrate:fresh --seed

# 4. Serve
php artisan serve
```

## Default Login

**Username:** `admin`
**Password:** `Password123!`

> **Important:** Change all default passwords in production!

---

## Documentation

- `docs/SETUP_GUIDE.md` - Quick setup & API testing
- `docs/SEEDER_DOCUMENTATION.md` - Detailed seeder info
- `docs/MIGRATION_SEQUENCE.md` - Migration dependencies
- `docs/PROJECT_SUMMARY.md` - Complete project summary
- `docs/TEAM_AND_MILESTONE_API.md` - Team assignment & milestone tracking API
- `docs/CRITICAL_METRICS_API.md` - Core COA/DBM/NEDA compliance metrics (5 endpoints)
- `docs/ADDITIONAL_CRITICAL_METRICS_API.md` - Risk management & impact metrics (5 endpoints)
- `docs/RA_7160_REFACTORING_GUIDE.md` - Complete RA 7160 implementation guide
- `docs/MIGRATION_IMPLEMENTATION_SUMMARY.md` - LGU structure migration details
- `FRESH_INSTALL_GUIDE.md` - Step-by-step setup for new installations
- `DEPLOY_LGU_PLATFORM.md` - Production deployment guide
- `REFACTORING_COMPLETE.md` - RA 7160 refactoring summary

---

## Implementation Summary

- **22 Controllers** (added ProjectTeamMember, ProjectMilestone, ProjectImage, ProgressReportImage)
- **16 Repository Interfaces**
- **16 Repository Implementations**
- **16 Service Classes**
- **30 Eloquent Models** (added LguSector + 3 others)
- **111+ API endpoints** (includes 10 critical government compliance & risk management metrics)
- **RA 7160 Compliant** - Full Local Government Code implementation
- **4 LGU Sectors** - Multi-sector governance (SS, ES, IEM, GPS)
- **4-Level Approval Chain** - Barangay → Municipal → Provincial → Governor
- Service-Repository-Interface pattern
- RBAC implemented
- Audit logging functional
- Dashboard analytics with government compliance metrics
- Financial disbursement tracking
- Notification system
- Location hierarchy management with barangay support
- Project team assignment
- Milestone tracking with completion rate
- COA/DBM/NEDA compliance reporting
- Proactive risk management dashboard
- Beneficiary impact tracking
- Early warning alert system

**Status:** READY FOR DEPLOYMENT

*Version:* 3.0 - Provincial LGU Governance Platform (RA 7160 Compliant)
*Updated:* 2026-01-30
*Region:* CARAGA (Region XIII), Philippines
*Original Focus:* Department of Agriculture
*Current Scope:* Multi-sector Provincial LGU Governance (Social Services, Economic Services, Infrastructure & Environmental Management, General Public Services)
