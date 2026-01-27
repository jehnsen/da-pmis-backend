<?php

namespace App\Http\Controllers;

use App\Classes\ApiResponseClass;
use App\Http\Requests\ProjectDisbursement\StoreProjectDisbursementRequest;
use App\Http\Requests\ProjectDisbursement\UpdateProjectDisbursementRequest;
use App\Http\Resources\ProjectDisbursementResource;
use App\Models\Project;
use App\Models\ProjectDisbursement;
use App\Services\ProjectDisbursementService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProjectDisbursementController extends Controller
{
    public function __construct(
        protected ProjectDisbursementService $service
    ) {}

    /**
     * List disbursements for a project
     *
     * GET /api/projects/{project}/disbursements
     */
    public function index(Request $request, Project $project): JsonResponse
    {
        try {
            $perPage = (int) $request->query('per_page', 15);
            $disbursements = $this->service->getByProject($project->id, $perPage);

            return response()->json([
                'success' => true,
                'data' => ProjectDisbursementResource::collection($disbursements),
                'meta' => [
                    'current_page' => $disbursements->currentPage(),
                    'last_page' => $disbursements->lastPage(),
                    'per_page' => $disbursements->perPage(),
                    'total' => $disbursements->total(),
                ],
            ]);
        } catch (\Exception $e) {
            return ApiResponseClass::rollback($e, 'Failed to retrieve disbursements');
        }
    }

    /**
     * Create a new disbursement
     *
     * POST /api/projects/{project}/disbursements
     */
    public function store(StoreProjectDisbursementRequest $request, Project $project): JsonResponse
    {
        try {
            $data = $request->validated();
            $data['project_id'] = $project->id;
            $data['created_by'] = Auth::id();
            $data['status'] = 'pending';

            $disbursement = $this->service->create($data);

            return response()->json([
                'success' => true,
                'message' => 'Disbursement created successfully',
                'data' => new ProjectDisbursementResource($disbursement),
            ], 201);
        } catch (\Exception $e) {
            return ApiResponseClass::rollback($e, 'Failed to create disbursement');
        }
    }

    /**
     * Show a specific disbursement
     *
     * GET /api/projects/{project}/disbursements/{disbursement}
     */
    public function show(Project $project, ProjectDisbursement $disbursement): JsonResponse
    {
        try {
            if ($disbursement->project_id !== $project->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Disbursement does not belong to this project',
                ], 404);
            }

            $disbursement->load(['project', 'createdBy', 'approvedBy']);

            return response()->json([
                'success' => true,
                'data' => new ProjectDisbursementResource($disbursement),
            ]);
        } catch (\Exception $e) {
            return ApiResponseClass::rollback($e, 'Failed to retrieve disbursement');
        }
    }

    /**
     * Update a disbursement
     *
     * PUT /api/projects/{project}/disbursements/{disbursement}
     */
    public function update(UpdateProjectDisbursementRequest $request, Project $project, ProjectDisbursement $disbursement): JsonResponse
    {
        try {
            if ($disbursement->project_id !== $project->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Disbursement does not belong to this project',
                ], 404);
            }

            if ($disbursement->status === 'completed') {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot update a completed disbursement',
                ], 422);
            }

            $disbursement = $this->service->update($disbursement, $request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Disbursement updated successfully',
                'data' => new ProjectDisbursementResource($disbursement),
            ]);
        } catch (\Exception $e) {
            return ApiResponseClass::rollback($e, 'Failed to update disbursement');
        }
    }

    /**
     * Delete a disbursement
     *
     * DELETE /api/projects/{project}/disbursements/{disbursement}
     */
    public function destroy(Project $project, ProjectDisbursement $disbursement): JsonResponse
    {
        try {
            if ($disbursement->project_id !== $project->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Disbursement does not belong to this project',
                ], 404);
            }

            if ($disbursement->status === 'completed') {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete a completed disbursement',
                ], 422);
            }

            $this->service->delete($disbursement);

            return response()->json([
                'success' => true,
                'message' => 'Disbursement deleted successfully',
            ]);
        } catch (\Exception $e) {
            return ApiResponseClass::rollback($e, 'Failed to delete disbursement');
        }
    }

    /**
     * Approve a disbursement (mark as completed)
     *
     * POST /api/projects/{project}/disbursements/{disbursement}/approve
     */
    public function approve(Project $project, ProjectDisbursement $disbursement): JsonResponse
    {
        try {
            if ($disbursement->project_id !== $project->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Disbursement does not belong to this project',
                ], 404);
            }

            if ($disbursement->status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'Only pending disbursements can be approved',
                ], 422);
            }

            $disbursement = $this->service->updateStatus($disbursement, 'completed', Auth::id());

            return response()->json([
                'success' => true,
                'message' => 'Disbursement approved successfully',
                'data' => new ProjectDisbursementResource($disbursement),
            ]);
        } catch (\Exception $e) {
            return ApiResponseClass::rollback($e, 'Failed to approve disbursement');
        }
    }

    /**
     * Cancel a disbursement
     *
     * POST /api/projects/{project}/disbursements/{disbursement}/cancel
     */
    public function cancel(Project $project, ProjectDisbursement $disbursement): JsonResponse
    {
        try {
            if ($disbursement->project_id !== $project->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Disbursement does not belong to this project',
                ], 404);
            }

            if ($disbursement->status === 'completed') {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot cancel a completed disbursement',
                ], 422);
            }

            $disbursement = $this->service->updateStatus($disbursement, 'cancelled');

            return response()->json([
                'success' => true,
                'message' => 'Disbursement cancelled successfully',
                'data' => new ProjectDisbursementResource($disbursement),
            ]);
        } catch (\Exception $e) {
            return ApiResponseClass::rollback($e, 'Failed to cancel disbursement');
        }
    }

    /**
     * Get financial summary for a project
     *
     * GET /api/projects/{project}/financial-summary
     */
    public function financialSummary(Project $project): JsonResponse
    {
        try {
            $summary = $this->service->getFinancialSummary($project);

            return response()->json([
                'success' => true,
                'data' => $summary,
            ]);
        } catch (\Exception $e) {
            return ApiResponseClass::rollback($e, 'Failed to retrieve financial summary');
        }
    }

    /**
     * Get disbursements by category for a project
     *
     * GET /api/projects/{project}/disbursements-by-category
     */
    public function byCategory(Project $project): JsonResponse
    {
        try {
            $categories = $this->service->getDisbursementsByCategory($project);

            return response()->json([
                'success' => true,
                'data' => $categories,
            ]);
        } catch (\Exception $e) {
            return ApiResponseClass::rollback($e, 'Failed to retrieve disbursements by category');
        }
    }

    /**
     * Get monthly spending for a project
     *
     * GET /api/projects/{project}/monthly-spending
     */
    public function monthlySpending(Request $request, Project $project): JsonResponse
    {
        try {
            $year = $request->query('year', now()->year);
            $spending = $this->service->getMonthlySpending($project, (int) $year);

            return response()->json([
                'success' => true,
                'data' => [
                    'year' => (int) $year,
                    'monthly_spending' => $spending,
                ],
            ]);
        } catch (\Exception $e) {
            return ApiResponseClass::rollback($e, 'Failed to retrieve monthly spending');
        }
    }

    /**
     * Get available disbursement categories
     *
     * GET /api/disbursement-categories
     */
    public function categories(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => ProjectDisbursement::CATEGORIES,
        ]);
    }
}
