<?php

namespace App\Repositories;

use App\Interfaces\DepartmentReportRepositoryInterface;
use App\Models\Department;
use App\Models\DepartmentKpi;
use App\Models\FundingDistribution;
use App\Models\ProgressReport;
use App\Models\Project;
use Illuminate\Support\Facades\DB;

class DepartmentReportRepository implements DepartmentReportRepositoryInterface
{
    public function getDepartmentReports(array $filters = []): array
    {
        $fiscalYear = $filters['fiscal_year'] ?? date('Y');
        $quarter = $filters['quarter'] ?? null;

        $departments = Department::with(['projects', 'kpis' => function ($query) use ($fiscalYear) {
            $query->where('fiscal_year', $fiscalYear);
        }])->get();

        $reports = [];

        foreach ($departments as $department) {
            // Get project statistics
            $projectQuery = Project::where('department_id', $department->id);

            if ($quarter) {
                $startMonth = ($quarter - 1) * 3 + 1;
                $endMonth = $quarter * 3;
                $projectQuery->whereMonth('created_at', '>=', $startMonth)
                    ->whereMonth('created_at', '<=', $endMonth)
                    ->whereYear('created_at', $fiscalYear);
            } else {
                $projectQuery->whereYear('created_at', $fiscalYear);
            }

            $projects = $projectQuery->get();
            $totalProjects = $projects->count();
            $completedProjects = $projects->filter(function ($p) {
                return $p->projectStatus && str_contains(strtolower($p->projectStatus->name ?? ''), 'completed');
            })->count();

            // Get budget data
            $budgetQuery = FundingDistribution::where('department_id', $department->id)
                ->where('fiscal_year', $fiscalYear);
            $totalBudget = $budgetQuery->sum('amount');

            // Calculate utilized budget from projects
            $utilizedBudget = Project::where('department_id', $department->id)
                ->whereYear('created_at', $fiscalYear)
                ->whereHas('projectStatus', function ($q) {
                    $q->whereIn('name', ['Completed', 'In Progress', 'completed', 'in_progress']);
                })
                ->sum('budget');

            // Get KPI achievement
            $kpis = $department->kpis->where('fiscal_year', $fiscalYear);
            $kpiAchievement = 0;
            if ($kpis->count() > 0) {
                $kpiAchievement = $kpis->avg(function ($kpi) {
                    if ($kpi->target_value > 0) {
                        return min(100, ($kpi->current_value / $kpi->target_value) * 100);
                    }

                    return 0;
                });
            }

            $reports[] = [
                'department_id' => $department->id,
                'department_name' => $department->name,
                'fiscal_year' => (int) $fiscalYear,
                'quarter' => $quarter ? (int) $quarter : null,
                'project_statistics' => [
                    'total' => $totalProjects,
                    'completed' => $completedProjects,
                    'in_progress' => $totalProjects - $completedProjects,
                    'completion_rate' => $totalProjects > 0 ? round(($completedProjects / $totalProjects) * 100, 2) : 0,
                ],
                'budget' => [
                    'allocated' => (float) $totalBudget,
                    'utilized' => (float) $utilizedBudget,
                    'remaining' => (float) ($totalBudget - $utilizedBudget),
                    'utilization_rate' => $totalBudget > 0 ? round(($utilizedBudget / $totalBudget) * 100, 2) : 0,
                ],
                'kpi_achievement' => round($kpiAchievement, 2),
            ];
        }

        return [
            'fiscal_year' => (int) $fiscalYear,
            'quarter' => $quarter ? (int) $quarter : null,
            'departments' => $reports,
            'summary' => [
                'total_departments' => count($reports),
                'total_projects' => array_sum(array_column(array_column($reports, 'project_statistics'), 'total')),
                'total_budget_allocated' => array_sum(array_column(array_column($reports, 'budget'), 'allocated')),
                'total_budget_utilized' => array_sum(array_column(array_column($reports, 'budget'), 'utilized')),
                'average_kpi_achievement' => count($reports) > 0 ? round(array_sum(array_column($reports, 'kpi_achievement')) / count($reports), 2) : 0,
            ],
        ];
    }

