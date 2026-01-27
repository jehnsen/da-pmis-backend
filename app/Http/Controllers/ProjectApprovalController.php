<?php

namespace App\Http\Controllers;

use App\Classes\ApiResponseClass;
use App\Http\Resources\ProjectApprovalResource;
use App\Http\Resources\ProjectResource;
use App\Services\ProjectApprovalService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProjectApprovalController extends Controller
{
    public function __construct(
        private readonly ProjectApprovalService $service
    ) {}

    /**
     * Submit a project for approval
     *
     * POST /api/projects/{id}/submit-for-approval
     */
    public function submitForApproval(int $project): JsonResponse
    {
        try {
            $user = Auth::user();
            $result = $this->service->submitForApproval($project, $user);

            if (! $result['success']) {
                return response()->json([
                    'message' => $result['message'],
                ], $result['code']);
            }

            return response()->json([
                'success' => true,
                'message' => $result['message'],
                'data' => new ProjectResource($result['project']),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to submit project for approval',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Approve a project
     *
     * POST /api/projects/{id}/approve
     */
    public function approve(Request $request, int $project): JsonResponse
    {
        try {
            $request->validate([
                'comments' => ['nullable', 'string', 'max:1000'],
            ]);

            $user = Auth::user();
            $result = $this->service->approve($project, $user, $request->comments);

            if (! $result['success']) {
                return response()->json([
                    'message' => $result['message'],
                ], $result['code']);
            }

            return response()->json([
                'success' => true,
                'message' => $result['message'],
                'data' => new ProjectResource($result['project']),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to approve project',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Reject a project
     *
     * POST /api/projects/{id}/reject
     */
    public function reject(Request $request, int $project): JsonResponse
    {
        try {
            $request->validate([
                'comments' => ['required', 'string', 'max:1000'],
                'reason' => ['nullable', 'string', 'max:255'],
            ]);

            $user = Auth::user();
            $result = $this->service->reject($project, $user, $request->comments, $request->reason);

            if (! $result['success']) {
                return response()->json([
                    'message' => $result['message'],
                ], $result['code']);
            }

            return response()->json([
                'success' => true,
                'message' => $result['message'],
                'data' => new ProjectResource($result['project']),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to reject project',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Request changes for a project
     *
     * POST /api/projects/{id}/request-changes
     */
    public function requestChanges(Request $request, int $project): JsonResponse
    {
        try {
            $request->validate([
                'comments' => ['required', 'string', 'max:1000'],
            ]);

            $user = Auth::user();
            $result = $this->service->requestChanges($project, $user, $request->comments);

            if (! $result['success']) {
                return response()->json([
                    'message' => $result['message'],
                ], $result['code']);
            }

            return response()->json([
                'success' => true,
                'message' => $result['message'],
                'data' => new ProjectResource($result['project']),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to request changes',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get projects pending approval for the current user
     *
     * GET /api/projects/pending-approval
     */
    public function pendingApproval(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            $perPage = (int) $request->query('per_page', 15);

            $projects = $this->service->getPendingApprovalForUser($user, $perPage);

            $userLevel = $this->service->getUserApprovalLevel($user);

            return ProjectResource::collection($projects)->additional([
                'user_approval_level' => $userLevel,
                'user_approval_level_display' => $userLevel ? ucfirst($userLevel).' Officer' : null,
            ])->response();
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve pending approvals',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get approval history for a project
     *
     * GET /api/projects/{id}/approval-history
     */
    public function approvalHistory(int $project): JsonResponse
    {
        try {
            $history = $this->service->getApprovalHistory($project);

            if (empty($history)) {
                return response()->json([
                    'message' => 'Project not found',
                ], 404);
            }

            return ApiResponseClass::sendResponse($history, 'Approval history retrieved successfully');
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve approval history',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get approval statistics
     *
     * GET /api/projects/approval-statistics
     */
    public function statistics(): JsonResponse
    {
        try {
            $stats = $this->service->getApprovalStatistics();

            return ApiResponseClass::sendResponse($stats, 'Approval statistics retrieved successfully');
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve approval statistics',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get projects by approval status
     *
     * GET /api/projects/by-approval-status
     */
    public function byApprovalStatus(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'status' => ['required', 'string', 'in:draft,pending_municipal,pending_provincial,pending_regional,approved,rejected'],
            ]);

            $perPage = (int) $request->query('per_page', 15);
            $projects = $this->service->getProjectsByApprovalStatus($request->status, $perPage);

            return ProjectResource::collection($projects)->response();
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve projects',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
