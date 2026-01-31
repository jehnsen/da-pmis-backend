<?php

namespace App\Http\Controllers;

use App\Classes\ApiResponseClass;
use App\Models\LguSector;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * LGU Sector Reports Controller
 *
 * Provides aggregated reporting for the 4 LGU sectors (RA 7160):
 * - Social Services (SS)
 * - Economic Services (ES)
 * - Infrastructure & Environmental Management (IEM)
 * - General Public Services (GPS)
 */
class LguSectorReportController extends Controller
{
    /**
     * Get aggregated reports for all LGU sectors
     *
     * GET /api/lgu-sectors/reports
     * Query params: ?fiscal_year={year}&quarter={1-4}&include_stats={true/false}
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $fiscalYear = $request->query('fiscal_year', date('Y'));
            $quarter = $request->query('quarter');
            $includeStats = $request->query('include_stats', 'true') === 'true';

            $sectors = LguSector::active()
                ->ordered()
                ->get()
                ->map(function ($sector) use ($fiscalYear, $quarter, $includeStats) {
                    $data = [
                        'id' => $sector->id,
                        'name' => $sector->name,
                        'code' => $sector->code,
                        'description' => $sector->description,
                        'icon' => $sector->icon,
                        'color_code' => $sector->color_code,
                    ];

                    if ($includeStats) {
                        // Get projects query for this sector
                        $projectsQuery = $sector->projects();

                        // Filter by fiscal year if provided
                        if ($fiscalYear) {
                            $projectsQuery->whereYear('start_date', $fiscalYear);
                        }

                        // Filter by quarter if provided
                        if ($quarter) {
                            $startMonth = ($quarter - 1) * 3 + 1;
                            $endMonth = $startMonth + 2;
                            $projectsQuery->whereRaw('MONTH(start_date) BETWEEN ? AND ?', [$startMonth, $endMonth]);
                        }

                        $projects = $projectsQuery->get();

                        // Calculate statistics
                        $totalProjects = $projects->count();
                        $approvedProjects = $projects->where('approval_status', 'approved')->count();
                        $totalBudget = $projects->sum('budget');

                        // Calculate total disbursed
                        $totalDisbursed = DB::table('project_disbursements')
                            ->whereIn('project_id', $projects->pluck('id'))
                            ->where('status', 'approved')
                            ->sum('amount');

                        $utilizationRate = $totalBudget > 0
                            ? round(($totalDisbursed / $totalBudget) * 100, 2)
                            : 0;

                        // Status breakdown
                        $statusBreakdown = $projects->groupBy('approval_status')->map(function ($group) {
                            return $group->count();
                        });

                        $data['statistics'] = [
                            'total_projects' => $totalProjects,
                            'approved_projects' => $approvedProjects,
                            'total_budget' => (float) $totalBudget,
                            'total_disbursed' => (float) $totalDisbursed,
                            'utilization_rate' => $utilizationRate,
                            'approval_rate' => $totalProjects > 0
                                ? round(($approvedProjects / $totalProjects) * 100, 2)
                                : 0,
                            'status_breakdown' => $statusBreakdown,
                        ];
                    }

                    return $data;
                });

            return ApiResponseClass::sendResponse([
                'fiscal_year' => $fiscalYear,
                'quarter' => $quarter,
                'sectors' => $sectors,
                'summary' => [
                    'total_sectors' => $sectors->count(),
                    'total_budget_all_sectors' => $sectors->sum('statistics.total_budget'),
                    'total_disbursed_all_sectors' => $sectors->sum('statistics.total_disbursed'),
                    'total_projects_all_sectors' => $sectors->sum('statistics.total_projects'),
                ],
            ], 'LGU sector reports retrieved successfully');
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve LGU sector reports',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get budget utilization for all LGU sectors
     *
     * GET /api/lgu-sectors/budget-utilization
     * Query params: ?fiscal_year={year}
     */
    public function budgetUtilization(Request $request): JsonResponse
    {
        try {
            $fiscalYear = $request->query('fiscal_year', date('Y'));

            $sectors = LguSector::active()
                ->ordered()
                ->get()
                ->map(function ($sector) use ($fiscalYear) {
                    // Get projects for this sector and fiscal year
                    $projects = $sector->projects()
                        ->whereYear('start_date', $fiscalYear)
                        ->get();

                    $totalBudget = $projects->sum('budget');

                    $totalDisbursed = DB::table('project_disbursements')
                        ->whereIn('project_id', $projects->pluck('id'))
                        ->where('status', 'approved')
                        ->sum('amount');

                    $utilizationRate = $totalBudget > 0
                        ? round(($totalDisbursed / $totalBudget) * 100, 2)
                        : 0;

                    return [
                        'sector_id' => $sector->id,
                        'sector_name' => $sector->name,
                        'sector_code' => $sector->code,
                        'color_code' => $sector->color_code,
                        'total_budget' => (float) $totalBudget,
                        'total_disbursed' => (float) $totalDisbursed,
                        'remaining_budget' => (float) ($totalBudget - $totalDisbursed),
                        'utilization_rate' => $utilizationRate,
                        'projects_count' => $projects->count(),
                    ];
                });

            return ApiResponseClass::sendResponse([
                'fiscal_year' => $fiscalYear,
                'sectors' => $sectors,
                'total_budget' => $sectors->sum('total_budget'),
                'total_disbursed' => $sectors->sum('total_disbursed'),
                'average_utilization_rate' => $sectors->avg('utilization_rate'),
            ], 'Budget utilization data retrieved successfully');
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve budget utilization data',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get monthly progress for a specific LGU sector
     *
     * GET /api/lgu-sectors/{id}/monthly-progress
     * Query params: ?year={year}
     */
    public function monthlyProgress(Request $request, int $sectorId): JsonResponse
    {
        try {
            $year = (int) $request->query('year', date('Y'));

            $sector = LguSector::find($sectorId);
            if (!$sector) {
                return response()->json(['message' => 'LGU Sector not found'], 404);
            }

            // Get monthly data for the year
            $monthlyData = [];
            for ($month = 1; $month <= 12; $month++) {
                $projects = $sector->projects()
                    ->whereYear('start_date', $year)
                    ->whereMonth('start_date', $month)
                    ->get();

                $disbursements = DB::table('project_disbursements')
                    ->whereIn('project_id', $projects->pluck('id'))
                    ->where('status', 'approved')
                    ->whereYear('disbursement_date', $year)
                    ->whereMonth('disbursement_date', $month)
                    ->sum('amount');

                $monthlyData[] = [
                    'month' => $month,
                    'month_name' => date('F', mktime(0, 0, 0, $month, 1)),
                    'projects_started' => $projects->count(),
                    'budget_allocated' => (float) $projects->sum('budget'),
                    'disbursements' => (float) $disbursements,
                    'projects_approved' => $projects->where('approval_status', 'approved')->count(),
                ];
            }

            return ApiResponseClass::sendResponse([
                'sector' => [
                    'id' => $sector->id,
                    'name' => $sector->name,
                    'code' => $sector->code,
                ],
                'year' => $year,
                'monthly_data' => $monthlyData,
            ], 'Monthly progress data retrieved successfully');
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve monthly progress data',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get performance summary for a specific LGU sector
     *
     * GET /api/lgu-sectors/{id}/performance-summary
     * Query params: ?fiscal_year={year}
     */
    public function performanceSummary(Request $request, int $sectorId): JsonResponse
    {
        try {
            $fiscalYear = $request->query('fiscal_year') ? (int) $request->query('fiscal_year') : null;

            $sector = LguSector::find($sectorId);
            if (!$sector) {
                return response()->json(['message' => 'LGU Sector not found'], 404);
            }

            // Build query
            $projectsQuery = $sector->projects();

            if ($fiscalYear) {
                $projectsQuery->whereYear('start_date', $fiscalYear);
            }

            $projects = $projectsQuery->get();

            // Performance metrics
            $totalProjects = $projects->count();
            $approvedProjects = $projects->where('approval_status', 'approved')->count();
            $ongoingProjects = $projects->whereIn('status_id', function($query) {
                $query->select('id')
                    ->from('project_statuses')
                    ->where('name', 'On Track');
            })->count();

            $completedProjects = $projects->whereIn('status_id', function($query) {
                $query->select('id')
                    ->from('project_statuses')
                    ->where('name', 'Completed');
            })->count();

            $totalBudget = $projects->sum('budget');

            $totalDisbursed = DB::table('project_disbursements')
                ->whereIn('project_id', $projects->pluck('id'))
                ->where('status', 'approved')
                ->sum('amount');

            // Approval workflow performance
            $approvalBreakdown = $projects->groupBy('approval_status')->map(function ($group, $status) {
                return [
                    'count' => $group->count(),
                    'percentage' => 0, // Will calculate below
                ];
            });

            if ($totalProjects > 0) {
                foreach ($approvalBreakdown as $status => $data) {
                    $approvalBreakdown[$status]['percentage'] = round(($data['count'] / $totalProjects) * 100, 2);
                }
            }

            return ApiResponseClass::sendResponse([
                'sector' => [
                    'id' => $sector->id,
                    'name' => $sector->name,
                    'code' => $sector->code,
                    'description' => $sector->description,
                ],
                'fiscal_year' => $fiscalYear,
                'performance' => [
                    'total_projects' => $totalProjects,
                    'approved_projects' => $approvedProjects,
                    'ongoing_projects' => $ongoingProjects,
                    'completed_projects' => $completedProjects,
                    'completion_rate' => $totalProjects > 0
                        ? round(($completedProjects / $totalProjects) * 100, 2)
                        : 0,
                    'approval_rate' => $totalProjects > 0
                        ? round(($approvedProjects / $totalProjects) * 100, 2)
                        : 0,
                ],
                'budget' => [
                    'total_budget' => (float) $totalBudget,
                    'total_disbursed' => (float) $totalDisbursed,
                    'remaining_budget' => (float) ($totalBudget - $totalDisbursed),
                    'utilization_rate' => $totalBudget > 0
                        ? round(($totalDisbursed / $totalBudget) * 100, 2)
                        : 0,
                ],
                'approval_breakdown' => $approvalBreakdown,
            ], 'Performance summary retrieved successfully');
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve performance summary',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Compare performance across all sectors
     *
     * GET /api/lgu-sectors/compare
     * Query params: ?fiscal_year={year}&metric={budget|projects|utilization}
     */
    public function compare(Request $request): JsonResponse
    {
        try {
            $fiscalYear = $request->query('fiscal_year', date('Y'));
            $metric = $request->query('metric', 'budget'); // budget, projects, utilization

            $sectors = LguSector::active()
                ->ordered()
                ->get()
                ->map(function ($sector) use ($fiscalYear) {
                    $projects = $sector->projects()
                        ->whereYear('start_date', $fiscalYear)
                        ->get();

                    $totalBudget = $projects->sum('budget');
                    $totalDisbursed = DB::table('project_disbursements')
                        ->whereIn('project_id', $projects->pluck('id'))
                        ->where('status', 'approved')
                        ->sum('amount');

                    return [
                        'sector_id' => $sector->id,
                        'sector_name' => $sector->name,
                        'sector_code' => $sector->code,
                        'color_code' => $sector->color_code,
                        'total_projects' => $projects->count(),
                        'approved_projects' => $projects->where('approval_status', 'approved')->count(),
                        'total_budget' => (float) $totalBudget,
                        'total_disbursed' => (float) $totalDisbursed,
                        'utilization_rate' => $totalBudget > 0
                            ? round(($totalDisbursed / $totalBudget) * 100, 2)
                            : 0,
                    ];
                });

            // Sort by the requested metric
            $sorted = $sectors->sortByDesc(function ($sector) use ($metric) {
                switch ($metric) {
                    case 'projects':
                        return $sector['total_projects'];
                    case 'utilization':
                        return $sector['utilization_rate'];
                    case 'budget':
                    default:
                        return $sector['total_budget'];
                }
            })->values();

            return ApiResponseClass::sendResponse([
                'fiscal_year' => $fiscalYear,
                'metric' => $metric,
                'sectors' => $sorted,
                'insights' => [
                    'highest_budget' => $sorted->first(),
                    'highest_utilization' => $sectors->sortByDesc('utilization_rate')->first(),
                    'most_projects' => $sectors->sortByDesc('total_projects')->first(),
                ],
            ], 'Sector comparison data retrieved successfully');
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve comparison data',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get departments for a specific LGU sector
     *
     * GET /api/lgu-sectors/{id}/departments
     * Query params: ?include_stats={true/false}
     */
    public function getDepartments(Request $request, int $sectorId): JsonResponse
    {
        try {
            $includeStats = $request->query('include_stats', 'true') === 'true';

            $sector = LguSector::find($sectorId);
            if (!$sector) {
                return response()->json(['message' => 'LGU Sector not found'], 404);
            }

            $departments = $sector->departments()->get()->map(function ($department) use ($includeStats) {
                $data = [
                    'id' => $department->id,
                    'name' => $department->name,
                    'description' => $department->description,
                ];

                if ($includeStats) {
                    $projects = $department->projects()->get();
                    $totalBudget = $projects->sum('budget');

                    $totalDisbursed = DB::table('project_disbursements')
                        ->whereIn('project_id', $projects->pluck('id'))
                        ->where('status', 'approved')
                        ->sum('amount');

                    $data['statistics'] = [
                        'total_projects' => $projects->count(),
                        'active_projects' => $projects->whereIn('approval_status', ['approved', 'pending_governor'])->count(),
                        'total_budget' => (float) $totalBudget,
                        'total_disbursed' => (float) $totalDisbursed,
                        'utilization_rate' => $totalBudget > 0
                            ? round(($totalDisbursed / $totalBudget) * 100, 2)
                            : 0,
                        'beneficiaries' => $projects->sum('beneficiaries_count'),
                    ];
                }

                return $data;
            });

            return ApiResponseClass::sendResponse([
                'sector' => [
                    'id' => $sector->id,
                    'name' => $sector->name,
                    'code' => $sector->code,
                ],
                'departments' => $departments,
                'total_departments' => $departments->count(),
            ], 'Departments retrieved successfully');
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve departments',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get sector overview with department breakdown
     *
     * GET /api/lgu-sectors/{id}/departments-overview
     * Query params: ?fiscal_year={year}
     */
    public function departmentsOverview(Request $request, int $sectorId): JsonResponse
    {
        try {
            $fiscalYear = $request->query('fiscal_year'); // Don't default to current year

            $sector = LguSector::find($sectorId);
            if (!$sector) {
                return response()->json(['message' => 'LGU Sector not found'], 404);
            }

            // Get sector-level projects for fiscal year
            $sectorProjectsQuery = $sector->projects();

            if ($fiscalYear) {
                $sectorProjectsQuery->whereYear('start_date', $fiscalYear);
            }

            $sectorProjects = $sectorProjectsQuery->get();

            $sectorTotalBudget = $sectorProjects->sum('budget');
            $sectorTotalDisbursed = DB::table('project_disbursements')
                ->whereIn('project_id', $sectorProjects->pluck('id'))
                ->where('status', 'approved')
                ->sum('amount');

            // Get department breakdown
            $departments = $sector->departments()->get()->map(function ($department) use ($fiscalYear) {
                $projectsQuery = $department->projects();

                if ($fiscalYear) {
                    $projectsQuery->whereYear('start_date', $fiscalYear);
                }

                $projects = $projectsQuery->get();

                $totalBudget = $projects->sum('budget');
                $totalDisbursed = DB::table('project_disbursements')
                    ->whereIn('project_id', $projects->pluck('id'))
                    ->where('status', 'approved')
                    ->sum('amount');

                return [
                    'id' => $department->id,
                    'name' => $department->name,
                    'description' => $department->description,
                    'projects_count' => $projects->count(),
                    'total_budget' => (float) $totalBudget,
                    'total_disbursed' => (float) $totalDisbursed,
                    'remaining_budget' => (float) ($totalBudget - $totalDisbursed),
                    'utilization_rate' => $totalBudget > 0
                        ? round(($totalDisbursed / $totalBudget) * 100, 2)
                        : 0,
                    'beneficiaries' => $projects->sum('beneficiaries_count'),
                    'compliance_average' => $projects->avg('compliance_score')
                        ? round($projects->avg('compliance_score'), 2)
                        : 0,
                ];
            });

            return ApiResponseClass::sendResponse([
                'sector' => [
                    'id' => $sector->id,
                    'name' => $sector->name,
                    'code' => $sector->code,
                    'description' => $sector->description,
                    'icon' => $sector->icon,
                    'color_code' => $sector->color_code,
                ],
                'fiscal_year' => $fiscalYear,
                'overview' => [
                    'total_projects' => $sectorProjects->count(),
                    'active_projects' => $sectorProjects->whereIn('approval_status', ['approved', 'pending_governor'])->count(),
                    'total_budget' => (float) $sectorTotalBudget,
                    'total_disbursed' => (float) $sectorTotalDisbursed,
                    'remaining_budget' => (float) ($sectorTotalBudget - $sectorTotalDisbursed),
                    'utilization_rate' => $sectorTotalBudget > 0
                        ? round(($sectorTotalDisbursed / $sectorTotalBudget) * 100, 2)
                        : 0,
                    'total_beneficiaries' => $sectorProjects->sum('beneficiaries_count'),
                    'average_compliance' => $sectorProjects->avg('compliance_score')
                        ? round($sectorProjects->avg('compliance_score'), 2)
                        : 0,
                ],
                'departments' => $departments,
                'department_count' => $departments->count(),
            ], 'Sector overview with departments retrieved successfully');
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve sector overview',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
