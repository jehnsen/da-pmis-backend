# Data Consistency & Cascading Updates - Critical Fixes

## Overview

This document addresses critical data consistency issues, cascading update problems, and soft delete complications that can cause incorrect reports, broken references, and data integrity violations.

---

## 🚨 **Critical Issue 1: Budget Update Cascade**

### **Scenario:**
```
User updates Project budget: ₱5,000,000 → ₱8,000,000
```

### **Current Problem: Single Change Affects 10+ Tables**

| Table/System | Impact | Currently Handled? |
|--------------|--------|-------------------|
| `projects.budget` | Updated | ✅ Yes |
| `project_disbursements` | May now exceed budget | ❌ No validation |
| Department totals | Aggregated budget changes | ❌ Not recalculated |
| Dashboard cache | Shows old ₱5M | ❌ Not invalidated |
| Progress reports | Budget utilization % wrong | ❌ Not recalculated |
| Financial summaries | Cached totals stale | ❌ Not invalidated |
| Audit logs | Change tracked | ✅ Yes (Auditable trait) |
| Notifications | Finance officer needs alert | ❌ Not sent |
| Approvals | May need re-approval | ❌ Not checked |
| Budget allocations | May violate department cap | ❌ Not validated |

**Result:** ❌ **DATA INCONSISTENCY ACROSS SYSTEM**

---

### **Real-World Failure Example**

```php
// Current Implementation (BROKEN)
public function update($id, array $data)
{
    $project = Project::find($id);

    // Budget increased
    $project->update(['budget' => 8000000]); // Was 5000000

    // ❌ PROBLEMS:
    // 1. Disbursements total = ₱6M, now only 75% of budget (was 120% over!)
    // 2. Department budget total still shows old ₱5M
    // 3. Dashboard cached "Department Budget: ₱50M" (should be ₱53M)
    // 4. Finance officer NOT notified of ₱3M increase
    // 5. Progress report shows "120% utilized" (outdated)
    // 6. Approval status NOT checked (should draft need re-approval for big change?)
}
```

**Impact:**
- ✅ Database updated
- ❌ Reports show wrong data
- ❌ Dashboards outdated
- ❌ Finance not notified
- ❌ Approval workflow broken

---

## 🚨 **Critical Issue 2: Soft Delete Cascade Failure**

### **Scenario:**
```
User soft deletes Project (deleted_at = NOW())
```

### **Current Problem: Deleted Records Still Count!**

```php
// Current Query (BROKEN)
Department::find(1)->projects()->sum('budget');
// Returns: ₱50,000,000 (INCLUDES deleted projects!)
// Should be: ₱45,000,000 (excluding ₱5M deleted project)

// Dashboard (BROKEN)
Project::where('department_id', 1)->count();
// Returns: 10 projects (includes 2 deleted)
// Should be: 8 projects

// Disbursements (BROKEN)
ProjectDisbursement::whereHas('project', function($q) use ($deptId) {
    $q->where('department_id', $deptId);
})->sum('amount');
// Returns: ₱45M (includes disbursements from deleted projects!)
// Should be: ₱40M

// Team Members (BROKEN)
$project->teamMembers; // Still returns team members even if project deleted!
// Should be: Empty (or include withTrashed() if intentional)
```

**Result:** ❌ **SOFT DELETED DATA POLLUTES REPORTS**

---

### **Cascading Soft Delete Issue**

```php
// Project soft deleted
$project->delete(); // deleted_at = NOW()

// ❌ PROBLEMS:
// 1. Disbursements NOT soft deleted (orphaned records)
// 2. Milestones NOT soft deleted
// 3. Team members NOT soft deleted
// 4. Approvals NOT soft deleted
// 5. All these records still counted in aggregations!
// 6. Department budget total WRONG
// 7. Dashboard counts WRONG
```

---

## **Solution Architecture**

### **1. Cascading Update Handler**

Create an observer to handle cascading updates:

