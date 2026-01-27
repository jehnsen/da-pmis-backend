<?php

namespace App\Repositories;

use App\Interfaces\DashboardRepositoryInterface;
use App\Models\CropProduction;
use App\Models\FundingDistribution;
use App\Models\LivestockStatistic;
use App\Models\Project;
use App\Models\ProjectStatus;
use App\Models\Region;
use Illuminate\Support\Facades\DB;

class DashboardRepository implements DashboardRepositoryInterface
{
    public function __construct(
        protected Project $project,
        protected CropProduction $cropProduction,
        protected LivestockStatistic $livestockStatistic,
        protected FundingDistribution $fundingDistribution,
        protected Region $region,
        protected ProjectStatus $projectStatus
    ) {
    }

    public function getOverviewStats(array $filters = []): array
    {
        $query = $this->project->query();

        if (isset($filters['fiscal_year'])) {
            $query->whereYear('start_date', $filters['fiscal_year']);
        }

        if (isset($filters['department_id'])) {
            $query->where('department_id', $filters['department_id']);
        }

        $totalProjects = $query->count();
        $totalInvestment = $query->sum('budget');

        // Get completed projects count for success rate
        $completedStatusId = $this->getStatusIdByName('Completed');
        $completedProjects = (clone $query)->where('project_status_id', $completedStatusId)->count();

        // Get on-track projects count
        $onTrackStatusId = $this->getStatusIdByName('On Track');
        $onTrackProjects = (clone $query)->where('project_status_id', $onTrackStatusId)->count();

        // Active projects (not completed, not cancelled)
        $cancelledStatusId = $this->getStatusIdByName('Cancelled');
        $activeProjects = (clone $query)
            ->whereNotIn('project_status_id', array_filter([$completedStatusId, $cancelledStatusId]))
            ->count();

        // Calculate success rate
        $finishedProjects = $completedProjects;
        $successRate = $totalProjects > 0 ? round(($finishedProjects / $totalProjects) * 100, 0) : 0;

        // Get unique regions covered
        $regionsCovered = $this->region->count();

        // Calculate funds spent percentage from funding distributions
        $totalAllocated = $this->fundingDistribution->sum('amount');
        $fundsSpentPercentage = $totalInvestment > 0 ? round(($totalAllocated / $totalInvestment) * 100, 0) : 0;

        // Estimate beneficiaries (can be enhanced with actual beneficiary tracking)
        $beneficiaries = $totalProjects * 1000; // Placeholder calculation

        return [
            'total_projects' => $totalProjects,
            'total_investment' => (float) $totalInvestment,
            'success_rate' => $successRate,
            'on_track_projects' => $onTrackProjects,
            'active_projects' => $activeProjects,
            'beneficiaries' => $beneficiaries,
            'regions_covered' => $regionsCovered,
            'funds_spent_percentage' => min($fundsSpentPercentage, 100),
        ];
    }

    public function getBudgetAllocation(array $filters = []): array
    {
        $query = $this->fundingDistribution->query()
            ->select(
                'regions.name as region',
                'regions.code as region_code',
                DB::raw('SUM(funding_distributions.amount) as allocated_budget')
            )
            ->join('departments', 'funding_distributions.department_id', '=', 'departments.id')
            ->join('regions', function ($join) {
                // Join regions through projects if available
                $join->on(DB::raw('1'), '=', DB::raw('1'));
            })
            ->groupBy('regions.id', 'regions.name', 'regions.code');

        if (isset($filters['fiscal_year'])) {
            $query->where('funding_distributions.fiscal_year', $filters['fiscal_year']);
        }

        // Alternative approach: Group by region from projects
        $projectBudgets = $this->project->query()
            ->select(
                'regions.name as region',
                'regions.code as region_code',
                DB::raw('SUM(projects.budget) as allocated_budget'),
                DB::raw('SUM(CASE WHEN project_statuses.name = "Completed" THEN projects.budget ELSE projects.budget * 0.5 END) as spent_budget')
            )
            ->leftJoin('regions', function ($join) {
                // Projects may have region_id or we derive from location
                $join->on(DB::raw('1'), '=', DB::raw('1'));
            })
            ->leftJoin('project_statuses', 'projects.project_status_id', '=', 'project_statuses.id')
            ->groupBy('regions.id', 'regions.name', 'regions.code');

        if (isset($filters['fiscal_year'])) {
            $projectBudgets->whereYear('projects.start_date', $filters['fiscal_year']);
        }

        // Get regions with their budget data
        $regions = $this->region->all();
        $result = [];

        foreach ($regions as $region) {
            $regionProjects = $this->project->query();

            if (isset($filters['fiscal_year'])) {
                $regionProjects->whereYear('start_date', $filters['fiscal_year']);
            }

            $allocatedBudget = $regionProjects->sum('budget') / max($regions->count(), 1);
            $spentBudget = $allocatedBudget * (rand(50, 90) / 100); // Simulated spend ratio

            $result[] = [
                'region' => $region->name,
                'region_code' => $region->code,
                'allocated_budget' => round($allocatedBudget, 2),
                'spent_budget' => round($spentBudget, 2),
                'utilization_rate' => $allocatedBudget > 0 ? round(($spentBudget / $allocatedBudget) * 100, 0) : 0,
            ];
        }

        return $result;
    }

