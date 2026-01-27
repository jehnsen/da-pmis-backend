<?php

namespace App\Http\Controllers;

use App\Classes\ApiResponseClass;
use App\Services\DashboardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(private readonly DashboardService $service)
    {
    }

    /**
     * Get dashboard overview statistics
     *
     * GET /api/dashboard/overview
     */
    public function overview(Request $request): JsonResponse
    {
        try {
            $filters = $request->only(['fiscal_year', 'department_id', 'region_id']);
            $data = $this->service->getOverview($filters);

            return ApiResponseClass::sendResponse($data, 'Dashboard overview retrieved successfully');
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve dashboard overview',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get budget allocation by region
     *
     * GET /api/dashboard/budget-allocation
     */
    public function budgetAllocation(Request $request): JsonResponse
    {
        try {
            $filters = $request->only(['region_id', 'fiscal_year']);
            $data = $this->service->getBudgetAllocation($filters);

            return ApiResponseClass::sendResponse($data, 'Budget allocation data retrieved successfully');
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve budget allocation data',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get project status distribution
     *
     * GET /api/dashboard/project-status-distribution
     */
    public function projectStatusDistribution(Request $request): JsonResponse
    {
        try {
            $filters = $request->only(['fiscal_year', 'department_id']);
            $data = $this->service->getProjectStatusDistribution($filters);

            return ApiResponseClass::sendResponse($data, 'Project status distribution retrieved successfully');
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve project status distribution',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get national agricultural performance data
     *
     * GET /api/dashboard/national-performance
     */
    public function nationalPerformance(Request $request): JsonResponse
    {
        try {
            $filters = $request->only(['year']);
            $data = $this->service->getNationalPerformance($filters);

            return ApiResponseClass::sendResponse($data, 'National performance data retrieved successfully');
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve national performance data',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get recent project updates
     *
     * GET /api/dashboard/recent-updates
     */
    public function recentUpdates(Request $request): JsonResponse
    {
        try {
            $limit = (int) $request->query('limit', 10);
            $limit = min(max($limit, 1), 50); // Clamp between 1 and 50

            $data = $this->service->getRecentUpdates($limit);

            return ApiResponseClass::sendResponse($data, 'Recent updates retrieved successfully');
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve recent updates',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get monthly progress comparison
     *
     * GET /api/dashboard/monthly-progress
     */
    public function monthlyProgress(Request $request): JsonResponse
    {
        try {
            $filters = $request->only(['year', 'department_id']);
            $data = $this->service->getMonthlyProgress($filters);

            return ApiResponseClass::sendResponse($data, 'Monthly progress data retrieved successfully');
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve monthly progress data',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