```php
// app/Observers/ProjectObserver.php
namespace App\Observers;

use App\Models\Project;
use App\Events\ProjectBudgetChanged;
use App\Events\ProjectDeleted;
use Illuminate\Support\Facades\Cache;

class ProjectObserver
{
    /**
     * Handle the Project "updating" event.
     */
    public function updating(Project $project): void
    {
        // Detect budget change
        if ($project->isDirty('budget')) {
            $oldBudget = $project->getOriginal('budget');
            $newBudget = $project->budget;
            $difference = $newBudget - $oldBudget;

            // Validate budget change
            $this->validateBudgetChange($project, $oldBudget, $newBudget);

            // Store for event dispatch after save
            $project->budgetChangeData = [
                'old_budget' => $oldBudget,
                'new_budget' => $newBudget,
                'difference' => $difference,
            ];
        }

        // Detect status change
        if ($project->isDirty('approval_status')) {
            $project->statusChangeData = [
                'old_status' => $project->getOriginal('approval_status'),
                'new_status' => $project->approval_status,
            ];
        }
    }

    /**
     * Handle the Project "updated" event.
     */
    public function updated(Project $project): void
    {
        // Handle budget change cascade
        if (isset($project->budgetChangeData)) {
            event(new ProjectBudgetChanged($project, $project->budgetChangeData));
            $this->invalidateBudgetCaches($project);
            $this->recalculateDepartmentBudget($project);
            $this->notifyBudgetChange($project, $project->budgetChangeData);
        }

        // Handle status change cascade
        if (isset($project->statusChangeData)) {
            $this->invalidateStatusCaches($project);
        }

        // Always invalidate project-specific caches
        $this->invalidateProjectCaches($project);
    }

    /**
     * Handle the Project "deleting" event (soft delete).
     */
    public function deleting(Project $project): void
    {
        if ($project->isForceDeleting()) {
            // Hard delete - cascade hard delete to children
            $this->cascadeHardDelete($project);
        } else {
            // Soft delete - cascade soft delete to children
            $this->cascadeSoftDelete($project);
        }
    }

    /**
     * Handle the Project "deleted" event (soft delete).
     */
    public function deleted(Project $project): void
    {
        // Invalidate all caches
        $this->invalidateAllCaches($project);

        // Recalculate department totals (excluding this project now)
        $this->recalculateDepartmentBudget($project);

        // Dispatch event
        event(new ProjectDeleted($project));

        // Notify stakeholders
        $this->notifyProjectDeletion($project);
    }

    /**
     * Validate budget change
     */
    private function validateBudgetChange(Project $project, float $oldBudget, float $newBudget): void
    {
        // Check if total disbursed exceeds new budget
        $totalDisbursed = $project->disbursements()
            ->where('status', 'completed')
            ->sum('amount');

        if ($totalDisbursed > $newBudget) {
            throw new \Exception(
                "Cannot reduce budget to ₱" . number_format($newBudget, 2) . ". " .
                "Already disbursed ₱" . number_format($totalDisbursed, 2) . ". " .
                "New budget must be at least ₱" . number_format($totalDisbursed, 2) . "."
            );
        }

        // Check department budget cap
        $department = $project->department;
        if ($department && $department->budget_cap) {
            $deptTotal = $department->projects()
                ->where('id', '!=', $project->id)
                ->sum('budget');
            $newDeptTotal = $deptTotal + $newBudget;

            if ($newDeptTotal > $department->budget_cap) {
                throw new \Exception(
                    "Budget increase would exceed department cap. " .
                    "Department cap: ₱" . number_format($department->budget_cap, 2) . ". " .
                    "Current total: ₱" . number_format($deptTotal, 2) . ". " .
                    "New total would be: ₱" . number_format($newDeptTotal, 2) . "."
                );
            }
        }

        // For significant changes (>20%), require re-approval if already approved
        $percentChange = abs(($newBudget - $oldBudget) / $oldBudget * 100);
        if ($percentChange > 20 && $project->isApproved()) {
            // Revert to draft for re-approval
            $project->approval_status = 'draft';
            \Log::warning("Project #{$project->id} budget changed by {$percentChange}%, reverted to draft for re-approval");
        }
    }

    /**
     * Cascade soft delete to related records
     */
    private function cascadeSoftDelete(Project $project): void
    {
        // Soft delete all related records
        $project->disbursements()->delete();
        $project->milestones()->delete();
        $project->approvals()->delete();
        $project->images()->delete();
        $project->fundingDistributions()->delete();

        // Remove team member assignments (no soft delete on pivot table)
        $project->teamMembers()->detach();

        \Log::info("Cascaded soft delete for project #{$project->id}");
    }

    /**
     * Cascade hard delete to related records
     */
    private function cascadeHardDelete(Project $project): void
    {
        // Hard delete related records
        $project->disbursements()->forceDelete();
        $project->milestones()->forceDelete();
        $project->approvals()->forceDelete();
        $project->images()->forceDelete();
        $project->fundingDistributions()->forceDelete();
        $project->auditLogs()->forceDelete();

        \Log::info("Cascaded hard delete for project #{$project->id}");
    }

    /**
     * Recalculate department budget total
     */
    private function recalculateDepartmentBudget(Project $project): void
    {
        if ($project->department_id) {
            $department = $project->department;

            // Recalculate totals (automatically excludes soft-deleted projects)
            $totalBudget = $department->projects()->sum('budget');
            $totalDisbursed = DB::table('project_disbursements')
                ->join('projects', 'project_disbursements.project_id', '=', 'projects.id')
                ->where('projects.department_id', $department->id)
                ->whereNull('projects.deleted_at') // Exclude soft-deleted projects
                ->where('project_disbursements.status', 'completed')
                ->sum('project_disbursements.amount');

            // Update department cached totals (if you have this)
            Cache::put("department.{$department->id}.total_budget", $totalBudget, now()->addHours(24));
            Cache::put("department.{$department->id}.total_disbursed", $totalDisbursed, now()->addHours(24));

            \Log::info("Recalculated department #{$department->id} budget: ₱{$totalBudget}");
        }
    }

    /**
     * Invalidate budget-related caches
     */
    private function invalidateBudgetCaches(Project $project): void
    {
        Cache::forget("project.{$project->id}.budget");
        Cache::forget("project.{$project->id}.total_disbursed");
        Cache::forget("project.{$project->id}.remaining_budget");
        Cache::forget("project.{$project->id}.utilization_rate");

        if ($project->department_id) {
            Cache::forget("department.{$project->department_id}.total_budget");
            Cache::forget("department.{$project->department_id}.total_disbursed");
            Cache::forget("department.{$project->department_id}.projects");
        }

        // Invalidate dashboard caches
        Cache::tags(['dashboard', 'budgets'])->flush();

        \Log::info("Invalidated budget caches for project #{$project->id}");
    }

    /**
     * Invalidate status-related caches
     */
    private function invalidateStatusCaches(Project $project): void
    {
        Cache::forget("project.{$project->id}.status");
        Cache::tags(['dashboard', 'approvals', 'projects'])->flush();
    }

    /**
     * Invalidate all project caches
     */
    private function invalidateProjectCaches(Project $project): void
    {
        Cache::tags(["project.{$project->id}"])->flush();
    }

    /**
     * Invalidate all caches on delete
     */
    private function invalidateAllCaches(Project $project): void
    {
        $this->invalidateBudgetCaches($project);
        $this->invalidateStatusCaches($project);
        $this->invalidateProjectCaches($project);

        // Also invalidate department-level caches
        if ($project->department_id) {
            Cache::tags(["department.{$project->department_id}"])->flush();
        }
    }

    /**
     * Notify stakeholders of budget change
     */
    private function notifyBudgetChange(Project $project, array $changeData): void
    {
        $difference = $changeData['difference'];
        $percentChange = abs($difference / $changeData['old_budget'] * 100);

        // Notify if significant change (>10%)
        if ($percentChange > 10) {
            // Notify finance officer
            $financeOfficers = User::whereHas('role', fn($q) =>
                $q->where('name', 'LIKE', '%finance%')
            )->get();

            foreach ($financeOfficers as $officer) {
                $officer->notify(new \App\Notifications\ProjectBudgetChangedNotification(
                    $project,
                    $changeData['old_budget'],
                    $changeData['new_budget'],
                    $difference
                ));
            }

            // Notify department head
            if ($project->department) {
                $deptHead = $project->department->users()
                    ->whereHas('role', fn($q) => $q->where('name', 'LIKE', '%head%'))
                    ->first();

                if ($deptHead) {
                    $deptHead->notify(new \App\Notifications\ProjectBudgetChangedNotification(
                        $project,
                        $changeData['old_budget'],
                        $changeData['new_budget'],
                        $difference
                    ));
                }
            }

            \Log::info("Notified stakeholders of budget change for project #{$project->id}");
        }
    }

    /**
     * Notify stakeholders of project deletion
     */
    private function notifyProjectDeletion(Project $project): void
    {
        // Notify team members
        foreach ($project->teamMembers as $member) {
            $member->notify(new \App\Notifications\ProjectDeletedNotification($project));
        }

        // Notify department head
        if ($project->department) {
            $deptHead = $project->department->users()
                ->whereHas('role', fn($q) => $q->where('name', 'LIKE', '%head%'))
                ->first();

            if ($deptHead) {
                $deptHead->notify(new \App\Notifications\ProjectDeletedNotification($project));
            }
        }
    }
}
```

