<?php

namespace App\Repositories;

use App\Interfaces\ProjectApprovalRepositoryInterface;
use App\Models\AuditLog;
use App\Models\Project;
use App\Models\ProjectApproval;
use App\Models\User;
use App\Notifications\ProjectApprovalNotification;
use App\Notifications\ProjectApprovedNotification;
use App\Notifications\ProjectChangesRequestedNotification;
use App\Notifications\ProjectRejectedNotification;
use Illuminate\Support\Facades\DB;

class ProjectApprovalRepository implements ProjectApprovalRepositoryInterface
{
    /**
     * Mapping of roles to approval levels (RA 7160 LGU Structure)
     */
    private array $roleToLevelMap = [
        'barangay_officer' => 'barangay',
        'barangay_development_council' => 'barangay',
        'municipal_officer' => 'municipal',
        'municipal_planning_officer' => 'municipal', // MPDO
        'provincial_officer' => 'provincial',
        'provincial_planning_officer' => 'provincial', // PPDO
        'governor' => 'governor',
        'admin' => 'governor', // Admin can approve at governor level
        'super_admin' => 'governor',
    ];

    /**
     * Approval flow: current status => next status (RA 7160 Chain)
     * Barangay → Municipal (MPDO) → Provincial (PPDO) → Governor
     */
    private array $approvalFlow = [
        'draft' => 'pending_barangay',
        'pending_barangay' => 'pending_municipal',
        'pending_municipal' => 'pending_provincial',
        'pending_provincial' => 'pending_governor',
        'pending_governor' => 'approved',
    ];

    /**
     * Level to pending status mapping (RA 7160)
     */
    private array $levelToStatusMap = [
        'barangay' => 'pending_barangay',
        'municipal' => 'pending_municipal',
        'provincial' => 'pending_provincial',
        'governor' => 'pending_governor',
    ];

    public function submitForApproval(Project $project, User $user): Project
    {
        return DB::transaction(function () use ($project, $user) {
            // Prevent re-submission of projects already in approval workflow
            if ($project->isPendingApproval()) {
                throw new \Exception(
                    'Project is already in the approval workflow. ' .
                    'Current status: ' . $project->approval_status_display . '. ' .
                    'Cannot re-submit until approval process completes.'
                );
            }

            $fromStatus = $project->approval_status;
            $toStatus = 'pending_barangay'; // RA 7160: Start at Barangay level

            // Update project
            $project->update([
                'approval_status' => $toStatus,
                'submitted_by' => $user->id,
                'submitted_at' => now(),
            ]);

            // Create approval record
            ProjectApproval::create([
                'project_id' => $project->id,
                'user_id' => $user->id,
                'action' => 'submitted',
                'level' => 'barangay', // RA 7160: Barangay Development Council
                'from_status' => $fromStatus,
                'to_status' => $toStatus,
                'action_taken_at' => now(),
            ]);

            // Create audit log
            $this->createAuditLog($project, $user, 'submitted', $fromStatus, $toStatus);

            // Send notification to barangay officers
            $this->notifyApproversAtLevel($project, 'barangay');

            return $project->fresh(['department', 'projectType', 'projectStatus', 'approvals']);
        });
    }

