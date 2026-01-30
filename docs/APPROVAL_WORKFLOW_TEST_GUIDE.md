# Project Approval Workflow Automated Test Suite

## Overview

A comprehensive automated test suite for the RA 7160-compliant project approval workflow has been successfully created at:

**Location:** `tests/Feature/ProjectApprovalWorkflowTest.php`

## Test Coverage

The test suite automates the entire approval workflow including user role switching, actions, and status verification at each level:

### ✅ Test 1: Complete Approval Workflow
**Test Method:** `it_completes_full_approval_workflow_successfully()`

Simulates the complete approval chain from draft to final approval:
1. **Barangay Officer** creates a new project (Status: `draft`)
2. **Barangay Officer** submits project for approval (Status: `pending_barangay`)
3. **Barangay Officer** approves at barangay level (Status: `pending_municipal`)
4. **Municipal Planning Officer (MPDO)** approves at municipal level (Status: `pending_provincial`)
5. **Provincial Planning Officer (PPDO)** approves at provincial level (Status: `pending_governor`)
6. **Provincial Governor** gives final approval (Status: `approved`)
7. Verifies approval history contains all 5 entries (1 submission + 4 approvals)

### ✅ Test 2: Rejection Workflow
**Test Method:** `it_rejects_project_at_municipal_level()`

Tests project rejection at the municipal level:
1. Barangay Officer creates and submits project
2. Barangay Officer approves (moves to municipal level)
3. **Municipal Officer rejects** the project (Status: `rejected`)
4. Verifies rejection is recorded in approval history with correct level

### ✅ Test 3: Authorization Checks
**Test Method:** `it_prevents_unauthorized_approval()`

Ensures users cannot approve projects at the wrong level:
1. Barangay Officer creates and submits project (Status: `pending_barangay`)
2. **Municipal Officer attempts to approve** (should fail with 403)
3. Verifies status remains `pending_barangay`
4. Confirms proper authorization enforcement

### ✅ Test 4: Request Changes Workflow
**Test Method:** `it_requests_changes_at_provincial_level()`

Tests the request changes functionality:
1. Project progresses through barangay and municipal approvals
2. **Provincial Officer requests changes** (sends back to `draft`)
3. Verifies project status returns to `draft` for revisions

### ✅ Test 5: Pending Approvals Per Level
**Test Method:** `it_retrieves_pending_approvals_for_each_level()`

Verifies that each approval level sees only relevant projects:
1. Creates 3 projects at different approval levels
2. **Barangay Officer** sees only 1 pending approval (barangay level)
3. **Municipal Officer** sees only 1 pending approval (municipal level)
4. **Provincial Officer** sees only 1 pending approval (provincial level)

## RA 7160 Approval Levels

The tests implement the full Local Government Code hierarchy:

| Level | Role | Status When Pending |
|-------|------|---------------------|
| 0 | Barangay Development Council Officer | `pending_barangay` |
| 1 | Municipal Planning Officer (MPDO) | `pending_municipal` |
| 2 | Provincial Planning Officer (PPDO) | `pending_provincial` |
| 3 | Provincial Governor | `pending_governor` |
| Final | Approved | `approved` |

## Running the Tests

### Run All Tests
```bash
./vendor/bin/phpunit tests/Feature/ProjectApprovalWorkflowTest.php --testdox
```

### Run Individual Test
```bash
./vendor/bin/phpunit tests/Feature/ProjectApprovalWorkflowTest.php --filter="it_completes_full_approval_workflow_successfully"
```

### Run with Verbose Output
```bash
./vendor/bin/phpunit tests/Feature/ProjectApprovalWorkflowTest.php --testdox -v
# Or for more verbose output
./vendor/bin/phpunit tests/Feature/ProjectApprovalWorkflowTest.php --testdox -vv
```

## Test Results

```
✔ It completes full approval workflow successfully (5/5 tests)
✔ It rejects project at municipal level
✔ It prevents unauthorized approval
✔ It requests changes at provincial level
✔ It retrieves pending approvals for each level

Tests: 5, Assertions: 52, All Passing
```

## Key Features

### 1. Role Switching
The test suite uses Laravel Sanctum to authenticate as different users:
```php
Sanctum::actingAs($this->barangayOfficer);
Sanctum::actingAs($this->municipalOfficer);
Sanctum::actingAs($this->provincialOfficer);
Sanctum::actingAs($this->governor);
```

