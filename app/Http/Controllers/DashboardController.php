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
            $filters = $request->only(['fiscal_year', 'department_id', 'sector_id', 'region_id']);
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
     * Get budget allocation by municipality (RA 7160 Compliant)
     * Returns actual project budgets and disbursements per municipality
     * Applies territorial jurisdiction for MPDO/Barangay officers
     *
     * GET /api/dashboard/budget-allocation?sector_id=1&fiscal_year=2026
     */
    public function budgetAllocation(Request $request): JsonResponse
    {
        try {
            $filters = $request->only(['sector_id', 'fiscal_year']);

            // Apply RA 7160 territorial jurisdiction
            $user = $request->user();

            $data = $this->service->getBudgetAllocation($filters, $user);

            return ApiResponseClass::sendResponse($data, 'Budget allocation by municipality retrieved successfully');
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve budget allocation data',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get budget allocation by LGU sector (Provincial Overview)
     * Shows budget distribution across all 4 sectors (SS, ES, IEM, GPS)
     *
     * GET /api/dashboard/budget-allocation-by-sector?fiscal_year=2026
     */
    public function budgetAllocationBySector(Request $request): JsonResponse
    {
        try {
            $filters = $request->only(['fiscal_year']);

            // Apply RA 7160 territorial jurisdiction
            $user = $request->user();

            $data = $this->service->getBudgetAllocationBySector($filters, $user);

            return ApiResponseClass::sendResponse($data, 'Budget allocation by sector retrieved successfully');
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve budget allocation by sector',
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
            $filters = $request->only(['fiscal_year', 'department_id', 'sector_id']);
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
            $filters = $request->only(['year', 'department_id', 'sector_id']);
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

    /**
     * Get physical vs financial progress variance (COA metric)
     *
     * GET /api/dashboard/physical-financial-variance
     */
    public function physicalFinancialVariance(Request $request): JsonResponse
    {
        try {
            $filters = $request->only(['fiscal_year', 'department_id', 'sector_id', 'report_period', 'show_all', 'limit']);
            $data = $this->service->getPhysicalFinancialVariance($filters);

            return ApiResponseClass::sendResponse($data, 'Physical vs financial variance data retrieved successfully');
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve physical vs financial variance data',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get budget variance heatmap by department (DBM metric)
     *
     * GET /api/dashboard/budget-variance-heatmap
     */
    public function budgetVarianceHeatmap(Request $request): JsonResponse
    {
        try {
            $filters = $request->only(['fiscal_year', 'sector_id']);
            $data = $this->service->getBudgetVarianceHeatmap($filters);

            return ApiResponseClass::sendResponse($data, 'Budget variance heatmap retrieved successfully');
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve budget variance heatmap',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get milestone completion tracker (NEDA metric)
     *
     * GET /api/dashboard/milestone-completion-tracker
     */
    public function milestoneCompletionTracker(Request $request): JsonResponse
    {
        try {
            $filters = $request->only(['fiscal_year', 'department_id', 'sector_id', 'quarter']);
            $data = $this->service->getMilestoneCompletionTracker($filters);

            return ApiResponseClass::sendResponse($data, 'Milestone completion tracker data retrieved successfully');
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve milestone completion tracker data',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get target vs achievement KPI table
     *
     * GET /api/dashboard/target-achievement-kpi
     */
    public function targetAchievementKpi(Request $request): JsonResponse
    {
        try {
            $filters = $request->only(['fiscal_year', 'department_id', 'sector_id']);
            $data = $this->service->getTargetAchievementKpi($filters);

            return ApiResponseClass::sendResponse($data, 'Target vs achievement KPI data retrieved successfully');
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve target vs achievement KPI data',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get cost efficiency metrics (DBM metric)
     *
     * GET /api/dashboard/cost-efficiency-metrics
     */
    public function costEfficiencyMetrics(Request $request): JsonResponse
    {
        try {
            $filters = $request->only(['fiscal_year', 'sector_id']);
            $data = $this->service->getCostEfficiencyMetrics($filters);

            return ApiResponseClass::sendResponse($data, 'Cost efficiency metrics retrieved successfully');
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve cost efficiency metrics',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get risk dashboard
     *
     * GET /api/dashboard/risk-dashboard
     */
    public function riskDashboard(Request $request): JsonResponse
    {
        try {
            $filters = $request->only(['fiscal_year', 'department_id', 'sector_id']);
            $data = $this->service->getRiskDashboard($filters);

            return ApiResponseClass::sendResponse($data, 'Risk dashboard data retrieved successfully');
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve risk dashboard data',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get beneficiary impact metrics
     *
     * GET /api/dashboard/beneficiary-impact-metrics
     */
    public function beneficiaryImpactMetrics(Request $request): JsonResponse
    {
        try {
            $filters = $request->only(['fiscal_year', 'sector_id']);
            $data = $this->service->getBeneficiaryImpactMetrics($filters);

            return ApiResponseClass::sendResponse($data, 'Beneficiary impact metrics retrieved successfully');
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve beneficiary impact metrics',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get compliance scorecard (COA audit readiness)
     *
     * GET /api/dashboard/compliance-scorecard
     */
    public function complianceScorecard(Request $request): JsonResponse
    {
        try {
            $filters = $request->only(['fiscal_year', 'department_id', 'sector_id']);
            $data = $this->service->getComplianceScorecard($filters);

            return ApiResponseClass::sendResponse($data, 'Compliance scorecard retrieved successfully');
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve compliance scorecard',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get year-over-year performance trends
     *
     * GET /api/dashboard/year-over-year-trends
     */
    public function yearOverYearTrends(Request $request): JsonResponse
    {
        try {
            $filters = $request->only(['current_year']);
            $data = $this->service->getYearOverYearTrends($filters);

            return ApiResponseClass::sendResponse($data, 'Year-over-year trends retrieved successfully');
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve year-over-year trends',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get early warning alerts
     *
     * GET /api/dashboard/early-warning-alerts
     */
    public function earlyWarningAlerts(Request $request): JsonResponse
    {
        try {
            $filters = $request->only(['fiscal_year', 'sector_id']);
            $data = $this->service->getEarlyWarningAlerts($filters);

            return ApiResponseClass::sendResponse($data, 'Early warning alerts retrieved successfully');
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve early warning alerts',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
