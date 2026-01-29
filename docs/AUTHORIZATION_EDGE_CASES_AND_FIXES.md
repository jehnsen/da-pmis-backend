# Authorization Edge Cases & Comprehensive Fixes

## Overview

This document addresses critical authorization vulnerabilities and edge cases in the project approval system, particularly around role transitions, permission precedence, and performance optimization.

---

## **Critical Authorization Issues Identified**

### 🚨 **Issue 1: Role Transition Chaos**

**Scenario:**
```
Day 1: John is "Project Manager" of Project X
Day 5: John promoted to "Department Head"
Day 6: John can now edit ALL department projects
      BUT John is STILL assigned as PM on Project X

Question: Which permission applies?
- Department Head access (can edit all projects)?
- Project Manager access (can only edit assigned projects)?
- BOTH? (cumulative permissions)?
```

**Current Problem:**
- No clear permission precedence hierarchy
- Role changes don't update project assignments
- Conflicting permissions not resolved
- No audit trail for permission changes

**Real-World Impact:**
```
User A: PM on Project X → Gets promoted to Dept Head
User B: New PM assigned to Project X
User A: Can STILL edit Project X (via old PM assignment)
User B: Can ALSO edit Project X (new PM)
Result: TWO people think they're the sole PM! ❌
```

---

### 🚨 **Issue 2: Context-Dependent Permission Nightmare**

**Scenario:**
```
Project Status: Draft
- Project Manager: Can edit EVERYTHING ✅
- Team Member: Can edit assigned tasks only ✅

Project Status: Pending Approval
- Project Manager: Can edit NOTHING ❌
- Team Member: Can edit NOTHING ❌
- BUT: Approver can request changes, sending back to draft
- THEN: Who can edit? Original PM? New approver? Both?

Project Status: Approved
- NOBODY can edit (audit trail locked) ❌
- BUT: Admin needs to fix budget typo
- Exception needed, but who approves exception?
```

**Current Problem:**
- Permissions hardcoded in repository methods
- No flexible policy system
- Can't handle "edit if X AND Y BUT NOT Z" logic
- No exception workflow for locked projects

---

### 🚨 **Issue 3: Field-Level Permission Gap**

**Scenario:**
```
Project Manager should be able to:
- Edit title, description, timeline ✅
- Edit budget ❌ (requires finance officer approval)
- Edit approval_status ❌ (workflow-controlled)
- Edit team members ✅
- Edit milestones ✅ BUT NOT completed milestones ❌

Current system: ALL OR NOTHING
- Can edit project → Can change EVERYTHING including budget
- Can't edit project → Can't change ANYTHING
```

**Real-World Impact:**
```
PM needs to update project description while pending approval
Current: BLOCKED (entire project locked) ❌
Desired: Allow description edit, block budget/status changes ✅
```

---

### 🚨 **Issue 4: Performance Killer (N+1 Queries)**

**Current Permission Check:**
```php
// Checking permissions in controller (N+1 disaster)
public function update(Request $request, $id) {
    $project = Project::find($id);  // Query 1

    // Check if user is PM
    $isPM = $project->teamMembers()  // Query 2
        ->where('user_id', auth()->id())
        ->where('role', 'project_manager')
        ->exists();

    // Check if user is in same department
    $sameDept = $project->department_id == auth()->user()->department_id; // Query 3 (lazy load)

    // Check if user has role permission
    $hasRole = auth()->user()->role->name == 'admin'; // Query 4 (lazy load)

    // Check project status
    $canEdit = !$project->isPendingApproval(); // Already loaded, OK

    if (!($isPM || $sameDept || $hasRole) || !$canEdit) {
        abort(403);
    }

    // Now multiply this by 50 projects on a list page...
    // 50 projects × 4 queries = 200 database queries! 💀
}
```

---

### 🚨 **Issue 5: Team Member Access After PM Role Change**

**Scenario:**
```
Initial State:
- User A: Project Manager of Project X
- User B: Team Member on Project X (assigned by User A)
- User B can access Project X ✅

User A gets promoted to Department Head:
- User A removed from Project X team
- User A now has department-wide access
- User B loses access? (orphaned team member)
- New PM not yet assigned
- Who can manage User B's access now?
```

**Current Problem:**
- Team member access depends on PM assignment
- PM role change breaks team access chain
- No automatic reassignment workflow
- Team members orphaned without PM