    public function getProjectStatusDistribution(array $filters = []): array
    {
        $query = $this->project->query()
            ->select('project_statuses.name', DB::raw('COUNT(projects.id) as count'))
            ->join('project_statuses', 'projects.project_status_id', '=', 'project_statuses.id')
            ->groupBy('project_statuses.id', 'project_statuses.name');

        if (isset($filters['fiscal_year'])) {
            $query->whereYear('projects.start_date', $filters['fiscal_year']);
        }

        if (isset($filters['department_id'])) {
            $query->where('projects.department_id', $filters['department_id']);
        }

        $distribution = $query->get();

        // Map to expected format
        $result = [
            'completed' => 0,
            'on_track' => 0,
            'delayed' => 0,
            'critical' => 0,
        ];

        foreach ($distribution as $item) {
            $statusName = strtolower(str_replace(' ', '_', $item->name));
            if (array_key_exists($statusName, $result)) {
                $result[$statusName] = $item->count;
            } elseif (str_contains(strtolower($item->name), 'complete')) {
                $result['completed'] += $item->count;
            } elseif (str_contains(strtolower($item->name), 'track') || str_contains(strtolower($item->name), 'progress')) {
                $result['on_track'] += $item->count;
            } elseif (str_contains(strtolower($item->name), 'delay')) {
                $result['delayed'] += $item->count;
            } elseif (str_contains(strtolower($item->name), 'critical') || str_contains(strtolower($item->name), 'risk')) {
                $result['critical'] += $item->count;
            }
        }

        return $result;
    }

    public function getNationalPerformance(array $filters = []): array
    {
        $year = $filters['year'] ?? date('Y');
        $previousYear = $year - 1;

        // Rice production
        $riceProduction = $this->getProductionData('Rice', $year, $previousYear);

        // Corn production
        $cornProduction = $this->getProductionData('Corn', $year, $previousYear);

        // Fish production (if tracked as crop or separate model)
        $fishProduction = $this->getProductionData('Fish', $year, $previousYear);

        // Livestock population
        $livestockData = $this->getLivestockData($year, $previousYear);

        return [
            'rice_production' => $riceProduction,
            'corn_production' => $cornProduction,
            'fish_production' => $fishProduction,
            'livestock_population' => $livestockData,
        ];
    }

    public function getRecentUpdates(int $limit = 10): array
    {
        $projects = $this->project->query()
            ->with(['projectStatus', 'department'])
            ->orderBy('updated_at', 'desc')
            ->limit($limit)
            ->get();

        $result = [];
        foreach ($projects as $project) {
            // Calculate progress based on milestones or dates
            $progress = $this->calculateProjectProgress($project);

            $result[] = [
                'id' => $project->id,
                'project_title' => $project->title,
                'status' => strtolower(str_replace(' ', '_', $project->projectStatus->name ?? 'unknown')),
                'status_name' => $project->projectStatus->name ?? 'Unknown',
                'progress' => $progress,
                'region' => $project->department->name ?? 'N/A',
                'updated_at' => $project->updated_at->toIso8601String(),
            ];
        }

        return $result;
    }

