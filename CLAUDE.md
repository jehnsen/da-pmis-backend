## Project Overview

**DA-PMIS** (Department of Agriculture - Performance Management Information System) is a Laravel 11 backend API for the CARAGA Region (Region XIII), Philippines. It manages agricultural projects, crop/livestock statistics, progress reports, and public engagement.

## Tech Stack

- **Framework**: Laravel 11 with PHP 8.2+
- **Database**: MySQL
- **Authentication**: Laravel Passport (OAuth 2.0)
- **Architecture**: Service-Repository-Interface Pattern

## Project Structure

```
app/
├── Classes/           # Utility classes (ApiResponseClass)
├── Enums/             # Enumerations (IncidentSeverity, IncidentStatus)
├── Http/
│   ├── Controllers/   # 14 API controllers
│   ├── Middleware/    # ForceJsonResponse middleware
│   ├── Requests/      # Form request validation (Store/Update pairs)
│   └── Resources/     # API resource transformers
├── Interfaces/        # Repository interfaces
├── Models/            # 22 Eloquent models
├── Providers/         # Service providers (interface bindings)
├── Repositories/      # Repository implementations
├── Services/          # Business logic layer
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

## Key Models

- **Project** - Main entity with budget, timeline, location, team members, milestones
- **Department** - Organizational units with KPIs
- **ProgressReport** - Periodic reports with metrics
- **CropProduction** / **LivestockStatistic** - Agricultural data
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

## API Routes

**Public Endpoints** (no auth required):
- `POST /api/register`, `POST /api/login`
- `GET /api/projects`, `GET /api/news-updates`, `GET /api/documents`
- `GET /api/crop-productions`, `GET /api/livestock-statistics`
- `POST /api/contact-inquiries`, `POST /api/newsletter-subscriptions`

**Protected Endpoints** (require `auth:api` middleware):
- Full CRUD for projects, departments, progress-reports
- Management of crop/livestock data, news, documents
- User profile and logout

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