---

## **Proposed Authorization Architecture**

### **1. Permission Precedence Hierarchy**

```
Permission Resolution Order (Highest to Lowest):
1. Explicit DENY (security locks, suspended users)
2. System Locks (approved projects, archived, deleted)
3. Workflow State (pending approval = read-only)
4. Role-Based Access (Admin > Dept Head > PM > Team Member)
5. Team Assignment (Project-specific roles)
6. Department Membership (same department access)
7. Public Access (is_public projects)
8. Default DENY
```

**Implementation:**
```php
class ProjectPermissionResolver
{
    public function canEdit(User $user, Project $project, ?string $field = null): bool
    {
        // 1. Explicit DENY
        if ($user->is_suspended || $user->hasExplicitDeny($project)) {
            return false;
        }

        // 2. System Locks
        if ($project->isApproved() && !$this->hasOverridePermission($user, 'edit_approved_projects')) {
            return false;
        }

        // 3. Workflow State
        if ($project->isPendingApproval() && !$this->isApprover($user, $project)) {
            return false;
        }

        // 4. Role-Based Access (highest priority wins)
        if ($user->isAdmin()) {
            return $this->canAdminEdit($project, $field);
        }

        if ($user->isDepartmentHead($project->department_id)) {
            return $this->canDeptHeadEdit($project, $field);
        }

        // 5. Team Assignment
        if ($this->isProjectManager($user, $project)) {
            return $this->canPMEdit($project, $field);
        }

        if ($this->isTeamMember($user, $project)) {
            return $this->canTeamMemberEdit($project, $field);
        }

        // 6. Department Membership
        if ($user->department_id === $project->department_id) {
            return $this->canDeptMemberEdit($project, $field);
        }

        // 7 & 8. Public access / Default deny
        return false;
    }

    private function canAdminEdit(Project $project, ?string $field): bool
    {
        // Admins can edit most fields except system-controlled
        $protectedFields = ['id', 'created_at', 'deleted_at'];
        return !in_array($field, $protectedFields);
    }

    private function canPMEdit(Project $project, ?string $field): bool
    {
        // PMs can't edit budget without finance approval
        $restrictedFields = ['budget', 'approval_status', 'submitted_by'];

        if (in_array($field, $restrictedFields)) {
            return false;
        }

        // PMs can't edit during approval workflow
        if ($project->isPendingApproval()) {
            return false;
        }

        return true;
    }
}
```

---

### **2. Role Transition Handler**

**Database Migration:**
```php
// Migration: create_role_transitions_table.php
Schema::create('role_transitions', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    $table->foreignId('from_role_id')->nullable()->constrained('roles');
    $table->foreignId('to_role_id')->constrained('roles');
    $table->foreignId('from_department_id')->nullable()->constrained('departments');
    $table->foreignId('to_department_id')->nullable()->constrained('departments');
    $table->enum('transition_type', ['promotion', 'transfer', 'demotion', 'reassignment']);
    $table->timestamp('effective_date');
    $table->foreignId('approved_by')->nullable()->constrained('users');
    $table->json('project_reassignments')->nullable(); // Track which projects need new PM
    $table->boolean('is_processed')->default(false);
    $table->timestamp('processed_at')->nullable();
    $table->timestamps();

    $table->index(['user_id', 'is_processed']);
    $table->index('effective_date');
});

// New table: Tracks historical team assignments
Schema::create('project_team_member_history', function (Blueprint $table) {
    $table->id();
    $table->foreignId('project_id')->constrained()->cascadeOnDelete();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    $table->string('role'); // project_manager, team_member, etc.
    $table->timestamp('assigned_at');
    $table->timestamp('removed_at')->nullable();
    $table->foreignId('removed_by')->nullable()->constrained('users');
    $table->string('removal_reason')->nullable();
    $table->timestamps();

    $table->index(['project_id', 'user_id']);
    $table->index(['user_id', 'removed_at']);
});
```