    public function approve(Project $project, User $user, string $level, ?string $comments = null): Project
    {
        return DB::transaction(function () use ($project, $user, $level, $comments) {
            // Apply pessimistic locking to prevent race conditions
            $project = Project::where('id', $project->id)->lockForUpdate()->first();

            if (!$project) {
                throw new \Exception('Project not found or locked by another transaction');
            }

            $fromStatus = $project->approval_status;
            $toStatus = $this->getNextApprovalStatus($fromStatus);
            $isFinalApproval = $toStatus === 'approved';

            // Verify the project is still in the expected state
            if (!$project->isPendingApproval()) {
                throw new \Exception('Project is no longer pending approval');
            }

            // Verify the user can still approve at this level
            if ($project->getCurrentPendingLevel() !== $level) {
                throw new \Exception("Project is not pending at {$level} level");
            }

            // Update project
            $project->update([
                'approval_status' => $toStatus,
            ]);

            // Create approval record
            ProjectApproval::create([
                'project_id' => $project->id,
                'user_id' => $user->id,
                'action' => 'approved',
                'comments' => $comments,
                'level' => $level,
                'from_status' => $fromStatus,
                'to_status' => $toStatus,
                'action_taken_at' => now(),
            ]);

            // Create audit log
            $this->createAuditLog($project, $user, 'approved', $fromStatus, $toStatus, $comments);

            // Notify submitter about approval
            $this->notifySubmitter($project, $user, $level, $isFinalApproval);

            // If not final approval, notify next level approvers
            if (! $isFinalApproval) {
                $nextLevel = $this->getNextLevel($level);
                if ($nextLevel) {
                    $this->notifyApproversAtLevel($project, $nextLevel);
                }
            }

            return $project->fresh(['department', 'projectType', 'projectStatus', 'approvals']);
        });
    }

    public function reject(Project $project, User $user, string $level, string $comments, ?string $reason = null): Project
    {
        return DB::transaction(function () use ($project, $user, $level, $comments, $reason) {
            // Apply pessimistic locking to prevent race conditions
            $project = Project::where('id', $project->id)->lockForUpdate()->first();

            if (!$project) {
                throw new \Exception('Project not found or locked by another transaction');
            }

            $fromStatus = $project->approval_status;
            $toStatus = 'rejected';

            // Verify the project is still in the expected state
            if (!$project->isPendingApproval()) {
                throw new \Exception('Project is no longer pending approval');
            }

            // Verify the user can still reject at this level
            if ($project->getCurrentPendingLevel() !== $level) {
                throw new \Exception("Project is not pending at {$level} level");
            }

            // Update project
            $project->update([
                'approval_status' => $toStatus,
            ]);

            // Create approval record
            ProjectApproval::create([
                'project_id' => $project->id,
                'user_id' => $user->id,
                'action' => 'rejected',
                'comments' => $comments,
                'reason' => $reason,
                'level' => $level,
                'from_status' => $fromStatus,
                'to_status' => $toStatus,
                'action_taken_at' => now(),
            ]);

            // Create audit log
            $this->createAuditLog($project, $user, 'rejected', $fromStatus, $toStatus, $comments);

            // Notify submitter about rejection
            $this->notifySubmitterRejected($project, $user, $level, $comments, $reason);

            return $project->fresh(['department', 'projectType', 'projectStatus', 'approvals']);
        });
    }

    public function requestChanges(Project $project, User $user, string $level, string $comments): Project
    {
        return DB::transaction(function () use ($project, $user, $level, $comments) {
            // Apply pessimistic locking to prevent race conditions
            $project = Project::where('id', $project->id)->lockForUpdate()->first();

            if (!$project) {
                throw new \Exception('Project not found or locked by another transaction');
            }

            $fromStatus = $project->approval_status;
            $toStatus = 'draft'; // Send back to draft for revisions

            // Verify the project is still in the expected state
            if (!$project->isPendingApproval()) {
                throw new \Exception('Project is no longer pending approval');
            }

            // Verify the user can still request changes at this level
            if ($project->getCurrentPendingLevel() !== $level) {
                throw new \Exception("Project is not pending at {$level} level");
            }

            // Update project
            $project->update([
                'approval_status' => $toStatus,
            ]);

            // Create approval record
            ProjectApproval::create([
                'project_id' => $project->id,
                'user_id' => $user->id,
                'action' => 'requested_changes',
                'comments' => $comments,
                'level' => $level,
                'from_status' => $fromStatus,
                'to_status' => $toStatus,
                'action_taken_at' => now(),
            ]);

            // Create audit log
            $this->createAuditLog($project, $user, 'requested_changes', $fromStatus, $toStatus, $comments);

            // Notify submitter about requested changes
            $this->notifySubmitterChangesRequested($project, $user, $level, $comments);

            return $project->fresh(['department', 'projectType', 'projectStatus', 'approvals']);
        });
    }