---

### **2. Register Observer**

```php
// app/Providers/AppServiceProvider.php
use App\Models\Project;
use App\Observers\ProjectObserver;

public function boot(): void
{
    Project::observe(ProjectObserver::class);
}
```

---

### **3. Fix Soft Delete Query Scopes**

**Problem:** Current queries include soft-deleted records

```php
// BEFORE (BROKEN)
class Dashboard
{
    public function getDepartmentBudget($deptId)
    {
        // ❌ Includes soft-deleted projects
        return Project::where('department_id', $deptId)->sum('budget');
    }
}

// AFTER (FIXED)
class Dashboard
{
    public function getDepartmentBudget($deptId)
    {
        // ✅ Automatically excludes soft-deleted (Laravel default)
        return Project::where('department_id', $deptId)->sum('budget');
    }

    public function getDepartmentBudgetIncludingDeleted($deptId)
    {
        // ✅ Explicitly include if needed
        return Project::withTrashed()
            ->where('department_id', $deptId)
            ->sum('budget');
    }
}
```

**Global Query Scope for Relations:**

```php
// app/Models/Project.php
public function disbursements(): HasMany
{
    // ✅ This automatically excludes soft-deleted disbursements (if using SoftDeletes)
    return $this->hasMany(ProjectDisbursement::class)
        ->orderBy('disbursement_date', 'desc');
}

// If you need to include trashed in specific cases:
public function disbursementsIncludingTrashed(): HasMany
{
    return $this->hasMany(ProjectDisbursement::class)
        ->withTrashed()
        ->orderBy('disbursement_date', 'desc');
}
```