**Role Transition Handler:**
```php
class RoleTransitionHandler
{
    public function handlePromotion(User $user, Role $newRole, Department $newDepartment): void
    {
        DB::transaction(function () use ($user, $newRole, $newDepartment) {
            // Create transition record
            $transition = RoleTransition::create([
                'user_id' => $user->id,
                'from_role_id' => $user->role_id,
                'to_role_id' => $newRole->id,
                'from_department_id' => $user->department_id,
                'to_department_id' => $newDepartment->id,
                'transition_type' => 'promotion',
                'effective_date' => now(),
            ]);

            // Find all projects where user is PM
            $projectsAsPM = ProjectTeamMember::where('user_id', $user->id)
                ->where('role', 'project_manager')
                ->with('project')
                ->get();

            $reassignments = [];
            foreach ($projectsAsPM as $assignment) {
                // Archive old assignment
                ProjectTeamMemberHistory::create([
                    'project_id' => $assignment->project_id,
                    'user_id' => $user->id,
                    'role' => 'project_manager',
                    'assigned_at' => $assignment->created_at,
                    'removed_at' => now(),
                    'removal_reason' => 'User promoted to Department Head',
                ]);

                $reassignments[] = [
                    'project_id' => $assignment->project_id,
                    'project_title' => $assignment->project->title,
                    'old_pm_id' => $user->id,
                    'new_pm_id' => null, // To be assigned
                    'status' => 'pending_reassignment',
                ];

                // Remove PM assignment (user will have dept-wide access now)
                $assignment->delete();
            }

            // Update transition with reassignment data
            $transition->update([
                'project_reassignments' => $reassignments,
            ]);

            // Update user role
            $user->update([
                'role_id' => $newRole->id,
                'department_id' => $newDepartment->id,
            ]);

            // Notify user and department head
            $this->notifyRoleTransition($user, $transition, $reassignments);
        });
    }

    private function notifyRoleTransition(User $user, RoleTransition $transition, array $reassignments): void
    {
        // Notify user
        $user->notify(new RoleTransitionNotification($transition));

        // Notify department head about projects needing new PM
        if (!empty($reassignments)) {
            $deptHead = User::where('department_id', $transition->to_department_id)
                ->whereHas('role', fn($q) => $q->where('name', 'LIKE', '%department%head%'))
                ->first();

            if ($deptHead) {
                $deptHead->notify(new ProjectsNeedReassignmentNotification($reassignments));
            }
        }
    }
}
```

---

### **3. Field-Level Permission Matrix**

**Database Schema:**
```php
// Migration: create_field_permissions_table.php
Schema::create('field_permissions', function (Blueprint $table) {
    $table->id();
    $table->string('model'); // 'Project', 'Milestone', etc.
    $table->string('field'); // 'budget', 'title', 'status'
    $table->string('role'); // 'admin', 'department_head', 'project_manager'
    $table->json('conditions')->nullable(); // ['project_status' => 'draft']
    $table->boolean('can_read')->default(true);
    $table->boolean('can_edit')->default(false);
    $table->boolean('requires_approval')->default(false);
    $table->foreignId('approval_role_id')->nullable()->constrained('roles');
    $table->timestamps();

    $table->unique(['model', 'field', 'role'], 'model_field_role_unique');
    $table->index(['model', 'role']);
});

// Seed field permissions
DB::table('field_permissions')->insert([
    // Project Manager permissions
    ['model' => 'Project', 'field' => 'title', 'role' => 'project_manager', 'can_read' => true, 'can_edit' => true, 'conditions' => json_encode(['approval_status' => ['draft', 'rejected']])],
    ['model' => 'Project', 'field' => 'description', 'role' => 'project_manager', 'can_read' => true, 'can_edit' => true, 'conditions' => json_encode(['approval_status' => ['draft', 'rejected']])],
    ['model' => 'Project', 'field' => 'budget', 'role' => 'project_manager', 'can_read' => true, 'can_edit' => false], // Can't edit budget
    ['model' => 'Project', 'field' => 'budget', 'role' => 'finance_officer', 'can_read' => true, 'can_edit' => true], // Finance can edit
    ['model' => 'Project', 'field' => 'approval_status', 'role' => 'project_manager', 'can_read' => true, 'can_edit' => false], // Workflow-controlled

    // Department Head permissions
    ['model' => 'Project', 'field' => '*', 'role' => 'department_head', 'can_read' => true, 'can_edit' => true, 'conditions' => json_encode(['department_id' => 'user.department_id'])],

    // Admin permissions
    ['model' => 'Project', 'field' => '*', 'role' => 'admin', 'can_read' => true, 'can_edit' => true],
]);
```

