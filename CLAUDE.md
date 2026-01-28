## Project Overview

**DA-PMIS** (Department of Agriculture - Performance Management Information System) is a Laravel 11 backend API for the CARAGA Region (Region XIII), Philippines. It manages agricultural projects, crop/livestock statistics, progress reports, dashboard analytics, project approvals, financial disbursements, and public engagement.

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
│   ├── Controllers/   # 18 API controllers
│   ├── Middleware/    # ForceJsonResponse middleware
│   ├── Requests/      # Form request validation (Store/Update pairs)
│   └── Resources/     # API resource transformers
├── Interfaces/        # 16 Repository interfaces
├── Models/            # 26 Eloquent models
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

## Key Models

- **Project** - Main entity with budget, timeline, location, team members, milestones
- **ProjectApproval** - Approval workflow records
- **ProjectDisbursement** - Financial disbursements per project
- **Department** - Organizational units with KPIs
- **ProgressReport** - Periodic reports with metrics
- **CropProduction** / **LivestockStatistic** - Agricultural data
- **Region** / **Province** / **Municipality** - Location hierarchy
- **NewsUpdate** / **Document** - Content management
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

## API Routes (101+ Endpoints)

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

### 2. Project Approval Workflow
- Submit projects for approval
- Multi-level approval process
- Request changes before approval
- Track approval history
- Statistics on approval workflow

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

---

## Database Schema Overview

**Projects Module:**
- `projects`, `project_types`, `project_statuses`
- `project_team_members`, `project_milestones`
- `project_approvals`, `project_disbursements`

**Location Module:**
- `regions`, `provinces`, `municipalities`

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

---

## Implementation Summary

- 20 Controllers
- 18 Repository Interfaces
- 18 Repository Implementations
- 18 Service Classes
- 26 Eloquent Models
- 101+ API endpoints
- Service-Repository-Interface pattern
- RBAC implemented
- Audit logging functional
- Dashboard analytics
- Project approval workflow
- Financial disbursement tracking
- Notification system
- Location hierarchy management
- Project team assignment
- Milestone tracking with completion rate

**Status:** READY FOR DEPLOYMENT

*Version:* 2.0
*Updated:* 2026-01-28
*Region:* CARAGA (Region XIII), Philippines
*Department:* Department of Agriculture