    public function revokeApproval(Project $project, int $approvalId, User $user, string $reason): Project
    {
        return DB::transaction(function () use ($project, $approvalId, $user, $reason) {
            // Apply pessimistic locking
            $project = Project::where('id', $project->id)->lockForUpdate()->first();

            if (!$project) {
                throw new \Exception('Project not found or locked by another transaction');
            }

            // Find the approval record
            $approval = ProjectApproval::where('id', $approvalId)
                ->where('project_id', $project->id)
                ->where('action', 'approved')
                ->where('is_revoked', false)
                ->first();

            if (!$approval) {
                throw new \Exception('Approval record not found or already revoked');
            }

            // Only the approver or admin can revoke
            $isAdmin = $user->role && str_contains(strtolower($user->role->name), 'admin');
            if ($approval->user_id !== $user->id && !$isAdmin) {
                throw new \Exception('You can only revoke your own approvals');
            }

            // Check time window (24 hours by default, configurable)
            $revocationWindowHours = config('app.approval_revocation_window_hours', 24);
            $approvalAge = $approval->action_taken_at->diffInHours(now());
            if ($approvalAge > $revocationWindowHours && !$isAdmin) {
                throw new \Exception("Approval can only be revoked within {$revocationWindowHours} hours. This approval was made {$approvalAge} hours ago.");
            }

            // Can only revoke if the project is still at the next level or hasn't moved too far
            $canRevoke = $this->canRevokeApproval($project, $approval);
            if (!$canRevoke) {
                throw new \Exception('Cannot revoke this approval as the project has progressed too far in the workflow');
            }

            $fromStatus = $project->approval_status;
            $toStatus = $approval->from_status; // Revert to the status before this approval

            // Mark approval as revoked
            $approval->update([
                'is_revoked' => true,
                'revoked_by' => $user->id,
                'revoked_at' => now(),
                'revocation_reason' => $reason,
            ]);

            // Revert project status
            $project->update([
                'approval_status' => $toStatus,
            ]);

            // Create audit log
            $this->createAuditLog($project, $user, 'revoked_approval', $fromStatus, $toStatus, $reason);

            // Notify relevant parties
            $this->notifyApprovalRevoked($project, $approval, $user, $reason);

            return $project->fresh(['department', 'projectType', 'projectStatus', 'approvals']);
        });
    }

    /**
     * Check if an approval can be revoked based on project's current state (RA 7160)
     */
    private function canRevokeApproval(Project $project, ProjectApproval $approval): bool
    {
        // Map of what statuses allow revoking approvals from each level
        $allowedRevocations = [
            'barangay' => ['pending_municipal', 'pending_barangay'],
            'municipal' => ['pending_provincial', 'pending_municipal'],
            'provincial' => ['pending_governor', 'pending_provincial'],
            'governor' => ['approved', 'pending_governor'],
        ];

        $level = $approval->level;
        $currentStatus = $project->approval_status;

        if (!isset($allowedRevocations[$level])) {
            return false;
        }

        return in_array($currentStatus, $allowedRevocations[$level]);
    }

    /**
     * Notify relevant parties about approval revocation
     */
    private function notifyApprovalRevoked(Project $project, ProjectApproval $approval, User $revoker, string $reason): void
    {
        try {
            // Notify the project submitter
            if ($project->submitter) {
                \Log::info("Approval revoked for project {$project->id} by user {$revoker->id}. Reason: {$reason}");
            }

            // Notify approvers at the reverted level
            $level = $project->getCurrentPendingLevel();
            if ($level) {
                $this->notifyApproversAtLevel($project, $level);
            }
        } catch (\Exception $e) {
            \Log::error('Failed to send revocation notifications: '.$e->getMessage());
        }
    }

