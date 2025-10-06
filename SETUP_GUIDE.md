# DA-CARAGA PMIS Backend Setup Guide

## Quick Start

### 1. Environment Setup
```bash
# Copy environment file
cp .env.example .env

# Configure database in .env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=da_pmis
DB_USERNAME=your_username
DB_PASSWORD=your_password

# Generate application key
php artisan key:generate
```

### 2. Install Dependencies
```bash
composer install
```

### 3. Database Setup
```bash
# Run migrations and seeders
php artisan migrate:fresh --seed
```

This will create and populate the database with realistic CARAGA Region data (~950+ records).

---

## Default Login Credentials

**Administrator Account:**
```
Username: admin
Password: Password123!
```

**Regional Director Account:**
```
Username: mrodriguez
Password: Password123!
```

**All Users Default Password:** `Password123!`

> ⚠️ **Security Warning:** Change all default passwords in production!

---

## What Gets Seeded

### Configuration Data
- ✅ 10 Project Types
- ✅ 7 Project Statuses
- ✅ 10 Document Categories
- ✅ 6 Regions (CARAGA + 5 provinces)

### User Management
- ✅ 29 Permissions
- ✅ 7 Roles
- ✅ 15 Departments
- ✅ 15 Users

### Content Data
- ✅ 20 Projects (₱18M - ₱125M)
- ✅ 300+ Crop Production Records
- ✅ 400+ Livestock Statistics
- ✅ 15 News Updates
- ✅ 20 Documents

---

## API Testing

### 1. Login
```bash
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{"username":"admin","password":"Password123!"}'
```

### 2. Get Projects (Public)
```bash
curl http://localhost:8000/api/projects
```

### 3. Create Project (Protected)
```bash
curl -X POST http://localhost:8000/api/projects \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "title": "New Project",
    "description": "Description",
    "department_id": 1,
    "project_type_id": 1,
    "project_status_id": 1,
    "budget": 50000000,
    "start_date": "2025-01-01",
    "end_date": "2025-12-31",
    "is_public": true
  }'
```

---

## Key Features

### 1. RBAC (Role-Based Access Control)
- Public users see limited data
- Authenticated users see full details including budget and team members

### 2. Audit Logging
- Automatic tracking of all create/update/delete operations
- Logs user, IP address, and changes

### 3. Soft Deletes
- Critical models use soft deletes for data recovery

### 4. CARAGA Region Data
All data is specific to CARAGA Region XIII provinces:
- Agusan del Norte
- Agusan del Sur
- Surigao del Norte
- Surigao del Sur
- Dinagat Islands

---

## Documentation

- **Seeder Details:** See `SEEDER_DOCUMENTATION.md`
- **Migration Sequence:** See `MIGRATION_SEQUENCE.md`
- **Project Requirements:** See `README.md`

---

## Common Commands

```bash
# Start dev server
php artisan serve

# Run migrations
php artisan migrate

# Fresh migration with seed
php artisan migrate:fresh --seed

# Run specific seeder
php artisan db:seed --class=ProjectSeeder

# Clear caches
php artisan optimize:clear

# View routes
php artisan route:list
```

---

## Troubleshooting

### Database Connection Error
```bash
# Check .env settings
# Test connection
php artisan migrate:status
```

### Class Not Found
```bash
composer dump-autoload
php artisan config:clear
```

### Permission Denied
```bash
chmod -R 775 storage bootstrap/cache
```

---

**Created:** 2025-10-06
**CARAGA PMIS Backend v1.0**
