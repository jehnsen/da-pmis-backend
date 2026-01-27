<?php

namespace App\Http\Controllers;

use App\Classes\ApiResponseClass;
use App\Services\DepartmentReportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DepartmentReportController extends Controller
{
    public function __construct(
        private readonly DepartmentReportService $service
    ) {}

    /**
     * Get aggregated reports for all departments
     *
     * GET /api/departments/reports
     * Query params: ?fiscal_year={year}&quarter={1-4}
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $filters = [
                'fiscal_year' => $request->query('fiscal_year', date('Y')),
                'quarter' => $request->query('quarter'),
            ];

            $data = $this->service->getDepartmentReports($filters);

            return ApiResponseClass::sendResponse($data, 'Department reports retrieved successfully');
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve department reports',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get budget utilization for all departments
     *
     * GET /api/departments/budget-utilization
     * Query params: ?fiscal_year={year}
     */
    public function budgetUtilization(Request $request): JsonResponse
    {
        try {
            $filters = [
                'fiscal_year' => $request->query('fiscal_year', date('Y')),
            ];

            $data = $this->service->getBudgetUtilization($filters);

            return ApiResponseClass::sendResponse($data, 'Budget utilization data retrieved successfully');
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve budget utilization data',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get monthly progress for a specific department
     *
     * GET /api/departments/{id}/monthly-progress
     * Query params: ?year={year}
     */
    public function monthlyProgress(Request $request, int $department): JsonResponse
    {
        try {
            $year = (int) $request->query('year', date('Y'));

            // Check if department exists
            $dept = $this->service->findDepartment($department);
            if (! $dept) {
                return response()->json(['message' => 'Department not found'], 404);
            }

            $data = $this->service->getMonthlyProgress($department, $year);

            return ApiResponseClass::sendResponse($data, 'Monthly progress data retrieved successfully');
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve monthly progress data',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get KPI summary for a specific department
     *
     * GET /api/departments/{id}/kpi-summary
     * Query params: ?fiscal_year={year}
     */
    public function kpiSummary(Request $request, int $department): JsonResponse
    {
        try {
            $fiscalYear = $request->query('fiscal_year') ? (int) $request->query('fiscal_year') : null;

            // Check if department exists
            $dept = $this->service->findDepartment($department);
            if (! $dept) {
                return response()->json(['message' => 'Department not found'], 404);
            }

            $data = $this->service->getKpiSummary($department, $fiscalYear);

            return ApiResponseClass::sendResponse($data, 'KPI summary retrieved successfully');
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve KPI summary',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