    public function getPendingApprovalForUser(User $user, int $perPage = 15)
    {
        $level = $this->getUserApprovalLevel($user);

        if (! $level) {
            return Project::whereRaw('1 = 0')->paginate($perPage); // Empty result
        }

        $pendingStatus = $this->levelToStatusMap[$level] ?? null;

        if (! $pendingStatus) {
            return Project::whereRaw('1 = 0')->paginate($perPage);
        }

        $query = Project::where('approval_status', $pendingStatus)
            ->with(['department', 'projectType', 'projectStatus', 'submitter', 'municipality']);

        // RA 7160 COMPLIANCE: Filter by territorial jurisdiction
        // Municipal and Barangay officers can only see projects from their municipality
        if ($level === 'municipal' || $level === 'barangay') {
            if (!$user->municipality_id) {
                // If user has no municipality assigned, return empty result
                return Project::whereRaw('1 = 0')->paginate($perPage);
            }
            // Filter projects to only those within the user's municipality
            $query->where('municipality_id', $user->municipality_id);
        }

        // Provincial (PPDO) and Governor levels see all projects in the province
        // No additional filtering needed - they have province-wide jurisdiction

        return $query->orderBy('submitted_at', 'asc')->paginate($perPage);
    }

    public function getApprovalHistory(int $projectId): array
    {
        $project = Project::with(['approvals.user', 'department', 'submitter'])->find($projectId);

        if (! $project) {
            return [];
        }

        $history = $project->approvals->map(function ($approval) {
            return [
                'id' => $approval->id,
                'level' => $approval->level,
                'level_display' => $approval->level_display,
                'action' => $approval->action,
                'action_display' => $approval->action_display,
                'by' => $approval->user->full_name ?? $approval->user->username,
                'user_id' => $approval->user_id,
                'comments' => $approval->comments,
                'reason' => $approval->reason,
                'from_status' => $approval->from_status,
                'to_status' => $approval->to_status,
                'timestamp' => $approval->action_taken_at?->toIso8601String(),
            ];
        })->toArray();

        return [
            'project_id' => $project->id,
            'project_title' => $project->title,
            'current_status' => $project->approval_status,
            'current_status_display' => $project->approval_status_display,
            'submitted_by' => $project->submitter ? [
                'id' => $project->submitter->id,
                'name' => $project->submitter->full_name ?? $project->submitter->username,
            ] : null,
            'submitted_at' => $project->submitted_at?->toIso8601String(),
            'history' => $history,
        ];
    }

    public function getUserApprovalLevel(User $user): ?string
    {
        if (! $user->role) {
            return null;
        }

        $roleName = strtolower($user->role->name);

        // Check direct mapping
        if (isset($this->roleToLevelMap[$roleName])) {
            return $this->roleToLevelMap[$roleName];
        }

        // Check if role contains key terms (RA 7160 hierarchy)
        if (str_contains($roleName, 'admin') || str_contains($roleName, 'super')) {
            return 'governor';
        }

        if (str_contains($roleName, 'governor')) {
            return 'governor';
        }

        if (str_contains($roleName, 'provincial') || str_contains($roleName, 'ppdo')) {
            return 'provincial';
        }

        if (str_contains($roleName, 'municipal') || str_contains($roleName, 'city') || str_contains($roleName, 'mpdo')) {
            return 'municipal';
        }

        if (str_contains($roleName, 'barangay') || str_contains($roleName, 'bdc')) {
            return 'barangay';
        }

        return null;
    }