---

### **4. Transaction Wrapper for Critical Updates**

```php
// app/Services/ProjectUpdateService.php
namespace App\Services;

use App\Models\Project;
use Illuminate\Support\Facades\DB;

class ProjectUpdateService
{
    public function updateBudget(Project $project, float $newBudget, string $reason): Project
    {
        return DB::transaction(function () use ($project, $newBudget, $reason) {
            $oldBudget = $project->budget;

            // 1. Update project budget
            $project->update(['budget' => $newBudget]);

            // 2. Create change log
            $project->auditLogs()->create([
                'action' => 'budget_updated',
                'old_values' => json_encode(['budget' => $oldBudget]),
                'new_values' => json_encode(['budget' => $newBudget]),
                'user_id' => auth()->id(),
                'ip_address' => request()->ip(),
                'reason' => $reason,
            ]);

            // 3. Update related progress reports (recalculate utilization %)
            $project->progressReports()->update([
                'budget_utilization' => DB::raw('(total_disbursed / ' . $newBudget . ') * 100')
            ]);

            // 4. Check if any disbursements now violate budget
            $totalDisbursed = $project->disbursements()
                ->where('status', 'completed')
                ->sum('amount');

            if ($totalDisbursed > $newBudget) {
                // Flag for review
                $project->update(['requires_budget_review' => true]);
            }

            return $project->fresh();
        });
    }

    public function softDeleteWithCascade(Project $project): bool
    {
        return DB::transaction(function () use ($project) {
            // Archive project state before deletion
            $this->archiveProjectState($project);

            // Soft delete project (observer handles cascading)
            $project->delete();

            return true;
        });
    }

    private function archiveProjectState(Project $project): void
    {
        // Create snapshot before deletion
        DB::table('project_deletion_snapshots')->insert([
            'project_id' => $project->id,
            'project_data' => json_encode($project->toArray()),
            'team_members' => json_encode($project->teamMembers->pluck('id')),
            'disbursements_total' => $project->total_disbursed,
            'milestones_count' => $project->milestones()->count(),
            'deleted_by' => auth()->id(),
            'deleted_at' => now(),
        ]);
    }
}
```

---

### **5. Cache Invalidation Strategy**

```php
// config/cache.php - Add tagged cache support
'default' => env('CACHE_DRIVER', 'redis'), // Redis supports tags

// app/Services/CacheInvalidationService.php
namespace App\Services;

use Illuminate\Support\Facades\Cache;

class CacheInvalidationService
{
    /**
     * Invalidate all caches related to a project
     */
    public function invalidateProject(int $projectId): void
    {
        // Project-specific caches
        Cache::tags(["project.{$projectId}"])->flush();

        // Related list caches
        Cache::forget("project.{$projectId}.disbursements");
        Cache::forget("project.{$projectId}.milestones");
        Cache::forget("project.{$projectId}.team_members");
        Cache::forget("project.{$projectId}.approvals");
    }

    /**
     * Invalidate department caches
     */
    public function invalidateDepartment(int $deptId): void
    {
        Cache::tags(["department.{$deptId}"])->flush();
        Cache::forget("department.{$deptId}.projects");
        Cache::forget("department.{$deptId}.budget_total");
        Cache::forget("department.{$deptId}.disbursed_total");
    }

    /**
     * Invalidate dashboard caches
     */
    public function invalidateDashboard(): void
    {
        Cache::tags(['dashboard'])->flush();
        Cache::forget('dashboard.overview');
        Cache::forget('dashboard.budget_allocation');
        Cache::forget('dashboard.project_status_distribution');
    }

    /**
     * Smart invalidation: Only invalidate affected caches
     */
    public function invalidateBudgetChange(Project $project): void
    {
        // Invalidate project caches
        $this->invalidateProject($project->id);

        // Invalidate department caches
        if ($project->department_id) {
            $this->invalidateDepartment($project->department_id);
        }

        // Invalidate dashboard budget caches only
        Cache::tags(['dashboard', 'budgets'])->flush();

        // DON'T invalidate unrelated caches (e.g., user lists, news, etc.)
    }
}
```

