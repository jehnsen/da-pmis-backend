<?php

namespace App\Services;

use App\Interfaces\ProjectApprovalRepositoryInterface;
use App\Models\Project;
use App\Models\User;

class ProjectApprovalService
{
    public function __construct(
        private readonly ProjectApprovalRepositoryInterface $repository
    ) {}

    /**
     * Submit a project for approval
     */
    public function submitForApproval(int $projectId, User $user): array
    {
        $project = Project::find($projectId);

        if (! $project) {
            return ['success' => false, 'message' => 'Project not found', 'code' => 404];
        }

        if (! $project->isDraft() && $project->approval_status !== 'rejected') {
            return [
                'success' => false,
                'message' => 'Only draft or rejected projects can be submitted for approval',
                'code' => 422,
            ];
        }

        $project = $this->repository->submitForApproval($project, $user);

        return [
            'success' => true,
            'message' => 'Project submitted for approval successfully',
            'project' => $project,
        ];
    }

    /**
     * Approve a project
     */
    public function approve(int $projectId, User $user, ?string $comments = null): array
    {
        $project = Project::find($projectId);

        if (! $project) {
            return ['success' => false, 'message' => 'Project not found', 'code' => 404];
        }

        if (! $project->isPendingApproval()) {
            return [
                'success' => false,
                'message' => 'Project is not pending approval',
                'code' => 422,
            ];
        }

        if (! $this->repository->canUserApprove($user, $project)) {
            return [
                'success' => false,
                'message' => 'You do not have permission to approve this project at the current level',
                'code' => 403,
            ];
        }

        try {
            $level = $this->repository->getUserApprovalLevel($user);
            $project = $this->repository->approve($project, $user, $level, $comments);

            $message = $project->isApproved()
                ? 'Project has been fully approved'
                : 'Project approved and moved to next approval level';

            return [
                'success' => true,
                'message' => $message,
                'project' => $project,
            ];
        } catch (\Illuminate\Database\QueryException $e) {
            // Handle unique constraint violation (duplicate approval)
            if ($e->getCode() === '23000' || str_contains($e->getMessage(), 'unique_project_level_action')) {
                return [
                    'success' => false,
                    'message' => 'This project has already been approved at this level. Another officer may have processed it simultaneously.',
                    'code' => 409, // Conflict
                ];
            }
            throw $e;
        } catch (\Exception $e) {
            // Handle state validation errors from repository
            if (str_contains($e->getMessage(), 'no longer pending') ||
                str_contains($e->getMessage(), 'not pending at')) {
                return [
                    'success' => false,
                    'message' => $e->getMessage(),
                    'code' => 409, // Conflict - state changed
                ];
            }
            throw $e;
        }
    }

    /**
     * Reject a project
     */
    public function reject(int $projectId, User $user, string $comments, ?string $reason = null): array
    {
        $project = Project::find($projectId);

        if (! $project) {
            return ['success' => false, 'message' => 'Project not found', 'code' => 404];
        }

        if (! $project->isPendingApproval()) {
            return [
                'success' => false,
                'message' => 'Project is not pending approval',
                'code' => 422,
            ];
        }

        if (! $this->repository->canUserApprove($user, $project)) {
            return [
                'success' => false,
                'message' => 'You do not have permission to reject this project at the current level',
                'code' => 403,
            ];
        }

        try {
            $level = $this->repository->getUserApprovalLevel($user);
            $project = $this->repository->reject($project, $user, $level, $comments, $reason);

            return [
                'success' => true,
                'message' => 'Project has been rejected',
                'project' => $project,
            ];
        } catch (\Illuminate\Database\QueryException $e) {
            // Handle unique constraint violation (duplicate rejection)
            if ($e->getCode() === '23000' || str_contains($e->getMessage(), 'unique_project_level_action')) {
                return [
                    'success' => false,
                    'message' => 'This project has already been rejected at this level. Another officer may have processed it simultaneously.',
                    'code' => 409,
                ];
            }
            throw $e;
        } catch (\Exception $e) {
            // Handle state validation errors from repository
            if (str_contains($e->getMessage(), 'no longer pending') ||
                str_contains($e->getMessage(), 'not pending at')) {
                return [
                    'success' => false,
                    'message' => $e->getMessage(),
                    'code' => 409,
                ];
            }
            throw $e;
        }
    }

    /**
     * Request changes for a project
     */
    public function requestChanges(int $projectId, User $user, string $comments): array
    {
        $project = Project::find($projectId);

        if (! $project) {
            return ['success' => false, 'message' => 'Project not found', 'code' => 404];
        }

        if (! $project->isPendingApproval()) {
            return [
                'success' => false,
                'message' => 'Project is not pending approval',
                'code' => 422,
            ];
        }

        if (! $this->repository->canUserApprove($user, $project)) {
            return [
                'success' => false,
                'message' => 'You do not have permission to request changes for this project',
                'code' => 403,
            ];
        }

        try {
            $level = $this->repository->getUserApprovalLevel($user);
            $project = $this->repository->requestChanges($project, $user, $level, $comments);

            return [
                'success' => true,
                'message' => 'Changes have been requested for this project',
                'project' => $project,
            ];
        } catch (\Illuminate\Database\QueryException $e) {
            // Handle unique constraint violation (duplicate change request)
            if ($e->getCode() === '23000' || str_contains($e->getMessage(), 'unique_project_level_action')) {
                return [
                    'success' => false,
                    'message' => 'Changes have already been requested for this project at this level. Another officer may have processed it simultaneously.',
                    'code' => 409,
                ];
            }
            throw $e;
        } catch (\Exception $e) {
            // Handle state validation errors from repository
            if (str_contains($e->getMessage(), 'no longer pending') ||
                str_contains($e->getMessage(), 'not pending at')) {
                return [
                    'success' => false,
                    'message' => $e->getMessage(),
                    'code' => 409,
                ];
            }
            throw $e;
        }
    }

    /**
     * Get projects pending approval for the current user
     */
    public function getPendingApprovalForUser(User $user, int $perPage = 15)
    {
        return $this->repository->getPendingApprovalForUser($user, $perPage);
    }

    /**
     * Get approval history for a project
     */
    public function getApprovalHistory(int $projectId): array
    {
        return $this->repository->getApprovalHistory($projectId);
    }

    /**
     * Get projects by approval status
     */
    public function getProjectsByApprovalStatus(string $status, int $perPage = 15)
    {
        return $this->repository->getProjectsByApprovalStatus($status, $perPage);
    }

    /**
     * Get approval statistics
     */
    public function getApprovalStatistics(): array
    {
        return $this->repository->getApprovalStatistics();
    }

    /**
     * Check if user can approve a specific project
     */
    public function canUserApprove(User $user, int $projectId): bool
    {
        $project = Project::find($projectId);

        if (! $project) {
            return false;
        }

        return $this->repository->canUserApprove($user, $project);
    }

    /**
     * Get the user's approval level
     */
    public function getUserApprovalLevel(User $user): ?string
    {
        return $this->repository->getUserApprovalLevel($user);
    }
}