    public function getMonthlyProgress(int $departmentId, int $year): array
    {
        $department = Department::find($departmentId);

        if (! $department) {
            return [];
        }

        $monthlyData = [];

        for ($month = 1; $month <= 12; $month++) {
            // Get projects created/updated in this month
            $projectsInMonth = Project::where('department_id', $departmentId)
                ->whereYear('created_at', $year)
                ->whereMonth('created_at', $month)
                ->count();

            $completedInMonth = Project::where('department_id', $departmentId)
                ->whereYear('updated_at', $year)
                ->whereMonth('updated_at', $month)
                ->whereHas('projectStatus', function ($q) {
                    $q->whereIn('name', ['Completed', 'completed']);
                })
                ->count();

            // Get progress reports for this month
            $reportsInMonth = ProgressReport::where('department_id', $departmentId)
                ->whereYear('report_date', $year)
                ->whereMonth('report_date', $month)
                ->with('metrics')
                ->get();

            // Calculate average metrics
            $metricValues = [];
            foreach ($reportsInMonth as $report) {
                foreach ($report->metrics as $metric) {
                    $metricName = $metric->metric_name;
                    if (! isset($metricValues[$metricName])) {
                        $metricValues[$metricName] = [];
                    }
                    $metricValues[$metricName][] = $metric->metric_value;
                }
            }

            $averageMetrics = [];
            foreach ($metricValues as $name => $values) {
                $averageMetrics[$name] = round(array_sum($values) / count($values), 2);
            }

            // Get budget utilized this month
            $budgetUtilized = FundingDistribution::where('department_id', $departmentId)
                ->where('fiscal_year', $year)
                ->whereYear('allocated_date', $year)
                ->whereMonth('allocated_date', $month)
                ->sum('amount');

            $monthlyData[] = [
                'month' => $month,
                'month_name' => date('F', mktime(0, 0, 0, $month, 1)),
                'projects_started' => $projectsInMonth,
                'projects_completed' => $completedInMonth,
                'reports_submitted' => $reportsInMonth->count(),
                'budget_utilized' => (float) $budgetUtilized,
                'metrics' => $averageMetrics,
            ];
        }

        // Calculate cumulative progress
        $cumulativeProjects = 0;
        $cumulativeBudget = 0;
        foreach ($monthlyData as &$data) {
            $cumulativeProjects += $data['projects_completed'];
            $cumulativeBudget += $data['budget_utilized'];
            $data['cumulative_completed'] = $cumulativeProjects;
            $data['cumulative_budget'] = $cumulativeBudget;
        }

        return [
            'department_id' => $departmentId,
            'department_name' => $department->name,
            'year' => $year,
            'monthly_progress' => $monthlyData,
            'yearly_summary' => [
                'total_projects_started' => array_sum(array_column($monthlyData, 'projects_started')),
                'total_projects_completed' => array_sum(array_column($monthlyData, 'projects_completed')),
                'total_reports_submitted' => array_sum(array_column($monthlyData, 'reports_submitted')),
                'total_budget_utilized' => array_sum(array_column($monthlyData, 'budget_utilized')),
            ],
        ];
    }