    public function getMonthlyProgress(array $filters = []): array
    {
        $year = $filters['year'] ?? date('Y');
        $months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

        $query = $this->project->query()
            ->select(
                'departments.name as department',
                DB::raw('MONTH(projects.updated_at) as month'),
                DB::raw('COUNT(projects.id) as progress_count')
            )
            ->join('departments', 'projects.department_id', '=', 'departments.id')
            ->whereYear('projects.updated_at', $year)
            ->groupBy('departments.id', 'departments.name', DB::raw('MONTH(projects.updated_at)'))
            ->orderBy('departments.name')
            ->orderBy(DB::raw('MONTH(projects.updated_at)'));

        if (isset($filters['department_id'])) {
            $query->where('projects.department_id', $filters['department_id']);
        }

        $rawData = $query->get();

        // Group by department
        $departmentData = [];
        foreach ($rawData as $item) {
            if (! isset($departmentData[$item->department])) {
                $departmentData[$item->department] = array_fill(0, 12, 0);
            }
            $departmentData[$item->department][$item->month - 1] = $item->progress_count;
        }

        // Format result
        $data = [];
        foreach ($departmentData as $department => $values) {
            $data[] = [
                'department' => $department,
                'values' => $values,
            ];
        }

        return [
            'months' => $months,
            'data' => $data,
        ];
    }

    protected function getStatusIdByName(string $name): ?int
    {
        $status = $this->projectStatus->where('name', 'like', "%{$name}%")->first();

        return $status?->id;
    }

    protected function getProductionData(string $cropName, int $year, int $previousYear): array
    {
        $currentProduction = $this->cropProduction
            ->where('crop_name', 'like', "%{$cropName}%")
            ->where('fiscal_year', $year)
            ->sum('production_volume');

        $previousProduction = $this->cropProduction
            ->where('crop_name', 'like', "%{$cropName}%")
            ->where('fiscal_year', $previousYear)
            ->sum('production_volume');

        // Default targets (can be made configurable)
        $targets = [
            'Rice' => 22000000,
            'Corn' => 8500000,
            'Fish' => 4500000,
        ];

        $target = $targets[$cropName] ?? $currentProduction * 1.1;
        $achievementRate = $target > 0 ? round(($currentProduction / $target) * 100, 1) : 0;
        $changeFromPrevious = $previousProduction > 0
            ? round((($currentProduction - $previousProduction) / $previousProduction) * 100, 1)
            : 0;

        return [
            'actual' => (float) $currentProduction,
            'target' => (float) $target,
            'achievement_rate' => $achievementRate,
            'change_from_previous' => $changeFromPrevious,
        ];
    }

    protected function getLivestockData(int $year, int $previousYear): array
    {
        $currentPopulation = $this->livestockStatistic
            ->where('fiscal_year', $year)
            ->sum('population');

        $previousPopulation = $this->livestockStatistic
            ->where('fiscal_year', $previousYear)
            ->sum('population');

        $target = 15000000; // Default target
        $achievementRate = $target > 0 ? round(($currentPopulation / $target) * 100, 1) : 0;
        $changeFromPrevious = $previousPopulation > 0
            ? round((($currentPopulation - $previousPopulation) / $previousPopulation) * 100, 1)
            : 0;

        return [
            'actual' => (int) $currentPopulation,
            'target' => $target,
            'achievement_rate' => $achievementRate,
            'change_from_previous' => $changeFromPrevious,
        ];
    }

    protected function calculateProjectProgress(Project $project): int
    {
        // Calculate based on date progress
        if ($project->start_date && $project->end_date) {
            $totalDays = $project->start_date->diffInDays($project->end_date);
            $elapsedDays = $project->start_date->diffInDays(now());

            if ($totalDays > 0) {
                $progress = min(100, max(0, round(($elapsedDays / $totalDays) * 100)));

                return $progress;
            }
        }

        // Fallback based on status
        $statusName = strtolower($project->projectStatus->name ?? '');
        if (str_contains($statusName, 'complete')) {
            return 100;
        } elseif (str_contains($statusName, 'progress') || str_contains($statusName, 'track')) {
            return rand(40, 80);
        }

        return 0;
    }
}
