<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProgressReport\StoreProgressReportRequest;
use App\Http\Requests\ProgressReport\UpdateProgressReportRequest;
use App\Http\Resources\ProgressReportResource;
use App\Models\Project;
use App\Models\ProgressReport;
use App\Services\ProgressReportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProgressReportController extends Controller
{
    public function __construct(private readonly ProgressReportService $service)
    {
    }

    public function index(Request $request)
    {
        $perPage = (int) $request->query('per_page', 15);
        $filters = $request->only([
            'department_id',
            'project_id',
            'report_period',
            'progress_status',
            'date_from',
            'date_to',
        ]);
        $data = $this->service->list($perPage, $filters);

        return ProgressReportResource::collection($data);
    }

    public function store(StoreProgressReportRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();
            $data['created_by'] = Auth::id();

            // Auto-determine progress status if not provided
            if (empty($data['progress_status'])) {
                $tempReport = new ProgressReport($data);
                $data['progress_status'] = $tempReport->determineProgressStatus();
            }

            $report = $this->service->create($data);

            return (new ProgressReportResource($report->load(['project', 'department', 'metrics', 'creator'])))
                ->response()
                ->setStatusCode(201);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to create progress report', 'error' => $e->getMessage()], 500);
        }
    }

    public function show(int $progressReport): ProgressReportResource
    {
        $report = $this->service->getById($progressReport);
        abort_unless($report, 404);

        return new ProgressReportResource($report->load(['project', 'department', 'metrics', 'creator']));
    }

    public function update(UpdateProgressReportRequest $request, int $progressReport): JsonResponse
    {
        try {
            $data = $request->validated();

            // Auto-determine progress status if progress values changed
            if (isset($data['physical_progress']) || isset($data['financial_progress'])) {
                $existingReport = $this->service->getById($progressReport);
                $tempReport = new ProgressReport(array_merge($existingReport->toArray(), $data));
                $data['progress_status'] = $tempReport->determineProgressStatus();
            }

            $report = $this->service->update($progressReport, $data);

            return (new ProgressReportResource($report->load(['project', 'department', 'metrics'])))
                ->response();
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to update progress report', 'error' => $e->getMessage()], 500);
        }
    }

    public function destroy(int $progressReport): JsonResponse
    {
        abort_unless($this->service->delete($progressReport), 404);

        return response()->json(['message' => 'Progress report deleted successfully']);
    }

    /**
     * Get progress timeline for a specific project
     *
     * GET /api/projects/{project}/progress-timeline
     */
    public function progressTimeline(Request $request, Project $project): JsonResponse
    {
        try {
            $limit = (int) $request->query('limit', 12);

            $reports = ProgressReport::forProject($project->id)
                ->select([
                    'id',
                    'reporting_date',
                    'physical_progress',
                    'financial_progress',
                    'progress_status',
                ])
                ->orderBy('reporting_date', 'desc')
                ->limit($limit)
                ->get()
                ->reverse()
                ->values();

            $timeline = $reports->map(function ($report) {
                return [
                    'id' => $report->id,
                    'date' => $report->reporting_date->format('Y-m-d'),
                    'physical_progress' => $report->physical_progress,
                    'financial_progress' => $report->financial_progress,
                    'status' => $report->progress_status,
                    'variance' => $report->physical_progress - $report->financial_progress,
                ];
            });

            // Calculate overall variance (latest report)
            $latestReport = $reports->last();
            $currentVariance = $latestReport
                ? $latestReport->physical_progress - $latestReport->financial_progress
                : 0;

            // Calculate average progress
            $avgPhysical = $reports->avg('physical_progress') ?? 0;
            $avgFinancial = $reports->avg('financial_progress') ?? 0;

            return response()->json([
                'success' => true,
                'data' => [
                    'project_id' => $project->id,
                    'project_title' => $project->title,
                    'timeline' => $timeline,
                    'variance' => $currentVariance,
                    'current_physical_progress' => $latestReport?->physical_progress ?? 0,
                    'current_financial_progress' => $latestReport?->financial_progress ?? 0,
                    'current_status' => $latestReport?->progress_status ?? 'on_track',
                    'average_physical_progress' => round($avgPhysical, 1),
                    'average_financial_progress' => round($avgFinancial, 1),
                    'total_reports' => $reports->count(),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve progress timeline',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get reports with issues or risks
     *
     * GET /api/progress-reports/with-issues
     */
    public function withIssues(Request $request): JsonResponse
    {
        try {
            $perPage = (int) $request->query('per_page', 15);

            $reports = ProgressReport::with(['project', 'department', 'creator'])
                ->where(function ($query) {
                    $query->whereNotNull('issues')
                        ->where('issues', '!=', '')
                        ->orWhere(function ($q) {
                            $q->whereNotNull('risks')
                                ->where('risks', '!=', '');
                        });
                })
                ->orderBy('reporting_date', 'desc')
                ->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => ProgressReportResource::collection($reports),
                'meta' => [
                    'current_page' => $reports->currentPage(),
                    'last_page' => $reports->lastPage(),
                    'per_page' => $reports->perPage(),
                    'total' => $reports->total(),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve reports with issues',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get progress statistics summary
     *
     * GET /api/progress-reports/statistics
     */
    public function statistics(): JsonResponse
    {
        try {
            $totalReports = ProgressReport::count();

            $byStatus = ProgressReport::selectRaw('progress_status, COUNT(*) as count')
                ->groupBy('progress_status')
                ->pluck('count', 'progress_status')
                ->toArray();

            $averages = ProgressReport::selectRaw('
                AVG(physical_progress) as avg_physical,
                AVG(financial_progress) as avg_financial
            ')->first();

            $recentReports = ProgressReport::where('reporting_date', '>=', now()->subDays(30))
                ->count();

            $reportsWithIssues = ProgressReport::whereNotNull('issues')
                ->where('issues', '!=', '')
                ->count();

            $reportsWithRisks = ProgressReport::whereNotNull('risks')
                ->where('risks', '!=', '')
                ->count();

            return response()->json([
                'success' => true,
                'data' => [
                    'total_reports' => $totalReports,
                    'by_status' => [
                        'on_track' => $byStatus['on_track'] ?? 0,
                        'at_risk' => $byStatus['at_risk'] ?? 0,
                        'delayed' => $byStatus['delayed'] ?? 0,
                        'ahead' => $byStatus['ahead'] ?? 0,
                    ],
                    'average_physical_progress' => round($averages->avg_physical ?? 0, 1),
                    'average_financial_progress' => round($averages->avg_financial ?? 0, 1),
                    'average_variance' => round(($averages->avg_physical ?? 0) - ($averages->avg_financial ?? 0), 1),
                    'recent_reports_count' => $recentReports,
                    'reports_with_issues' => $reportsWithIssues,
                    'reports_with_risks' => $reportsWithRisks,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve statistics',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