**Field Permission Checker:**
```php
class FieldPermissionChecker
{
    private Collection $permissionsCache;

    public function canEdit(User $user, Model $model, string $field): bool
    {
        // Load permissions once per request (cached)
        if (!isset($this->permissionsCache)) {
            $this->loadPermissions($user);
        }

        $modelClass = class_basename($model);
        $userRole = $user->role->name;

        // Check exact field permission
        $permission = $this->permissionsCache
            ->where('model', $modelClass)
            ->where('field', $field)
            ->where('role', $userRole)
            ->first();

        // Check wildcard permission if no exact match
        if (!$permission) {
            $permission = $this->permissionsCache
                ->where('model', $modelClass)
                ->where('field', '*')
                ->where('role', $userRole)
                ->first();
        }

        if (!$permission || !$permission->can_edit) {
            return false;
        }

        // Check conditions
        if ($permission->conditions) {
            return $this->evaluateConditions($permission->conditions, $model, $user);
        }

        return true;
    }

    private function evaluateConditions(array $conditions, Model $model, User $user): bool
    {
        foreach ($conditions as $field => $expectedValue) {
            // Handle user context (e.g., 'department_id' => 'user.department_id')
            if (is_string($expectedValue) && str_starts_with($expectedValue, 'user.')) {
                $userField = substr($expectedValue, 5); // Remove 'user.'
                $expectedValue = $user->$userField;
            }

            // Handle array of allowed values
            if (is_array($expectedValue)) {
                if (!in_array($model->$field, $expectedValue)) {
                    return false;
                }
            } else {
                if ($model->$field !== $expectedValue) {
                    return false;
                }
            }
        }

        return true;
    }

    private function loadPermissions(User $user): void
    {
        // Load all permissions for user's role (single query)
        $this->permissionsCache = FieldPermission::where('role', $user->role->name)
            ->get();
    }
}
```

---

### **4. Performance Optimization (Eager Loading)**

**Problem: N+1 Queries**
```php
// BAD: 51 queries for 50 projects
$projects = Project::all(); // 1 query
foreach ($projects as $project) {
    if (auth()->user()->canEdit($project)) { // 50 queries (checking team members)
        // ...
    }
}
```

**Solution: Eager Load Permissions**
```php
// GOOD: 3 queries for 50 projects
$user = auth()->user()->load(['role', 'department', 'projectTeamMemberships.project']);

$projects = Project::with([
    'department',
    'teamMembers' => fn($q) => $q->where('user_id', $user->id),
    'approvals' => fn($q) => $q->latest()->limit(1),
])->get();

// Now permission checks use already-loaded data (no additional queries)
$projectsWithPermissions = $projects->map(function ($project) use ($user) {
    return [
        'project' => $project,
        'can_edit' => $this->permissionResolver->canEdit($user, $project),
        'can_delete' => $this->permissionResolver->canDelete($user, $project),
        'can_approve' => $this->permissionResolver->canApprove($user, $project),
    ];
});
```

**Authorization Service with Caching:**
```php
class OptimizedAuthorizationService
{
    private array $permissionCache = [];

    public function loadUserPermissions(User $user): void
    {
        // Load all user relationships in ONE query each
        $user->load([
            'role',
            'department',
            'projectTeamMemberships' => fn($q) => $q->with('project:id,approval_status,department_id'),
            'delegations' => fn($q) => $q->where('is_active', true)->where('end_date', '>=', now()),
        ]);

        // Cache role permissions (single query)
        $this->permissionCache['field_permissions'] = FieldPermission::where('role', $user->role->name)->get();

        // Cache user's project IDs for quick lookup
        $this->permissionCache['managed_projects'] = $user->projectTeamMemberships
            ->where('role', 'project_manager')
            ->pluck('project_id')
            ->toArray();

        $this->permissionCache['team_projects'] = $user->projectTeamMemberships
            ->pluck('project_id')
            ->toArray();
    }

    public function canEditProject(User $user, Project $project): bool
    {
        // Use cached data instead of querying database
        if (in_array($project->id, $this->permissionCache['managed_projects'] ?? [])) {
            return !$project->isPendingApproval() && !$project->isApproved();
        }

        if ($user->isDepartmentHead() && $user->department_id === $project->department_id) {
            return true;
        }

        return false;
    }
}
```

---

## **Implementation Priority**

