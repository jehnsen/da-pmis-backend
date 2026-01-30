# RA 7160 Territorial Jurisdiction Validation

**Document Version:** 2.0
**Last Updated:** 2026-01-30
**Implementation Status:** ✅ COMPLETE

## Overview

This document outlines the **complete implementation** of territorial jurisdiction restrictions in the PLGU-GIP system to ensure compliance with **RA 7160 (Local Government Code of 1991)**, which establishes the territorial authority and jurisdictional boundaries for Local Government Unit (LGU) officers.

## Legal Basis: RA 7160 Compliance

Under **RA 7160**, the Local Government Code of the Philippines:

- **Section 476**: Municipal Planning and Development Officers (MPDO) have jurisdiction only within their assigned municipality
- **Article X**: Barangay Development Council (BDC) officers operate within barangay and municipal boundaries
- **Territorial Jurisdiction**: Officers cannot exercise authority over projects outside their assigned territorial boundaries

## Implementation Summary

### Affected Roles

The following roles are restricted by territorial jurisdiction:

| Role ID | Role Name | Jurisdiction Level | Restriction |
|---------|-----------|-------------------|-------------|
| 7 | Barangay Development Council Officer | Municipality | Can only access projects within their municipality |
| 5 | Municipal Planning Officer (MPDO) | Municipality | Can only access projects within their municipality |
| 6 | Municipal Officer | Municipality | Can only access projects within their municipality |

**Unrestricted Roles** (Province-wide or higher jurisdiction):
- Provincial Planning Officer (PPDO) - Role ID 3
- Provincial Governor - Role ID 2
- Provincial Officer - Role ID 4
- System Administrator - Role ID 1

## Affected Endpoints

### 1. `GET /api/projects?per_page=15` (Authenticated)

**Before:** All users could see all projects
**After:** Municipal and Barangay officers only see projects from their municipality

**Implementation:**
- Added `forUser()` scope to filter projects by user's municipality
- Users without assigned municipality get empty results

**Example Request:**
```http
GET /api/projects?per_page=15
Authorization: Bearer {token}
```

**Example Response (MPDO from Municipality ID 5):**
```json
{
  "data": [
    {
      "id": 1,
      "title": "Rice Irrigation Project",
      "municipality_id": 5,
      "municipality": {
        "id": 5,
        "name": "Butuan City"
      }
    }
  ]
}
```

---

### 2. `GET /api/projects/by-approval-status?status=draft`

**Before:** All users could see all projects with specified status
**After:** Municipal and Barangay officers only see projects from their municipality

**Implementation:**
- Added `forUser()` scope in `getProjectsByApprovalStatus()` method
- Updated validation to include all RA 7160 approval levels

**Valid Status Values:**
- `draft`
- `pending_barangay`
- `pending_municipal`
- `pending_provincial`
- `pending_governor`
- `approved`
- `rejected`

**Example Request:**
```http
GET /api/projects/by-approval-status?status=pending_municipal&per_page=15
Authorization: Bearer {token}
```

---

### 3. `GET /api/projects/pending-approval`

**Before:** Already had municipality filtering ✅
**After:** No changes needed (already compliant)

**Existing Implementation:**
```php
// Lines 385-394 in ProjectApprovalRepository.php
if ($level === 'municipal' || $level === 'barangay') {
    if (!$user->municipality_id) {
        return Project::whereRaw('1 = 0')->paginate($perPage);
    }
    $query->where('municipality_id', $user->municipality_id);
}
```

---

### 4. `GET /api/projects/{id}` (Authenticated)

**Before:** Any authenticated user could view any project
**After:** Municipal and Barangay officers can only view projects from their municipality

**Implementation:**
- Updated `show()` method to pass authenticated user
- Returns 404 if project not found or user doesn't have access

**Example:**
```http
GET /api/projects/123
Authorization: Bearer {token}
```

**Response (if project from different municipality):**
```json
{
  "message": "Not Found"
}
```

---

### 5. `PUT /api/projects/{id}` (Update)

**Before:** Any authenticated user could update any project
**After:** Municipal and Barangay officers can only update projects from their municipality

**Implementation:**
- Added territorial jurisdiction validation before update
- Throws clear error message if access denied

**Error Response:**
```json
{
  "message": "Failed to update project",
  "error": "Access denied: You can only modify projects within your municipality. This project belongs to a different municipality (RA 7160 territorial jurisdiction)."
}
```

---

### 6. `DELETE /api/projects/{id}`

**Before:** Any authenticated user could delete any project
**After:** Municipal and Barangay officers can only delete projects from their municipality

**Implementation:**
- Added territorial jurisdiction validation before deletion
- Returns 404 or throws error if access denied

---

### 7. `POST /api/projects/{id}/approve`

**Before:** Already had municipality validation ✅
**After:** No changes needed (already compliant)

