# Approval Workflow Security Fixes

## Overview

This document describes the fixes implemented to address critical security and integrity issues in the project approval workflow.

## Issues Fixed

### 1. Race Condition Prevention ✅

**Problem:** Two officers could approve the same project simultaneously, causing duplicate approvals and potentially skipping approval levels.

**Solution Implemented:**
- **Pessimistic Locking:** Added `lockForUpdate()` to all approval operations
- **Unique Database Constraint:** Added unique index on `(project_id, level, action)`
- **State Validation:** Verify project state hasn't changed during transaction
- **Graceful Error Handling:** Return user-friendly conflict messages

**Code Changes:**
- [ProjectApprovalRepository.php:87-122](../app/Repositories/ProjectApprovalRepository.php#L87-L122) - Added locking in `approve()`
- [ProjectApprovalRepository.php:146-178](../app/Repositories/ProjectApprovalRepository.php#L146-L178) - Added locking in `reject()`
- [ProjectApprovalRepository.php:197-229](../app/Repositories/ProjectApprovalRepository.php#L197-L229) - Added locking in `requestChanges()`
- [Migration](../database/migrations/2026_01_29_050505_add_unique_constraint_to_project_approvals_table.php) - Unique constraint

**Database Schema:**
```sql
ALTER TABLE project_approvals
ADD UNIQUE KEY unique_project_level_action (project_id, level, action);
```

---

### 2. Edit During Approval Prevention ✅

**Problem:** Projects could be modified while in the approval workflow, causing approvers to approve different content than they reviewed.

**Solution Implemented:**
- Block all edits to projects with status `pending_municipal`, `pending_provincial`, or `pending_regional`
- Block all edits to `approved` projects to maintain audit trail integrity
- Only `draft` and `rejected` projects can be edited
- Clear error messages guide users on proper workflow

**Code Changes:**
- [ProjectRepository.php:132-158](../app/Repositories/ProjectRepository.php#L132-L158) - Added validation in `update()`

---

## Technical Implementation Details

### Pessimistic Locking Flow

```php
DB::transaction(function () use ($project, $user, $level, $comments) {
    // 1. Lock the project row for update (blocks other transactions)
    $project = Project::where('id', $project->id)->lockForUpdate()->first();

    // 2. Verify project still exists
    if (!$project) {
        throw new \Exception('Project not found or locked');
    }

    // 3. Verify state hasn't changed
    if (!$project->isPendingApproval()) {
        throw new \Exception('Project is no longer pending approval');
    }

    // 4. Verify correct level
    if ($project->getCurrentPendingLevel() !== $level) {
        throw new \Exception("Project is not pending at {$level} level");
    }

    // 5. Perform update (still locked)
    $project->update(['approval_status' => $toStatus]);

    // 6. Create approval record (unique constraint enforced)
    ProjectApproval::create([...]);

    // 7. Transaction commits, lock released
});
```

### Error Handling

**Conflict Detection (HTTP 409):**
- Duplicate approval attempts (database constraint violation)
- State changed during transaction
- Another officer processed the project

**Validation Errors (HTTP 422):**
- Project not in expected state
- User lacks permission

---

## Testing Guide

### Test Case 1: Race Condition (Simultaneous Approvals)

**Setup:**
1. Create a project in `pending_municipal` status
2. Have two municipal officers logged in simultaneously

**Test Scenario:**
```bash
# Officer A - Terminal 1
curl -X POST http://localhost:8000/api/projects/1/approve \
  -H "Authorization: Bearer {token_officer_a}" \
  -H "Content-Type: application/json"

# Officer B - Terminal 2 (execute immediately after Officer A)
curl -X POST http://localhost:8000/api/projects/1/approve \
  -H "Authorization: Bearer {token_officer_b}" \
  -H "Content-Type: application/json"
```

**Expected Result:**
- Officer A: `200 OK` - "Project approved and moved to next approval level"
- Officer B: `409 Conflict` - "This project has already been approved at this level. Another officer may have processed it simultaneously."
- Database: Only ONE approval record created at municipal level
- Project status: `pending_provincial` (correct next level)

**Verification:**
```sql
SELECT * FROM project_approvals
WHERE project_id = 1 AND level = 'municipal' AND action = 'approved';
-- Should return exactly 1 row

SELECT approval_status FROM projects WHERE id = 1;
-- Should be 'pending_provincial', not 'approved'
```

---

### Test Case 2: Edit During Approval (Pending Status)

**Setup:**
1. Create a project with budget = 100000
2. Submit it for approval (status = `pending_municipal`)

**Test Scenario:**
```bash
# Attempt to update project while pending
curl -X PUT http://localhost:8000/api/projects/1 \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "title": "Modified Title",
    "budget": 10000000
  }'
```

**Expected Result:**
- Status: `500 Internal Server Error`
- Message: "Cannot edit project while it is pending approval. Current status: Pending Municipal Approval. Please wait for approval to complete or request the project to be sent back to draft."
- Database: Project remains unchanged (budget still 100000)

**Verification:**
```sql
SELECT title, budget, approval_status FROM projects WHERE id = 1;
-- budget should still be 100000
-- title should be unchanged
-- approval_status should still be 'pending_municipal'
```

---

### Test Case 3: Edit Approved Project

**Setup:**
1. Create and fully approve a project (status = `approved`)

**Test Scenario:**
```bash
# Attempt to update approved project
curl -X PUT http://localhost:8000/api/projects/1 \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "title": "Modified After Approval"
  }'
```

**Expected Result:**
- Status: `500 Internal Server Error`
- Message: "Cannot edit approved project. Approved projects are locked to maintain audit trail integrity. Please contact an administrator if changes are needed."

---

### Test Case 4: Valid Edit (Draft Status)

**Setup:**
1. Create a project in `draft` status

**Test Scenario:**
```bash
# Update draft project
curl -X PUT http://localhost:8000/api/projects/1 \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "title": "Updated Title",
    "budget": 200000
  }'
```

**Expected Result:**
- Status: `200 OK`
- Project updated successfully
- Changes reflected in database

---

### Test Case 5: Edit After Rejection

**Setup:**
1. Submit a project for approval
2. Have it rejected (status = `rejected`)

**Test Scenario:**
```bash
# Update rejected project
curl -X PUT http://localhost:8000/api/projects/1 \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "description": "Fixed the issues mentioned in rejection"
  }'
```

**Expected Result:**
- Status: `200 OK`
- Project updated successfully (rejected projects can be edited)

---

### Test Case 6: Request Changes Flow

**Setup:**
1. Create project in `pending_provincial` status

**Test Scenario:**
```bash
# Provincial officer requests changes
curl -X POST http://localhost:8000/api/projects/1/request-changes \
  -H "Authorization: Bearer {token_provincial}" \
  -H "Content-Type: application/json" \
  -d '{
    "comments": "Please add more details about implementation timeline"
  }'

# Now project is back to 'draft', submitter can edit
curl -X PUT http://localhost:8000/api/projects/1 \
  -H "Authorization: Bearer {token_submitter}" \
  -H "Content-Type: application/json" \
  -d '{
    "description": "Updated with detailed timeline"
  }'
```

**Expected Result:**
- Request changes: `200 OK`, status → `draft`
- Edit: `200 OK`, changes applied
- Submitter can now re-submit for approval

---

## Error Response Format

### Success Response (200 OK)
```json
{
  "success": true,
  "message": "Project approved and moved to next approval level",
  "data": {
    "id": 1,
    "title": "Project Title",
    "approval_status": "pending_provincial",
    ...
  }
}
```

### Conflict Response (409 Conflict)
```json
{
  "success": false,
  "message": "This project has already been approved at this level. Another officer may have processed it simultaneously."
}
```

### Validation Error (422 Unprocessable Entity)
```json
{
  "success": false,
  "message": "Project is not pending approval"
}
```

### Server Error (500 Internal Server Error)
```json
{
  "message": "Cannot edit project while it is pending approval. Current status: Pending Municipal Approval. Please wait for approval to complete or request the project to be sent back to draft."
}
```

---

## Database Verification Queries

### Check for Duplicate Approvals (Should Return 0)
```sql
SELECT project_id, level, action, COUNT(*) as count
FROM project_approvals
GROUP BY project_id, level, action
HAVING count > 1;
```

### Check Project Approval Flow Integrity
```sql
-- All approved projects should have approvals at all 3 levels
SELECT p.id, p.title, p.approval_status,
       COUNT(DISTINCT CASE WHEN pa.level = 'municipal' THEN 1 END) as municipal_approvals,
       COUNT(DISTINCT CASE WHEN pa.level = 'provincial' THEN 1 END) as provincial_approvals,
       COUNT(DISTINCT CASE WHEN pa.level = 'regional' THEN 1 END) as regional_approvals
FROM projects p
LEFT JOIN project_approvals pa ON p.id = pa.project_id AND pa.action = 'approved'
WHERE p.approval_status = 'approved'
GROUP BY p.id, p.title, p.approval_status
HAVING municipal_approvals = 0 OR provincial_approvals = 0 OR regional_approvals = 0;
-- Should return 0 rows (all approved projects went through full flow)
```

### Audit Trail Verification
```sql
SELECT p.id, p.title, p.approval_status,
       pa.level, pa.action, pa.action_taken_at, u.username as officer
FROM projects p
INNER JOIN project_approvals pa ON p.id = pa.project_id
INNER JOIN users u ON pa.user_id = u.id
WHERE p.id = 1
ORDER BY pa.action_taken_at ASC;
```

---

## Performance Considerations

### Pessimistic Locking Impact

**Lock Duration:**
- Locks held only during transaction (typically < 100ms)
- Minimal impact on concurrent operations
- Only affects same project, different projects unaffected

**Potential Issues:**
- **Deadlock:** Rare, only if multiple transactions lock projects in different orders
- **Timeout:** If transaction takes too long (unlikely with current operations)

**Monitoring:**
```sql
-- Check for lock wait timeouts
SHOW ENGINE INNODB STATUS;

-- Check current locks
SELECT * FROM information_schema.innodb_locks;
```

---

## Rollback Instructions

If issues occur, rollback the changes:

```bash
# Rollback migration (removes unique constraint)
php artisan migrate:rollback --step=1

# Revert code changes
git revert <commit_hash>
```

**Warning:** Rolling back the unique constraint will re-enable race conditions!

---

## Future Enhancements

### Not Yet Implemented

1. **Approval Revocation**
   - Allow officers to undo their approval within a time window
   - Require admin approval for revocations

2. **Officer Reassignment Handling**
   - Delegation mechanism when officer changes role
   - Auto-reassign pending approvals

3. **Approver Absence Management**
   - "Out of Office" status
   - Backup approver assignment
   - SLA timers with escalation

4. **Version Snapshots**
   - Store project state at submission time
   - Show diff between submitted and current state
   - Allow approvers to see what they're approving

5. **Concurrent Editing**
   - Optimistic locking for draft projects
   - Real-time collaboration features
   - Change conflict resolution

---

## Summary of Changes

| File | Lines Changed | Purpose |
|------|---------------|---------|
| ProjectApprovalRepository.php | ~30 lines | Added pessimistic locking & state validation |
| ProjectApprovalService.php | ~60 lines | Added error handling for conflicts |
| ProjectRepository.php | ~20 lines | Block edits during approval |
| Migration (unique constraint) | ~20 lines | Database constraint + cleanup |

**Total:** ~130 lines of code added

---

## Support

For issues or questions:
- Check the error message for specific guidance
- Review audit logs: `GET /api/projects/{id}/audit-logs`
- Review approval history: `GET /api/projects/{id}/approval-history`
- Contact system administrator

---

**Version:** 1.0
**Date:** 2026-01-29
**Status:** ✅ IMPLEMENTED & TESTED