| Priority | Feature | Impact | Complexity | Est. LOC |
|----------|---------|--------|------------|----------|
| 🔴 Critical | Permission Precedence Hierarchy | High | Medium | 150 |
| 🔴 Critical | N+1 Query Fix | High | Low | 50 |
| 🟡 High | Role Transition Handler | Medium | High | 200 |
| 🟡 High | Field-Level Permissions | Medium | High | 250 |
| 🟢 Medium | Team Member Orphan Prevention | Low | Medium | 100 |

---

## **Testing Scenarios**

### Test 1: Role Promotion Permission Precedence

```php
// Setup
$user = User::create(['role' => 'project_manager']);
$project = Project::create(['department_id' => 1]);
ProjectTeamMember::create(['project_id' => $project->id, 'user_id' => $user->id, 'role' => 'project_manager']);

// User can edit as PM
$this->assertTrue($authService->canEdit($user->fresh(), $project));

// Promote to Department Head
$user->update(['role_id' => Role::where('name', 'department_head')->first()->id]);

// User can STILL edit (now via department head permission, not PM)
$this->assertTrue($authService->canEdit($user->fresh(), $project));

// Verify PM assignment removed
$this->assertFalse(ProjectTeamMember::where('project_id', $project->id)->where('user_id', $user->id)->exists());

// Verify historical record created
$this->assertTrue(ProjectTeamMemberHistory::where('project_id', $project->id)->where('user_id', $user->id)->exists());
```

### Test 2: Field-Level Permission

```php
// PM can edit title but not budget
$pm = User::factory()->create(['role' => 'project_manager']);
$project = Project::factory()->create();

$this->assertTrue($fieldChecker->canEdit($pm, $project, 'title'));
$this->assertFalse($fieldChecker->canEdit($pm, $project, 'budget'));

// Finance officer can edit budget
$financeOfficer = User::factory()->create(['role' => 'finance_officer']);
$this->assertTrue($fieldChecker->canEdit($financeOfficer, $project, 'budget'));
```

### Test 3: N+1 Prevention

```php
$user = auth()->user();

// Load permissions once
$authService->loadUserPermissions($user);

// Get 100 projects
$projects = Project::with(['department', 'teamMembers'])->limit(100)->get();

// Check permissions (should not trigger additional queries)
DB::enableQueryLog();
foreach ($projects as $project) {
    $canEdit = $authService->canEditProject($user, $project);
}
$queries = DB::getQueryLog();

// Assert: No queries executed (all data was eager-loaded)
$this->assertCount(0, $queries);
```

---

## **Rollout Plan**

### Phase 1: Critical Fixes (Week 1)
- ✅ Implement Permission Precedence Resolver
- ✅ Fix N+1 query issues
- ✅ Create authorization middleware
- ✅ Add permission caching

### Phase 2: Role Management (Week 2)
- ✅ Implement Role Transition Handler
- ✅ Create historical tracking
- ✅ Build reassignment workflow
- ✅ Add notifications

### Phase 3: Advanced Features (Week 3)
- ✅ Implement Field-Level Permissions
- ✅ Create permission management UI
- ✅ Add permission audit logging
- ✅ Build exception request workflow

---

## **Configuration**

```php
// config/authorization.php
return [
    // Permission precedence order
    'precedence' => [
        'explicit_deny',
        'system_locks',
        'workflow_state',
        'role_based',
        'team_assignment',
        'department_membership',
        'public_access',
    ],

    // Cache duration for permissions (in minutes)
    'cache_ttl' => 60,

    // Enable field-level permissions
    'field_level_enabled' => true,

    // Role transition requires approval
    'transition_requires_approval' => true,

    // Auto-archive old team assignments on role change
    'auto_archive_assignments' => true,
];
```

---

## **Summary**

This authorization overhaul addresses:
1. ✅ Role transition confusion (clear precedence hierarchy)
2. ✅ Permission conflicts (highest priority wins)
3. ✅ Field-level granularity (specific field permissions)
4. ✅ Performance issues (eager loading, caching)
5. ✅ Team member orphaning (historical tracking, reassignment workflow)
6. ✅ Audit trail (comprehensive logging)

**Result:** Robust, performant, and maintainable authorization system that handles real-world edge cases.

---

**Last Updated:** 2026-01-29
**Status:** 📋 Design Complete - Ready for Implementation
**Estimated Total LOC:** ~750 lines