**Existing Implementation:**
```php
// Lines 496-513 in ProjectApprovalRepository.php
if ($userLevel === 'municipal') {
    if (!$user->municipality_id || !$project->municipality_id) {
        return false;
    }
    return $user->municipality_id === $project->municipality_id;
}
```

---

### 8. `POST /api/projects/{id}/reject`

**Before:** Already had municipality validation ✅
**After:** No changes needed (already compliant)

---

## Technical Implementation Details

### 1. Project Model - `forUser()` Scope

**File:** [app/Models/Project.php](../app/Models/Project.php)

Added a reusable Eloquent scope for territorial filtering:

```php
/**
 * Scope for filtering projects by territorial jurisdiction (RA 7160 Compliance)
 * Municipal and Barangay officers can only see projects within their municipality
 * Provincial and Governor levels can see all projects
 *
 * @param \Illuminate\Database\Eloquent\Builder $query
 * @param \App\Models\User|null $user
 * @return \Illuminate\Database\Eloquent\Builder
 */
public function scopeForUser($query, $user = null)
{
    // If no user provided, return unfiltered (for public access)
    if (!$user) {
        return $query;
    }

    // If user has no role, return unfiltered
    if (!$user->role) {
        return $query;
    }

    $roleName = strtolower($user->role->name);

    // Determine user's approval level for territorial jurisdiction
    $isMunicipalLevel = str_contains($roleName, 'municipal') ||
                       str_contains($roleName, 'mpdo') ||
                       str_contains($roleName, 'barangay') ||
                       str_contains($roleName, 'bdc');

    // Municipal and Barangay level officers: filter by municipality
    if ($isMunicipalLevel) {
        if ($user->municipality_id) {
            return $query->where('municipality_id', $user->municipality_id);
        } else {
            // User has municipal/barangay role but no municipality assigned
            // Return empty result for security
            return $query->whereRaw('1 = 0');
        }
    }

    // Provincial, Governor, and Admin levels: can see all projects
    return $query;
}
```

---

### 2. Repository Layer Updates

**Files Modified:**
- [app/Repositories/ProjectRepository.php](../app/Repositories/ProjectRepository.php)
- [app/Repositories/ProjectApprovalRepository.php](../app/Repositories/ProjectApprovalRepository.php)
- [app/Interfaces/ProjectRepositoryInterface.php](../app/Interfaces/ProjectRepositoryInterface.php)
- [app/Interfaces/ProjectApprovalRepositoryInterface.php](../app/Interfaces/ProjectApprovalRepositoryInterface.php)

**Changes:**

#### ProjectRepository.php

```php
// Updated method signature to accept user parameter
public function paginate(int $perPage = 15, array $filters = [], $user = null)
{
    $query = $this->model->query()
        ->with(['department', 'sector', 'projectType', 'projectStatus'])
        ->forUser($user); // Apply RA 7160 territorial jurisdiction filtering

    // ... rest of filtering logic
    return $query->paginate($perPage);
}

// Find method with territorial filtering
public function find($id, $user = null)
{
    $query = $this->model->query()
        ->with(['department', 'sector', 'projectType', 'projectStatus', 'teamMembers', 'milestones'])
        ->forUser($user); // Apply RA 7160 territorial jurisdiction filtering

    return $query->find($id);
}

// Update method with territorial validation
public function update($id, array $data, $user = null)
{
    $project = $this->find($id, $user); // Apply territorial jurisdiction check

    if (!$project) {
        // Project not found or user doesn't have access due to territorial jurisdiction
        return null;
    }

    // RA 7160 Territorial Jurisdiction Validation
    if ($user && $user->municipality_id) {
        $roleName = strtolower($user->role->name ?? '');
        $isMunicipalLevel = str_contains($roleName, 'municipal') ||
                           str_contains($roleName, 'mpdo') ||
                           str_contains($roleName, 'barangay') ||
                           str_contains($roleName, 'bdc');

        if ($isMunicipalLevel && $project->municipality_id !== $user->municipality_id) {
            throw new \Exception(
                'Access denied: You can only modify projects within your municipality. ' .
                'This project belongs to a different municipality (RA 7160 territorial jurisdiction).'
            );
        }
    }

    // ... rest of update logic
}

// Delete method with territorial validation
public function delete($id, $user = null)
{
    $project = $this->find($id, $user); // Apply territorial jurisdiction check

    if (!$project) {
        return null;
    }

    // RA 7160 Territorial Jurisdiction Validation
    if ($user && $user->municipality_id) {
        $roleName = strtolower($user->role->name ?? '');
        $isMunicipalLevel = str_contains($roleName, 'municipal') ||
                           str_contains($roleName, 'barangay');

        if ($isMunicipalLevel && $project->municipality_id !== $user->municipality_id) {
            throw new \Exception(
                'Access denied: You can only delete projects within your municipality. ' .
                'This project belongs to a different municipality (RA 7160 territorial jurisdiction).'
            );
        }
    }

    $project->delete();
    return $project;
}
```