    public function canUserApprove(User $user, Project $project): bool
    {
        $userLevel = $this->getUserApprovalLevel($user);
        $projectLevel = $project->getCurrentPendingLevel();

        if (! $userLevel || ! $projectLevel) {
            return false;
        }

        // SECURITY FIX: Admins must follow proper approval workflow
        // They cannot skip levels to maintain audit trail integrity
        // Admins are mapped to 'regional' level and must wait for municipal/provincial approvals
        if ($userLevel !== $projectLevel) {
            return false;
        }

        // RA 7160 COMPLIANCE: Territorial Jurisdiction Validation
        // Municipal and Barangay officers can only approve projects within their municipality
        if ($userLevel === 'municipal') {
            // Municipal Planning and Development Officers (MPDO) can only approve
            // projects within their municipality (RA 7160 Section 476)
            if (!$user->municipality_id || !$project->municipality_id) {
                return false; // Both user and project must have municipality assigned
            }
            return $user->municipality_id === $project->municipality_id;
        }

        if ($userLevel === 'barangay') {
            // Barangay Development Council (BDC) officers can only approve
            // projects within their municipality (RA 7160 Article X)
            if (!$user->municipality_id || !$project->municipality_id) {
                return false; // Both user and project must have municipality assigned
            }
            return $user->municipality_id === $project->municipality_id;
            // Note: Future enhancement - add barangay-level validation when barangay_id is implemented
        }

        // Provincial (PPDO) and Governor levels have province-wide jurisdiction
        // They can approve projects from any municipality within the province
        return true;
    }

    public function getProjectsByApprovalStatus(string $status, int $perPage = 15, $user = null)
    {
        $query = Project::where('approval_status', $status)
            ->with(['department', 'projectType', 'projectStatus', 'submitter', 'municipality'])
            ->forUser($user); // Apply RA 7160 territorial jurisdiction filtering

        return $query->orderBy('updated_at', 'desc')->paginate($perPage);
    }

    public function getApprovalStatistics(): array
    {
        $stats = [
            'total_projects' => Project::count(),
            'by_status' => [
                'draft' => Project::where('approval_status', 'draft')->count(),
                'pending_barangay' => Project::where('approval_status', 'pending_barangay')->count(),
                'pending_municipal' => Project::where('approval_status', 'pending_municipal')->count(),
                'pending_provincial' => Project::where('approval_status', 'pending_provincial')->count(),
                'pending_governor' => Project::where('approval_status', 'pending_governor')->count(),
                'approved' => Project::where('approval_status', 'approved')->count(),
                'rejected' => Project::where('approval_status', 'rejected')->count(),
            ],
            'total_pending' => Project::pendingApproval()->count(),
            'recent_approvals' => ProjectApproval::where('action', 'approved')
                ->where('action_taken_at', '>=', now()->subDays(30))
                ->count(),
            'recent_rejections' => ProjectApproval::where('action', 'rejected')
                ->where('action_taken_at', '>=', now()->subDays(30))
                ->count(),
            'average_approval_time' => $this->calculateAverageApprovalTime(),
        ];

        return $stats;
    }

    /**
     * Get the next approval status based on current status
     */
    private function getNextApprovalStatus(string $currentStatus): string
    {
        return $this->approvalFlow[$currentStatus] ?? $currentStatus;
    }

    /**
     * Get the next approval level (RA 7160 Chain)
     */
    private function getNextLevel(string $currentLevel): ?string
    {
        $levelFlow = [
            'barangay' => 'municipal',
            'municipal' => 'provincial',
            'provincial' => 'governor',
        ];

        return $levelFlow[$currentLevel] ?? null;
    }

