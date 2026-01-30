<?php

namespace App\Interfaces;

use App\Models\Project;
use App\Models\User;

interface ProjectApprovalRepositoryInterface
{
    /**
     * Submit a project for approval
     */
    public function submitForApproval(Project $project, User $user): Project;

    /**
     * Approve a project at a specific level
     */
    public function approve(Project $project, User $user, string $level, ?string $comments = null): Project;

    /**
     * Reject a project at a specific level
     */
    public function reject(Project $project, User $user, string $level, string $comments, ?string $reason = null): Project;

    /**
     * Request changes for a project
     */
    public function requestChanges(Project $project, User $user, string $level, string $comments): Project;

    /**
     * Revoke an approval (undo within time window)
     */
    public function revokeApproval(Project $project, int $approvalId, User $user, string $reason): Project;

    /**
     * Get projects pending approval for a user based on their role
     */
    public function getPendingApprovalForUser(User $user, int $perPage = 15);

    /**
     * Get approval history for a project
     */
    public function getApprovalHistory(int $projectId): array;

    /**
     * Get the approval level a user can approve based on their role
     */
    public function getUserApprovalLevel(User $user): ?string;

    /**
     * Check if a user can approve at the current project level
     */
    public function canUserApprove(User $user, Project $project): bool;

    /**
     * Get all projects with a specific approval status
     */
    public function getProjectsByApprovalStatus(string $status, int $perPage = 15, $user = null);

    /**
     * Get approval statistics
     */
    public function getApprovalStatistics(): array;
}