#### ProjectApprovalRepository.php

```php
// Updated to pass user and apply forUser scope
public function getProjectsByApprovalStatus(string $status, int $perPage = 15, $user = null)
{
    $query = Project::where('approval_status', $status)
        ->with(['department', 'projectType', 'projectStatus', 'submitter', 'municipality'])
        ->forUser($user); // Apply RA 7160 territorial jurisdiction filtering

    return $query->orderBy('updated_at', 'desc')->paginate($perPage);
}
```

---

### 3. Service Layer Updates

**File:** [app/Services/ProjectService.php](../app/Services/ProjectService.php)

Updated all methods to accept and pass the `$user` parameter:

```php
public function list(int $perPage = 15, array $filters = [], $user = null): LengthAwarePaginator|Collection
{
    return $this->repo->paginate($perPage, $filters, $user);
}

public function getById(int $id, $user = null)
{
    return $this->repo->find($id, $user);
}

public function update(int $id, array $data, $user = null)
{
    return $this->repo->update($id, $data, $user);
}

public function delete(int $id, $user = null)
{
    return $this->repo->delete($id, $user);
}
```

**File:** [app/Services/ProjectApprovalService.php](../app/Services/ProjectApprovalService.php)

```php
public function getProjectsByApprovalStatus(string $status, int $perPage = 15, $user = null)
{
    return $this->repository->getProjectsByApprovalStatus($status, $perPage, $user);
}
```

---

### 4. Controller Layer Updates

**File:** [app/Http/Controllers/ProjectController.php](../app/Http/Controllers/ProjectController.php)

Updated all methods to pass authenticated user:

```php
// ProjectController::index()
public function index(Request $request)
{
    // ... filter collection

    // Apply RA 7160 territorial jurisdiction for authenticated users
    $user = $request->user();
    $data = $this->service->list($perPage, $filters, $user);

    return ProjectResource::collection($data)->additional([
        'filters_applied' => $appliedFilters,
    ]);
}

// ProjectController::show()
public function show(Request $request, int $project): ProjectResource
{
    // Apply RA 7160 territorial jurisdiction for authenticated users
    $user = $request->user();
    $proj = $this->service->getById($project, $user);
    abort_unless($proj, 404);
    // ...
}

// ProjectController::update()
public function update(UpdateProjectRequest $request, int $project): JsonResponse
{
    try {
        // Apply RA 7160 territorial jurisdiction for authenticated users
        $user = $request->user();
        $proj = $this->service->update($project, $request->validated(), $user);

        if (!$proj) {
            return response()->json([
                'message' => 'Project not found or you do not have permission to update this project'
            ], 404);
        }
        // ...
    }
}

// ProjectController::destroy()
public function destroy(Request $request, int $project): JsonResponse
{
    try {
        // Apply RA 7160 territorial jurisdiction for authenticated users
        $user = $request->user();
        $deleted = $this->service->delete($project, $user);

        if (!$deleted) {
            return response()->json([
                'message': 'Project not found or you do not have permission to delete this project'
            ], 404);
        }
        // ...
    }
}
```

**File:** [app/Http/Controllers/ProjectApprovalController.php](../app/Http/Controllers/ProjectApprovalController.php)

```php
public function byApprovalStatus(Request $request): JsonResponse
{
    try {
        $request->validate([
            'status' => ['required', 'string', 'in:draft,pending_barangay,pending_municipal,pending_provincial,pending_governor,approved,rejected'],
        ]);

        $perPage = (int) $request->query('per_page', 15);

        // Apply RA 7160 territorial jurisdiction for authenticated users
        $user = $request->user();
        $projects = $this->service->getProjectsByApprovalStatus($request->status, $perPage, $user);

        return ProjectResource::collection($projects)->response();
    } catch (\Exception $e) {
        return response()->json([
            'message' => 'Failed to retrieve projects',
            'error' => $e->getMessage(),
        ], 500);
    }
}
```

---

## Security Considerations

### 1. Default Deny Approach

When a municipal/barangay officer has **no municipality assigned** (`municipality_id = null`):

```php
if (!$user->municipality_id) {
    return $query->whereRaw('1 = 0'); // Return empty result
}
```

**Rationale:** Security-first approach prevents accidental exposure of data.

---

### 2. Role Detection

The system detects municipal-level roles using multiple patterns:

```php
$isMunicipalLevel = str_contains($roleName, 'municipal') ||
                   str_contains($roleName, 'mpdo') ||
                   str_contains($roleName, 'barangay') ||
                   str_contains($roleName, 'bdc');
```

This ensures future role variations are automatically covered.

---

### 3. Public Access Unchanged