    public function getKpiSummary(int $departmentId, ?int $fiscalYear = null): array
    {
        $department = Department::find($departmentId);

        if (! $department) {
            return [];
        }

        $fiscalYear = $fiscalYear ?? date('Y');

        $kpis = DepartmentKpi::where('department_id', $departmentId)
            ->where('fiscal_year', $fiscalYear)
            ->get();

        $kpiDetails = [];
        $totalAchievement = 0;
        $onTrackCount = 0;
        $atRiskCount = 0;
        $behindCount = 0;

        foreach ($kpis as $kpi) {
            $achievementRate = 0;
            $status = 'pending';

            if ($kpi->target_value > 0) {
                $achievementRate = ($kpi->current_value / $kpi->target_value) * 100;

                if ($achievementRate >= 80) {
                    $status = 'on_track';
                    $onTrackCount++;
                } elseif ($achievementRate >= 50) {
                    $status = 'at_risk';
                    $atRiskCount++;
                } else {
                    $status = 'behind';
                    $behindCount++;
                }
            }

            $totalAchievement += min(100, $achievementRate);

            $kpiDetails[] = [
                'id' => $kpi->id,
                'name' => $kpi->name,
                'target_value' => (float) $kpi->target_value,
                'current_value' => (float) $kpi->current_value,
                'unit' => $kpi->unit,
                'achievement_rate' => round(min(100, $achievementRate), 2),
                'status' => $status,
                'gap' => (float) max(0, $kpi->target_value - $kpi->current_value),
            ];
        }

        // Get historical comparison (previous year)
        $previousYearKpis = DepartmentKpi::where('department_id', $departmentId)
            ->where('fiscal_year', $fiscalYear - 1)
            ->get();

        $previousYearAvg = 0;
        if ($previousYearKpis->count() > 0) {
            $previousYearAvg = $previousYearKpis->avg(function ($kpi) {
                if ($kpi->target_value > 0) {
                    return min(100, ($kpi->current_value / $kpi->target_value) * 100);
                }

                return 0;
            });
        }

        $currentYearAvg = $kpis->count() > 0 ? ($totalAchievement / $kpis->count()) : 0;
        $yearOverYearChange = $previousYearAvg > 0 ? (($currentYearAvg - $previousYearAvg) / $previousYearAvg) * 100 : 0;

        return [
            'department_id' => $departmentId,
            'department_name' => $department->name,
            'fiscal_year' => (int) $fiscalYear,
            'kpis' => $kpiDetails,
            'summary' => [
                'total_kpis' => $kpis->count(),
                'average_achievement' => round($currentYearAvg, 2),
                'on_track' => $onTrackCount,
                'at_risk' => $atRiskCount,
                'behind' => $behindCount,
                'year_over_year_change' => round($yearOverYearChange, 2),
                'previous_year_average' => round($previousYearAvg, 2),
            ],
        ];
    }

    public function getBudgetUtilization(array $filters = []): array
    {
        $fiscalYear = $filters['fiscal_year'] ?? date('Y');

        $departments = Department::all();
        $utilizationData = [];

        foreach ($departments as $department) {
            $allocated = FundingDistribution::where('department_id', $department->id)
                ->where('fiscal_year', $fiscalYear)
                ->sum('amount');

            $utilized = Project::where('department_id', $department->id)
                ->whereYear('created_at', $fiscalYear)
                ->whereHas('projectStatus', function ($q) {
                    $q->whereIn('name', ['Completed', 'In Progress', 'completed', 'in_progress']);
                })
                ->sum('budget');

            $utilizationData[] = [
                'department_id' => $department->id,
                'department_name' => $department->name,
                'allocated' => (float) $allocated,
                'utilized' => (float) $utilized,
                'remaining' => (float) ($allocated - $utilized),
                'utilization_rate' => $allocated > 0 ? round(($utilized / $allocated) * 100, 2) : 0,
            ];
        }

        // Sort by utilization rate descending
        usort($utilizationData, function ($a, $b) {
            return $b['utilization_rate'] <=> $a['utilization_rate'];
        });

        return [
            'fiscal_year' => (int) $fiscalYear,
            'departments' => $utilizationData,
            'totals' => [
                'total_allocated' => array_sum(array_column($utilizationData, 'allocated')),
                'total_utilized' => array_sum(array_column($utilizationData, 'utilized')),
                'total_remaining' => array_sum(array_column($utilizationData, 'remaining')),
                'overall_utilization_rate' => array_sum(array_column($utilizationData, 'allocated')) > 0
                    ? round((array_sum(array_column($utilizationData, 'utilized')) / array_sum(array_column($utilizationData, 'allocated'))) * 100, 2)
                    : 0,
            ],
        ];
    }

    public function findDepartment(int $id)
    {
        return Department::with(['projects', 'kpis', 'progressReports', 'fundingDistributions'])
            ->find($id);
    }
}