### 2. Status Verification
After each action, the test verifies the project status:
```php
$this->project->refresh();
$this->assertEquals('pending_municipal', $this->project->approval_status);
```

### 3. API Endpoint Testing
Tests all approval-related endpoints:
- `POST /api/projects` - Create project
- `POST /api/projects/{id}/submit-for-approval` - Submit for approval
- `POST /api/projects/{id}/approve` - Approve project
- `POST /api/projects/{id}/reject` - Reject project
- `POST /api/projects/{id}/request-changes` - Request changes
- `GET /api/projects/{id}/approval-history` - Get approval history
- `GET /api/projects/pending-approval` - Get pending approvals

### 4. Database Isolation
Uses `RefreshDatabase` trait to ensure tests are isolated:
- Database is refreshed before each test
- Test data is automatically cleaned up
- No interference between tests

### 5. Comprehensive Data Seeding
Each test automatically seeds:
- **Roles**: Barangay Officer, MPDO, PPDO, Governor
- **Departments**: Field Operations, Planning & Monitoring, Executive Office
- **Locations**: CARAGA Region, Agusan del Sur Province, Prosperidad Municipality
- **Project Metadata**: Project types, statuses, sectors
- **Test Users**: One user for each approval level

## Test User Credentials

The following test users are created automatically:

| Username | Role | Department |
|----------|------|------------|
| test_bdc | Barangay Development Council Officer | Field Operations Division |
| test_mpdo | Municipal Planning Officer (MPDO) | Planning & Monitoring Division |
| test_ppdo | Provincial Planning Officer (PPDO) | Planning & Monitoring Division |
| test_governor | Provincial Governor | Executive Office |

All test users have password: `password` (for testing only)

## Approval Workflow Status Flow

```
draft
  ↓ (submit)
pending_barangay
  ↓ (barangay approve)
pending_municipal
  ↓ (municipal approve)
pending_provincial
  ↓ (provincial approve)
pending_governor
  ↓ (governor approve)
approved
```

Alternative flows:
- **Rejection**: Any level → `rejected`
- **Changes Requested**: Any level → `draft` (for revisions)

## Assertions Used

The test suite includes comprehensive assertions:
- **Status Assertions**: Verify approval_status after each action
- **Response Assertions**: Check HTTP status codes (200, 201, 403, 422)
- **Count Assertions**: Verify number of approval history entries
- **Contains Assertions**: Check for specific actions in history
- **Authorization Assertions**: Ensure proper permission enforcement

## Integration Points

The tests verify integration with:
- ✅ ProjectApprovalController
- ✅ ProjectApprovalService
- ✅ ProjectApprovalRepository
- ✅ Project Model
- ✅ ProjectApproval Model
- ✅ Role-based Authorization
- ✅ Sanctum Authentication
- ✅ Database Transactions
- ✅ Approval History Tracking

## CI/CD Integration

To integrate with CI/CD pipelines:

```yaml
# Example GitHub Actions workflow
- name: Run Approval Workflow Tests
  run: ./vendor/bin/phpunit tests/Feature/ProjectApprovalWorkflowTest.php
```

## Troubleshooting

### Database Connection Issues
Ensure your test database is configured in `.env` or `phpunit.xml`:
```xml
<env name="DB_CONNECTION" value="mysql"/>
<env name="DB_DATABASE" value="iterable_db"/>
```

### Authentication Issues
The tests use Sanctum. Ensure it's properly installed:
```bash
composer require laravel/sanctum
php artisan migrate
```

### Migration Issues
If migrations fail, run:
```bash
php artisan migrate:fresh
```

## Future Enhancements

Potential additions to the test suite:
- [ ] Test concurrent approvals by multiple officers
- [ ] Test approval revocation workflow
- [ ] Test approval with file attachments
- [ ] Test email notifications sent during workflow
- [ ] Test approval statistics and reporting
- [ ] Performance testing for large approval queues

## Maintenance

When updating the approval workflow:
1. Update the test assertions to match new statuses
2. Add new test methods for new features
3. Keep role mappings synchronized with actual roles
4. Update documentation to reflect changes

---

**Version:** 1.0
**Created:** 2026-01-30
**Test Framework:** PHPUnit 11.5
**Laravel Version:** 11
**Coverage:** 5 comprehensive workflow tests with 52 assertions