Public (unauthenticated) endpoints remain **unrestricted**:
- `GET /api/projects` (public)
- `GET /api/projects/{id}` (public)

Only **authenticated** endpoints enforce territorial restrictions.

---

## Testing Guide

### Test Case 1: MPDO Access Restriction

**Setup:**
1. Create user with role "Municipal Planning Officer (MPDO)"
2. Assign `municipality_id = 5`
3. Create projects with `municipality_id = 5` and `municipality_id = 10`

**Expected Results:**
```http
GET /api/projects
Authorization: Bearer {mpdo_token}

Response: Only projects with municipality_id = 5
```

---

### Test Case 2: Provincial Officer Access

**Setup:**
1. Create user with role "Provincial Planning Officer (PPDO)"
2. Assign any municipality (or null)
3. Create projects across multiple municipalities

**Expected Results:**
```http
GET /api/projects
Authorization: Bearer {ppdo_token}

Response: ALL projects (no filtering)
```

---

### Test Case 3: Update Restriction

**Setup:**
1. MPDO user from Municipality 5
2. Attempt to update project from Municipality 10

**Expected Results:**
```http
PUT /api/projects/123
Authorization: Bearer {mpdo_token}

Response: 404 Not Found or
{
  "message": "Failed to update project",
  "error": "Access denied: You can only modify projects within your municipality..."
}
```

---

### Test Case 4: Approval Workflow Compliance

**Setup:**
1. MPDO from Municipality 5
2. Project from Municipality 10 at `pending_municipal` status

**Expected Results:**
```http
POST /api/projects/123/approve
Authorization: Bearer {mpdo_token}

Response: 403 Forbidden
{
  "message": "You do not have permission to approve this project at the current level"
}
```

---

## Database Requirements

### User Table

Users must have the following fields:
- `municipality_id` (nullable, foreign key to `municipalities.id`)
- `role_id` (foreign key to `roles.id`)

### Project Table

Projects must have:
- `municipality_id` (nullable, foreign key to `municipalities.id`)
- `approval_status` (enum: draft, pending_barangay, pending_municipal, pending_provincial, pending_governor, approved, rejected)

---

## Migration Notes

### For Existing Deployments

1. **Assign Municipality to Users:**
   ```sql
   UPDATE users
   SET municipality_id = [municipality_id]
   WHERE role_id IN (5, 6, 7); -- MPDO, Municipal Officer, BDC
   ```

2. **Assign Municipality to Projects:**
   ```sql
   UPDATE projects
   SET municipality_id = [municipality_id]
   WHERE municipality_id IS NULL;
   ```

3. **Test with Sample Users:**
   - Create test accounts for each role level
   - Verify access restrictions work correctly

---

## Compliance Checklist

- [x] Municipal Planning Officers (MPDO) restricted to their municipality
- [x] Barangay Development Council Officers restricted to their municipality
- [x] Provincial officers have province-wide access
- [x] Approval workflow respects territorial jurisdiction
- [x] CRUD operations validate municipality boundaries
- [x] List/filter endpoints apply territorial filters
- [x] Clear error messages for access denied scenarios
- [x] Security-first approach (default deny when municipality unassigned)
- [x] Public endpoints remain unrestricted
- [x] Documentation complete

---

## Files Modified

### Models
- [app/Models/Project.php](../app/Models/Project.php) - Added `scopeForUser()` method

### Repositories
- [app/Repositories/ProjectRepository.php](../app/Repositories/ProjectRepository.php)
- [app/Repositories/ProjectApprovalRepository.php](../app/Repositories/ProjectApprovalRepository.php)

### Interfaces
- [app/Interfaces/ProjectRepositoryInterface.php](../app/Interfaces/ProjectRepositoryInterface.php)
- [app/Interfaces/ProjectApprovalRepositoryInterface.php](../app/Interfaces/ProjectApprovalRepositoryInterface.php)

### Services
- [app/Services/ProjectService.php](../app/Services/ProjectService.php)
- [app/Services/ProjectApprovalService.php](../app/Services/ProjectApprovalService.php)

### Controllers
- [app/Http/Controllers/ProjectController.php](../app/Http/Controllers/ProjectController.php)
- [app/Http/Controllers/ProjectApprovalController.php](../app/Http/Controllers/ProjectApprovalController.php)

---

## Related Documentation

- [RA 7160 Refactoring Guide](RA_7160_REFACTORING_GUIDE.md)
- [Project Approval API](PROJECT_APPROVAL_API.md)
- [CLAUDE.md](../CLAUDE.md) - Project overview

---

## Support

For questions or issues related to territorial jurisdiction implementation:
1. Review this documentation
2. Check the RA 7160 Refactoring Guide
3. Review code comments in affected files
4. Test with appropriate user roles and municipalities

---

**Implementation Complete: 2026-01-30**
**Next Review:** Before production deployment