---

## **Database Migrations for Support Tables**

```php
// Migration: create_project_deletion_snapshots_table.php
Schema::create('project_deletion_snapshots', function (Blueprint $table) {
    $table->id();
    $table->foreignId('project_id'); // Not constrained (project will be deleted)
    $table->json('project_data');
    $table->json('team_members')->nullable();
    $table->decimal('disbursements_total', 15, 2)->default(0);
    $table->integer('milestones_count')->default(0);
    $table->foreignId('deleted_by')->constrained('users');
    $table->timestamp('deleted_at');
    $table->timestamps();

    $table->index('project_id');
    $table->index('deleted_by');
    $table->index('deleted_at');
});
```

---

## **Testing Scenarios**

### Test 1: Budget Update Cascade

```php
public function test_budget_update_cascades_correctly()
{
    $project = Project::factory()->create(['budget' => 5000000]);
    $project->disbursements()->create(['amount' => 3000000, 'status' => 'completed']);

    // Update budget
    $project->update(['budget' => 8000000]);

    // Assert cache invalidated
    $this->assertNull(Cache::get("project.{$project->id}.budget"));

    // Assert department total recalculated
    $deptTotal = Project::where('department_id', $project->department_id)->sum('budget');
    $this->assertEquals(8000000, $deptTotal);

    // Assert utilization rate recalculated
    $this->assertEquals(37.5, $project->fresh()->utilization_rate); // 3M / 8M = 37.5%
}
```

### Test 2: Soft Delete Excludes from Aggregations

```php
public function test_soft_deleted_projects_excluded_from_totals()
{
    $dept = Department::factory()->create();
    $project1 = Project::factory()->create(['department_id' => $dept->id, 'budget' => 5000000]);
    $project2 = Project::factory()->create(['department_id' => $dept->id, 'budget' => 3000000]);

    // Total before deletion
    $this->assertEquals(8000000, $dept->projects()->sum('budget'));

    // Soft delete project2
    $project2->delete();

    // Total after deletion (should exclude project2)
    $this->assertEquals(5000000, $dept->projects()->sum('budget'));

    // Verify project2 is soft deleted
    $this->assertSoftDeleted($project2);

    // Verify withTrashed includes it
    $this->assertEquals(8000000, $dept->projects()->withTrashed()->sum('budget'));
}
```

### Test 3: Cascade Soft Delete to Children

```php
public function test_project_soft_delete_cascades_to_children()
{
    $project = Project::factory()->create();
    $disbursement = $project->disbursements()->create(['amount' => 1000000]);
    $milestone = $project->milestones()->create(['title' => 'Test Milestone']);

    // Soft delete project
    $project->delete();

    // Assert children are also soft deleted
    $this->assertSoftDeleted($disbursement);
    $this->assertSoftDeleted($milestone);

    // Verify counts exclude deleted
    $this->assertEquals(0, ProjectDisbursement::where('project_id', $project->id)->count());
    $this->assertEquals(0, ProjectMilestone::where('project_id', $project->id)->count());
}
```

---

## **Summary**

| Issue | Before | After |
|-------|--------|-------|
| Budget change | Updates project only | ✅ Cascades to dept totals, invalidates caches, notifies stakeholders |
| Soft delete | Project deleted, children orphaned | ✅ Cascades to all children, recalculates totals |
| Dashboard totals | Includes deleted projects | ✅ Excludes soft-deleted automatically |
| Cache invalidation | Manual, often forgotten | ✅ Automatic via observer |
| Notifications | None | ✅ Smart notifications for significant changes |
| Transactions | None | ✅ All-or-nothing updates |
| Audit trail | Basic | ✅ Comprehensive with reasons |

**Lines of Code:** ~600 (Observer + Services)

---

**Last Updated:** 2026-01-29
**Status:** 📋 Design Complete - Ready for Implementation