    /**
     * Notify approvers at a specific level (RA 7160 LGU Structure)
     */
    private function notifyApproversAtLevel(Project $project, string $level): void
    {
        try {
            // Find users who can approve at this level
            $approvers = User::whereHas('role', function ($query) use ($level) {
                $query->where(function ($q) use ($level) {
                    // Match exact role names
                    $q->where('name', 'LIKE', "%{$level}%");

                    // Admin and Governor can approve at governor level
                    if ($level === 'governor') {
                        $q->orWhere('name', 'LIKE', '%admin%')
                            ->orWhere('name', 'LIKE', '%governor%');
                    }

                    // Include MPDO for municipal level
                    if ($level === 'municipal') {
                        $q->orWhere('name', 'LIKE', '%mpdo%');
                    }

                    // Include PPDO for provincial level
                    if ($level === 'provincial') {
                        $q->orWhere('name', 'LIKE', '%ppdo%');
                    }

                    // Include BDC for barangay level
                    if ($level === 'barangay') {
                        $q->orWhere('name', 'LIKE', '%bdc%')
                            ->orWhere('name', 'LIKE', '%barangay%');
                    }
                });
            })->where('is_active', true)->get();

            foreach ($approvers as $approver) {
                $approver->notify(new ProjectApprovalNotification($project, $level));
            }
        } catch (\Exception $e) {
            \Log::error('Failed to send approval notifications: '.$e->getMessage());
        }
    }

    /**
     * Notify the project submitter about approval
     */
    private function notifySubmitter(Project $project, User $approver, string $level, bool $isFinalApproval): void
    {
        try {
            if ($project->submitter) {
                $project->submitter->notify(
                    new ProjectApprovedNotification($project, $approver, $level, $isFinalApproval)
                );
            }
        } catch (\Exception $e) {
            \Log::error('Failed to send approval notification to submitter: '.$e->getMessage());
        }
    }

    /**
     * Notify the project submitter about rejection
     */
    private function notifySubmitterRejected(Project $project, User $rejector, string $level, string $comments, ?string $reason): void
    {
        try {
            if ($project->submitter) {
                $project->submitter->notify(
                    new ProjectRejectedNotification($project, $rejector, $level, $comments, $reason)
                );
            }
        } catch (\Exception $e) {
            \Log::error('Failed to send rejection notification to submitter: '.$e->getMessage());
        }
    }

    /**
     * Notify the project submitter about requested changes
     */
    private function notifySubmitterChangesRequested(Project $project, User $reviewer, string $level, string $comments): void
    {
        try {
            if ($project->submitter) {
                $project->submitter->notify(
                    new ProjectChangesRequestedNotification($project, $reviewer, $level, $comments)
                );
            }
        } catch (\Exception $e) {
            \Log::error('Failed to send changes requested notification to submitter: '.$e->getMessage());
        }
    }

    /**
     * Create an audit log entry for approval actions
     */
    private function createAuditLog(
        Project $project,
        User $user,
        string $action,
        string $fromStatus,
        string $toStatus,
        ?string $comments = null
    ): void {
        try {
            AuditLog::create([
                'user_id' => $user->id,
                'action' => "project_approval_{$action}",
                'model_type' => Project::class,
                'model_id' => $project->id,
                'old_values' => json_encode(['approval_status' => $fromStatus]),
                'new_values' => json_encode([
                    'approval_status' => $toStatus,
                    'comments' => $comments,
                ]),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
        } catch (\Exception $e) {
            // Log error but don't fail the transaction
            \Log::error('Failed to create audit log for project approval: '.$e->getMessage());
        }
    }

    /**
     * Calculate average time from submission to final approval
     */
    private function calculateAverageApprovalTime(): ?float
    {
        $approvedProjects = Project::where('approval_status', 'approved')
            ->whereNotNull('submitted_at')
            ->get();

        if ($approvedProjects->isEmpty()) {
            return null;
        }

        $totalDays = 0;
        $count = 0;

        foreach ($approvedProjects as $project) {
            $finalApproval = $project->approvals()
                ->where('action', 'approved')
                ->where('to_status', 'approved')
                ->first();

            if ($finalApproval && $project->submitted_at) {
                $days = $project->submitted_at->diffInDays($finalApproval->action_taken_at);
                $totalDays += $days;
                $count++;
            }
        }

        return $count > 0 ? round($totalDays / $count, 1) : null;
    }
}
